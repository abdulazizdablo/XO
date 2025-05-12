<?php

namespace App\Http\Controllers\Users\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Refund\StoreRefundRequest;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Setting;
use App\Models\Refund;
use App\Services\RefundService;
use App\Models\OrderItem;
use App\Models\Invoice;
use App\Models\Transaction;
use Symfony\Component\HttpFoundation\Response;
use Exception;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RefundController extends Controller
{


    public function __construct(protected RefundService $refundService)
    {
    }
	
	public function store(StoreRefundRequest $request){
		
		try{
			
			DB::beginTransaction();
			$user = auth('sanctum')->user();

			if(!$user){
				return response()->error('Unauthorized',403);
			}
			$total_quantity = 0;
			$old_price = 0;
			$fees = 2000; //to do: get fees from settings
			$key = Setting::where('key', 'fees')->first();
			
			if ($key != null) {
                $key->value = json_decode($key->value);
                $fees = $key['value']->en->shipping_fee;
        	}	
			
			$validatedData = $request->validated();
            $order_id = $validatedData['order_id'];
			$date = $validatedData['date'];

			try {
				$date = Carbon::createFromFormat('Y-m-d', $date);
			} catch (\Exception $e) {
				try {
					$date = Carbon::createFromFormat('d-m-Y', $date);
				} catch (\Exception $e) {
					// Handle the case where neither format works
					return response()->json([
						'success' => false,
						'error' => 'Invalid date format',
					], Response::HTTP_BAD_REQUEST);
				}
			}
		
			if($date < now()){
				$date = Carbon::now()->format('Y-m-d');				
			}
			
            $time = $validatedData['time'];
            //$total_refund = 0;
            $order = Order::findOrFail($order_id);
			
            if ($order->replacedOrReturned == 1){
              //  throw new Exception('Order can not refunded');
				return response()->error(['message' => trans('products.remove_favourite',[],$request->header('Content-Language'))] ,409);

            }
			
			if ($order->paid == 0 || $order->status != 'received' ) {
            	//throw new Exception('Order can not replaced');
				
							return response()->error(['message' => trans('refund.order_not_replaced',[],$request->header('Content-Language'))] ,409);

        	}
			
			if($order->user_id != $user->id){
                throw new Exception('Order does not belong to you');				
			}
			
            /*if($order->coupon_id != null){
                throw new Exception("Order uses coupon so it CAN NOT be refunded", 400);    
            }*/
			
			$payment_method = $order->payment_method;
			
			$return_order = Order::create([
			'user_id' => $user->id,
			'inventory_id' => $order->inventory_id,
			'original_order_id' => $order_id,
			'total_quantity' => 0, //this has to be the sum of all new items
			'discounted_by_coupon' => 0,
			'covered_by_gift_card' => 0,
			'payment_method' => $payment_method,
			'shipping_fee' => $fees,
			'qr_code' => 'QR',
			'delivery_type' => 'return'
			]);

			$order_items_data = $request->validated('order_items'); //To Do: validate that all order items belongs to the same order
			
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
						$discounted = floor(($returned_price_for_item * $percentage)/100);
						$discounted_returned_price_for_item = $returned_price_for_item -  $discounted;
						$old_price += $discounted_returned_price_for_item * $item_data['quantity'];
					}else{
						$old_price += $returned_price_for_item * $item_data['quantity'];
						$discounted_returned_price_for_item = $returned_price_for_item;
					}

                    //to do: make sure that all order items belong to the same order ... by validation

                    if ($order_item->order_id != $order->id) {
                       // throw new Exception('products does not belong to selected order');
						
									return response()->error(['message' => trans('order.order_replaced_error',[],$request->header('Content-Language'))] ,409);

                    }

                    if ($order_item->group_id != null) { // To Do: add condition that underware and towels can't be returned
                        throw new Exception('Offer products can not be replaced');
                    }
					$order_item->update(['status'=>'return']);
					OrderItem::create([
						'product_variation_id' => $order_item->product_variation_id,
						'order_id' => $return_order->id,
						'quantity' => $item_data['quantity'],
						'to_inventory' => $order_item->to_inventory,
						'original_price' => $returned_price_for_item * $item_data['quantity'] ,
						'price' => $discounted_returned_price_for_item  * $item_data['quantity'],
						//'price' => $old_price, // to check if the price is correct
						'status' => 'return'						
					]);
                }
			}
				
			$return_order->update([
			'total_quantity' => $total_quantity, 
			'total_price' =>  - $old_price, 
			'paid_by_user' => - $old_price, 
			'price_without_offers' => - $old_price,	
			'paid' => 1
			]);
			
			$order->update(['status'=>'returned']);
			
			$shipment = $order->shipment;
			$newShipment = new \App\Models\Shipment($shipment->getAttributes());
			$newShipment->order_id = $return_order->id;
			$newShipment->date = $date;
			$newShipment->time = $time;
			$newShipment->is_delivered = 0;
			$newShipment->save();
			
			$refund =  Refund::create([
    
                    'user_id' => $user->id,
                    'order_id' => $order_id,
                    'inventory_id'=>$order->inventory_id,
                    'refund_date' => now(),
                    'total_refund' => $old_price/* - $fees */,
                    'status' => 'pending',
                    'reason' => $request->reason
    
                ]);
			
			Transaction::create([
				'transaction_uuid' => 'refund',
				'refund_id'=> $return_order->id,
				'user_id' => $user->id,
				'amount' => -($old_price - $fees) ,
				'status' => 'pending',
				'payment_method'=> $order->payment_method,
				'transaction_source' => $order->transaction->transaction_source,
				'operation_type' =>'refund'
			]);
			
			$ids = array_column($request->order_items, 'id');
            
			$order_items = OrderItem::findOrFail($ids)->load('order','product_variation','product_variation.group');

			$refund_items =  $order_items->map(function ($item, $key) use ($refund, $request) {

				return $refund->refund_items()->create([
					//'order_item_id' => $item->id,
					'product_variation_id' => $item->product_variation_id,
					'quantity' => $request->order_items[$key]['quantity'],
					'price' => $item->price
				]);
			});
			
			$invoice_number = $order->invoice_number . '.refund-' . $refund->id;
			
            Invoice::create([
                'order_id' => $order_id,
                'user_id' => $order->user->id,
                'shipment_id' => $order->shipment->id,
                'total_price' => $old_price,
                'fees' => $fees,
                'total_payment' => $old_price - $fees,
                'invoice_number' => $invoice_number,
                'type' => 'replace',
               // 'order_items' => json_encode($exchange->exchange_items),

            ]);
			//$order->update(['status'=>'returned']);
            $invoice_data = Order::select(['id', 'invoice_number','total_quantity', 'payment_method', 'total_price', 'paid_by_user','discounted_by_coupon','price_without_offers','covered_by_gift_card','gift_id', 'shipping_fee', 'total_quantity', 'created_at'])
				->where('id', $return_order->id)
				->firstOrFail()
				->load([
					'order_items:id,product_variation_id,order_id,quantity,original_price,price',
					'order_items.product_variation:id,product_id', // Load the product variation
					'order_items.product_variation.product:id,name', // Load the product through the product variation
					'shipment:id,order_id,type,receiver_first_name,date,time,receiver_last_name,city,street,neighborhood',
					'invoice:id,order_id,gift_card_balance,coupon_percenage'
				]);
			$invoice_data->discounted_price = $return_order->total_price - $return_order->discounted_by_coupon - $order->covered_by_gift_card;
			
            DB::commit();        
		//	return response()->json('Order refunded successfully');
			return response()->success(['message' => trans('refund.order_refunded',[],$request->header('Content-Language')), 'invoice_data' => $invoice_data]  ,200);
		}catch (\Exception $e) {

            DB::rollBack();
            return response()->error($e->getMessage(), 400);
        }
		
	}

}
