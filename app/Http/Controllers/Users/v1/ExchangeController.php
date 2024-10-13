<?php

namespace App\Http\Controllers\Users\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Exchange\StoreExchangeRequest;
use Illuminate\Http\Request;
use App\Models\Coupon;
use App\Models\Exchange;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Setting;
use App\Models\ProductVariation;
use App\Services\ExchangeService;
use App\Services\OrderService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use App\Services\EcashPaymentService;
use App\Models\ExchangeItem;
use App\Models\Invoice;
use App\Models\Transaction;
use Carbon\Carbon;

class ExchangeController extends Controller
{

    public function __construct(
        protected ExchangeService $exchangeService,
        protected OrderService $orderService,
        protected EcashPaymentService $ecashPaymentService
    ) {
    }
	
    public function store(StoreExchangeRequest $request)
    {
        try {
            // $product = $this->refundService->checkExchangeProducts($order_items);
        DB::beginTransaction();
		$user = auth('sanctum')->user();
			
		if(!$user){
			return response()->error('Unauthorized',403);
		}
			
		$unreplaceable_categories_setting = Setting::where('key', 'addNonReplacableCatgories')->first();
		$unreplaceable_categories = array_values(json_decode($unreplaceable_categories_setting->value,true)[0]);
			
		$links = Setting::where('key','links')->firstOrFail();
		$phone = json_decode($links->value,true)['phone'];
		$fees = 2000; //to do: get fees from settings
        $key = Setting::where('key', 'fees')->first();
		
		if ($key != null) {
                $key->value = json_decode($key->value);
                $fees = $key['value']->en->shipping_fee;
        }	
		
		$order_id = $request->validated('order_id');
        $date = $request->validated('date');
		
		try {
				$date = Carbon::createFromFormat('Y-m-d', $date);
			} catch (\Exception $e) {
				try {
					$date = Carbon::createFromFormat('d-m-Y', $date);
				} catch (\Exception $e) {
					// Handle the case where neither format works
					//$date = Carbon::createFromFormat('d-m-Y', now());
					return response()->json([
						'success' => false,
						'error' => 'Invalid date format',
					], Response::HTTP_BAD_REQUEST);
				}
			}
		
		$time = $request->validated('time');
        $order = Order::findOrFail($order_id);
		
		if ($order->user_id != $user->id){
			throw new Exception('only order owner can perform this operation');
		}

		if ($order->paid == 0 || $order->status != 'received' ) {
         //   throw new Exception('Order can not replaced');
			
			return response()->error(['message' => trans('order.order_replaced_error',[],$request->header('Content-Language')) ],409);
        }
		
		$payment_method = $request->validated('payment_method');

		$exchange_order = Order::create([
			'user_id' => $user->id,
			'inventory_id' => $order->inventory_id,
			'original_order_id' => $order_id,
			'total_quantity' => 0, //this has to be the sum of all new items
			'discounted_by_coupon' => 0,
			'covered_by_gift_card' => 0,
			'payment_method' => $payment_method,
			'shipping_fee' => $fees,
			'qr_code' => 'QR',
			'delivery_type' => 'replacing'
			]);
		
		$order_items_data = $request->validated('order_items'); 
		$exchange_items_data = $request->validated('exchange_items');
			 $aggregatedExchangeItems = array_reduce($request->get('exchange_items'), function ($carry, $item) {
            $productId = $item['product_variation_id'];
				 
            if (!isset($carry[$productId])) {
                $carry[$productId] = ['quantity' => 0];
            }
				 
            $carry[$productId]['quantity'] += $item['quantity'];
            return $carry;
        }, []);
		
		$transformedExchangeItems = []; // we are doing this transformation because sometimes the same product variation is sent more than once in the same exchange request, and without handling it, only the first apperance will be calculated.
    foreach ($aggregatedExchangeItems as $productId => $details) { 
        $transformedExchangeItems[] = [
            'product_variation_id' => $productId,
            'quantity' => $details['quantity'],
        ];
    }

		$old_price = 0;
		$new_price = 0;
		$total_quantity = 0;
		
		$city_id = $order->shipment->city_id;
			
			foreach ($transformedExchangeItems as $exchange_data) {
				$product_variation = ProductVariation::findOrFail($exchange_data['product_variation_id']);
				$new_price += $product_variation->product->pricing->value * $exchange_data['quantity'];
				//new price is the price without offers, this will be used later in total price in order
			}
       	foreach ($order_items_data as $item_data) {
                $order_item = OrderItem::findOrFail($item_data['id']);
			
                if (!$order_item) {
                    throw new Exception('Item not found');
                }

                if ($order_item) {

                    $total_quantity += $item_data['quantity'];
					$returned_price_for_item = ($order_item->price) / ($order_item->quantity);

					if($order->coupon_id != null){//if original order uses coupon then we have to return only the paid price
						$percentage = Coupon::findOrFail($order->coupon_id)->percentage;	
						$discounted = ($returned_price_for_item * $percentage)/100;
						$discounted_returned_price_for_item = $returned_price_for_item -  $discounted;
						$old_price += $discounted_returned_price_for_item * $item_data['quantity'];
					}else{
						$old_price += $returned_price_for_item * $item_data['quantity'];
						$discounted_returned_price_for_item = $returned_price_for_item;
					}
					
                    if ($order_item->order_id != $order->id) {
                        throw new Exception('products does not belong to selected order');
                    }

                    if ($order_item->group_id != null) { // To Do: add condition that underware and towels can't be returned
                        throw new Exception('Offer products can not be replaced');
                    }

					$order_item->update(['status'=>'return']);
					OrderItem::create([
						'product_variation_id' => $order_item->product_variation_id,
						'order_id' => $exchange_order->id,
						'quantity' => $item_data['quantity'],
						'to_inventory' => $order_item->to_inventory,
						'original_inventory' => $order_item->original_inventory,
						'original_price' => $returned_price_for_item * $item_data['quantity'] ,
						'price' => $discounted_returned_price_for_item  * $item_data['quantity'],
						//'price' => $old_price,
						'status' => 'return'						
					]);
                }

                
			
                //if ((Order::where('id', $order_item->order_id)->first()->status !== 'received' || (Order::where('id', $order_item->order_id)->first()->closed == 1))) {
                  //  throw new Exception("Order Cant't be Refunded", 400);
                //}
            }		
		//return $exchange_items_data;
		

			$result = $this->orderService->createOrderItem($exchange_order->id, $transformedExchangeItems, $city_id);

			$exchange_order->update([
				'total_quantity' => $result['total_quantity'] - $total_quantity, 
				'total_price' => $result['total_price'] - $old_price, // price of new items after applying offers minus the price of old items
				'paid_by_user' => $result['total_price'] - $old_price, 
				'price_without_offers' => $new_price - $old_price,			
				]);

			$total_exchange = $result['total_price'] - $old_price;
			
                //if (($order->gift_id != null) && ($total_exchange < 0)) {
                //    throw new Exception('gift card orders can only replaced with same or higher price');
                //}
			
			$shipment = $order->shipment;
			$newShipment = new \App\Models\Shipment($shipment->getAttributes());
			$newShipment->order_id = $exchange_order->id;
			$newShipment->date = $date;
			$newShipment->time = $time;
			$newShipment->is_delivered = 0;
			$newShipment->save();

            ////
            ///
            //
            $exchange = Exchange::create([
                'order_id' => $order_id,
                // 'inventory_id'=>$order->inventory_id,
                'quantity' => $total_quantity,
                'exchange_date' => now(),
                'total_exchange' => $total_exchange,
                'status' => 'pending',
                'reason' => $request->validated('reason')
            ]);

            $ids = array_column($request->validated('order_items'), 'id');

            $order_items = OrderItem::findOrFail($ids)->load('order', 'product_variation', 'product_variation.group');

            $exchange_items = $order_items->map(function ($item, $key) use ($exchange, $request) {

                $exchange->exchange_items()->create([
                    'exchange_id' => $exchange->id,
                    'order_item_id' => $item->id,
                    'product_variation_id' => $request->validated('exchange_items')[$key]['product_variation_id'],
                    'in_quantity' => $request->validated('exchange_items')[$key]['quantity'],
                    'out_quantity' => $request->validated('order_items')[$key]['quantity'],
                    'returned_product_variation_id' => $item->product_variation_id,
                ]);
            });
            
			//return $exchange->load(['exchange_items','exchange_items.productVariation.product','exchange_items.returnedProductVariation.product']);
			
			$invoice = Invoice::create([
                'order_id' => $order_id,
                'user_id' => $order->user->id,
                'shipment_id' => $order->shipment->id,
                'total_price' => $total_exchange,
                'fees' => $fees,
                'total_payment' => $total_exchange + $fees,
                'invoice_number' => $exchange_order->invoice_number,
                'type' => 'replace',
            ]);
			
			$data=[
                "order_ref"=>$exchange_order->id.'-'.$invoice->id,
                "lang"=>"EN",
				'exchange' => $exchange->load('exchange_items'),
				'amount'=>($result['total_price'] - $old_price + $fees),
				'message' => 'You will be redirected to MTN Cash',
				'order_id' => $exchange_order->id,
				'phone' => $phone,
				'invoice_number' => $exchange_order->invoice_number
            ];
			
            if ($total_exchange + $fees < 0){
				Transaction::create([
                    'transaction_uuid' => 'refund',
                    'order_id'=> $exchange_order->id,
                    'user_id' => $user->id,
                    'amount' => $total_exchange + $fees, // if we should return money to user then the fees should be subtracted from the refunded money (total exchange in refund will be negative so we use plus)
                    'status' => 'pending',
                    'payment_method'=> $exchange_order->payment_method,
                    'operation_type' =>'exchange_order'
                ]);

				$exchange_order->update(['paid'=>1]);
				$order->update(['status'=>'replaced']);
				DB::commit();

				//return response()->success('Your exchnage order has been submitted', 201);
				 return response()->success(['exchange' => $exchange->load('exchange_items'),
											'amount'=> $total_exchange + $fees,
											'message' => 'Your money will be returned to your account within a week',
											'order_id' => $exchange_order->id,
											'phone' => $phone,
											'invoice_number' => $exchange_order->invoice_number,
										    'url' => null],
										   201);
			}
			
			if ($total_exchange + $fees == 0){	
				$exchange_order->update(['paid'=>1]);
				$order->update(['status'=>'replaced']);
				DB::commit();
                return response()->success(['exchange' => $exchange->load('exchange_items'),
											'amount'=> 0,
											'message' => 'Your order was submitted successfully',
											'order_id' => $exchange_order->id,
											'phone' => $phone,
											'invoice_number' => $exchange_order->invoice_number,
										    'url' => null],
										   201);			
			}
			
			if (($total_exchange + $fees > 0) && ($order->payment_method == 'Free') && $order->gift_id != null){	
				$exchange_order->update(['payment_method'=> 'cod']);
				//$order->update(['status'=>'replaced']);  // original order become replaced after confirm received of the cod 
				DB::commit();
//				return response()->success(['exchange' => $exchange->load('exchange_items'),], 201);
                $response = $this->orderService->cashOnDelivery($data);
				return response()->success(['exchange' => $exchange->load('exchange_items'),], 201);

//                return $response;			
			}
			
            if (!$payment_method && $total_exchange + $fees > 0) {
                throw new Exception('Choose payment method', 400);
            }
			
            if ($payment_method == "ecash" || $payment_method == "payment_method_3") {
                //return 'done';
                $full_url = $this->ecashPaymentService->replaceOrderPayment($data);
				DB::commit();
				//return $full_url;
                return response()->success($full_url, 201);

            } elseif ($payment_method == "mtn-cash" || $payment_method == "payment_method_2") {
				DB::commit();
                return response()->success(['exchange' => $exchange->load('exchange_items'),
											'amount'=>($result['total_price'] - $old_price + $fees),
											'message' => 'You will be redirected to MTN Cash',
											'order_id' => $exchange_order->id,
											'phone' => $phone,
											'invoice_number' => $exchange_order->invoice_number,
										    'url' => null],
										   201);

            } elseif ($payment_method == "syriatel-cash" || $payment_method == "payment_method_1") {
				DB::commit();
                return response()->success(['exchange' => $exchange->load('exchange_items'),
											'amount'=>($result['total_price'] - $old_price + $fees),
											'message' => 'You will be redirected to Syriatel Cash',
											'order_id' => $exchange_order->id,
											'phone' => $phone,
											'invoice_number' => $exchange_order->invoice_number,
  										    'url' => null],
										   201);

            }
			
			elseif ($payment_method == "cod" ) {
				DB::commit();
				return response()->success(['exchange' => $exchange->load('exchange_items'),], 201);
                $response = $this->orderService->cashOnDelivery($data);
                return $response;

            }

        } catch (\Exception $e) {

            DB::rollBack();
            return response()->error($e->getMessage(), 400);
        }


        /* if ($order->reciving_date   > now()->subDays(15)) &&  {

            $refund = Refund::create([

                'order_id' => $order->id,

                'refund_date' => now(),

                'status' => 'pending',



            ]);
        }*/

        return response()->success(['exchange' => $exchange->load('exchange_items'),], 201);
    }
	
    public function checkPriceDifference(Request $request)
    {
        $order_id = $request->input('order_id');
        $order = Order::find($order_id);
        if (!$order) {
            return response()->json(['error' => 'Order not found'], 404);
        }

        $order_items_data = $request->input('order_items'); //To Do: validate that all order items belongs to the same order
        $exchange_items_data = $request->input('exchange_items');
        $old_price = 0;
        $new_price = 0;
        $total_quantity = 0;

        //foreach ($exchange_items_data as $exchange_data) {
        //    $product_variation = ProductVariation::findOrFail($exchange_data['product_variation_id']);
        //    $new_price += $product_variation->product->pricing->value * $exchange_data['quantity'];
        //    //To Do: when calculating new price apply offer and flash sales
        // }
		$new_price = $this->orderService->calculatePrice($exchange_items_data);


        foreach ($order_items_data as $item_data) {

            $order_item = OrderItem::findOrFail($item_data['id']);

            if (!$order_item) {
                throw new Exception('Item not found');
            }

            if ($order_item->group_id != null) { // To Do: add condition that underware and towels can't be returned
              //  throw new Exception('Offer products can not be replaced');
			return response()->error(['message' => trans('exchange.offer_products_not_replaced',[],$request->header('Content-Language')) ],409);

            }

            if ($order_item) {
                $total_quantity += $item_data['quantity'];

					$returned_price_for_item = ($order_item->price) / ($order_item->quantity);

                    $old_price += $returned_price_for_item * $item_data['quantity'];

                //to do: make sure that all order items belong to the same order ... by validation

                if ($order_item->order_id != $order->id) {
                    throw new Exception('products does not belong to selected order');
                }


            }
        }
		
		$total_exchange = $new_price['total_price'] - $old_price;
		return response()->success(['total_exchange' => $total_exchange], 200);

    }

    public function showbyReplacedSKU(Request $request)
    {
        $product = ProductVariation::where('sku_code', $request->sku_code)->load('pricing');
        return response()->success($product, 200);
    }
}
