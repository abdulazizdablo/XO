<?php

namespace App\Http\Controllers\Users\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\CheckAvailableInCityRequest;
use App\Http\Requests\Order\GetPriceRequest;
use App\Http\Requests\Order\StoreOrderRequest;
use App\Http\Requests\Order\CheckAvailableCityRequest;
use App\Models\Address;
use App\Models\Coupon;
use App\Models\Invoice;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\Setting;
use App\Models\StockLevel;
use App\Models\OrderItem;
use App\Services\EcashPaymentService;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Validation\Rule;
use App\Models\City;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{




	public function __construct(
		protected OrderService $orderService,
		protected EcashPaymentService $ecashPaymentService
	) {
	}

	public function pay()
	{
		$data = [
			"amount" => 10000,
			"order_ref" => "INV-1",
			"lang" => "EN"
		];
		$full_url = $this->ecashPaymentService->sendPayment($data);
		// $full_url = "https://checkout.ecash-pay.co/Checkout/Card/GB97ST/IXZM1E/744F03E5FE93EC8C7302DAD09E823701/SYP/1000.00/";
		return response()->success(
			$full_url,
			Response::HTTP_OK
		);
	}

	/**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
	public function index(Request $request)
	{
		// $per_page = 4;
		$page_size = request('pageSize');
	

		$filter_data = $request->only([
			'invoice_number',
			'status',
			'price_min',
			'price_max',
			'quantity',
			'date_min',
			'date_max',
			'delivery_min',
			'delivery_max',
		]);

		$sort_data = $request->only([
			'sort_key',
			'sort_value',
		]);

		$order = $this->orderService->getAllUserOrders(auth('sanctum')->id(), $filter_data, $sort_data, $page_size);

		return response()->success(
			$order,
			Response::HTTP_OK
		);
	}

	/**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

	public function store(StoreOrderRequest $request)
	{
	
		
		$user_id = auth('sanctum')->id();

		$unfinishedOrder = Order::where([['original_order_id',null],['user_id',$user_id],['paid',0],['payment_method','!=','cod']])->latest()->first();//to delete unfinished orders if the user try to make many orders without completing payment
		if($unfinishedOrder){
			$unpaidItems = $unfinishedOrder->order_items()->get();
			foreach ($unpaidItems as $pv) {
				$stock = StockLevel::where([['inventory_id', $pv->original_inventory], ['product_variation_id', $pv->product_variation_id]])->first();
				$stock->update([
					'on_hold' => $stock->on_hold - $pv->quantity,
					'current_stock_level' => $stock->current_stock_level + $pv->quantity
				]);
			}
			$unfinishedOrder->forceDelete();
		}

		try {
		
			$validated_data = $request->validated();

			if($validated_data['order'][0]['payment_method'] == 'payment_method_1'){
				$validated_data['order'][0]['payment_method'] = 'syriatel-cash';	
			}

			if($validated_data['order'][0]['payment_method'] == 'payment_method_2'){
				$validated_data['order'][0]['payment_method'] = 'mtn-cash';	
			}

			if($validated_data['order'][0]['payment_method'] == 'payment_method_3'){
				$validated_data['order'][0]['payment_method'] = 'ecash';	
			}



			$branch_id = $validated_data['branch_id'] ?? null;
			$inventory_id = $validated_data['inventory_id'] ?? null;
			$order_data = $validated_data['order'][0];
			$payment_method = Str::lower($order_data['payment_method']);
			$order_items_data = $validated_data['order_items'];
			$shipping_info_data = $validated_data['shipping_info'][0];
			$address_id = null;
			
			$lat = $shipping_info_data['lat'];
			$long = $shipping_info_data['long'];
			//$inventory_id = $this->orderService->getInventoryIdByPoint($lat, $long);
			
			if (is_null($inventory_id)) {
				//throw new \Exception('Service is not available is your region');
				
				if (isset($shipping_info_data['city'])) {
					$city = City::where('name->en', $shipping_info_data['city'])->orWhere('name->ar', $shipping_info_data['city'])->first();
					if (!$city) {
						return response()->json('Please enter existing city', 404);
					}
				}
				if (isset($shipping_info_data['city_id'])) {
					$city = City::where('id', $shipping_info_data['city_id'])->firstOrFail();
				}
				$address = null;
				if (isset($address_id)) {
					$address = Address::findOrFail($address_id);
					$city = City::where('id', $address->city_id)->firstOrFail();
				}
				$city_id = optional($shipping_info_data)['city_id'] ?? optional($city)->id;//*/
			}else{
				$address = null;
				$city_id = Inventory::find($inventory_id)->city_id;
				$city = City::where('id', $city_id)->firstOrFail();				
			}
			


			DB::beginTransaction();

			// Create Order
			$order = $this->orderService->createOrder(
				$order_data,
				$user_id,
				$address_id,
				$branch_id,
				$city_id,
				$inventory_id,
				$lat,
				$long
			);


			if (Str::lower($shipping_info_data['type']) == 'xo-delivery' || Str::lower($shipping_info_data['type']) == 'kadmous') {
				$order_info = $this->orderService->storeShippingInfo(
					$shipping_info_data,
					$order->id,
					optional($address),
					$city_id,
					$city
				);
			}
			$response = $this->orderService->createOrderItem($order->id, $order_items_data, $city_id, $inventory_id);
			$final = $this->orderService->applyCouponAndGift($response, $order);
			//return $final['order']['paid_by_user'];
			//return $payment_method;
			if(($final['order']['paid_by_user'] != 0) && ($payment_method == "free")){
				throw new \Exception('something went wrong');
			}

			$free_shipping = 10000000;
			$key = Setting::where('key', 'fees')->firstOrFail();

			if ($key != null) {
				$key->value = json_decode($key->value);
				$fees = $key['value']->en->shipping_fee;
				$free_shipping = $key['value']->en->free_shipping;
			}

			if($order->total_price > $free_shipping){
				$order->update(['shipping_fee'=>0]);	
			}

			$balance = null;
			$percentage = null;
			$tt = 0;
			if($order->gift_id != null){
				$cpn = Coupon::find($order->gift_id);
				$balance = (int)$cpn->amount_off;
				$tt = $balance + (int)$order->covered_by_gift_card;
			}
			if($order->coupon_id != null){
				$percentage = Coupon::find($order->coupon_id)->percentage;
			}
			$invoice = Invoice::create([
				'order_id'=>$order->id,
				'user_id'=>$user_id,
				'shipment_id'=>$order->shipment->id,
				'total_price'=>$order->paid_by_user,
				'fees'=>$order->shipping_fee,
				'total_payment'=>$order->paid_by_user + $order->shipping_fee ,
				'invoice_number'=>$order->invoice_number,
				'gift_card_balance' => ($tt == 0)? null: $tt,
				'coupon_percenage' => $percentage? $percentage : null,
				'type' => 'new',
				//'order_items' => json_encode($order->order_items)
			]);

			$price_before_offers_and_discounts = 0;
			$order_items = $order->order_items()->get();
			foreach($order_items as $order_item){
				$price_before_offers_and_discounts += $order_item->original_price;
			}
			$order->update(['price_without_offers'=>$price_before_offers_and_discounts]);
			// $order_items[] = $this->orderService->createOrderItem(1, $order_items_data);
			// return $order_info;
			$links = Setting::where('key','links')->firstOrFail();
			$phone = json_decode($links->value,true)['phone'];
			$invoice_number = $order->invoice_number;

			DB::commit();
			$invoice_data = Order::select(['id', 'invoice_number','total_quantity', 'payment_method', 'total_price', 'paid_by_user','discounted_by_coupon','price_without_offers','covered_by_gift_card','gift_id', 'shipping_fee', 'total_quantity', 'created_at'])
				->where('id', $order->id)
				->firstOrFail()
				->load([
					'order_items:id,product_variation_id,order_id,quantity,original_price,price',
					'order_items.product_variation:id,product_id', // Load the product variation
					'order_items.product_variation.product:id,name', // Load the product through the product variation
					'shipment:id,order_id,type,receiver_first_name,date,time,receiver_last_name,city,street,neighborhood',
					'invoice:id,order_id,gift_card_balance,coupon_percenage'
				]);
			$invoice_data->discounted_price = $order->total_price - $order->discounted_by_coupon - $order->covered_by_gift_card;
			$data = [
				"amount" => $order->paid_by_user + $order->shipping_fee,
				"order_ref" => $order->id.'-'.$invoice->id,
				"lang" => "EN"
			];
			if ($final['order']['paid_by_user'] == 0) {
			
				$final['order']->update(['paid' => 1]);
			
				return response(
					["message" => "Gift card covered the whole order with fees", "amount" => 0, "order_id" => $order->id,'phone' => $phone,'invoice_number' => $invoice_number, 'invoice_data' => $invoice_data],
					201
				);
			}

			else {

				if ($payment_method == "ecash" || $payment_method == "payment_method_3") {

					$full_url = $this->ecashPaymentService->sendPayment($data);

					return response()->json(
						['data' => $full_url,
						 'phone' => $phone,
						 "order_id" => $order->id,
						 'invoice_number' => $invoice_number,
						 'invoice_data' => $invoice_data],
						Response::HTTP_CREATED
					);
				} elseif ($payment_method == "mtn-cash" || $payment_method == "payment_method_2") {
					return response(
						["message" => "You will be redircted to MTN Cash", "amount" => ($order->paid_by_user + $order->shipping_fee), "order_id" => $order->id,'phone' => $phone,'invoice_number' => $invoice_number, 'invoice_data' => $invoice_data],
						201
					);
				} elseif ($payment_method == "syriatel-cash" || $payment_method == "payment_method_1") {

					return response(
						["message" => "You will be redircted to Syriatel Cash", "amount" => ($order->paid_by_user + $order->shipping_fee) ,"order_id" => $order->id,'phone' => $phone,'invoice_number' => $invoice_number, 'invoice_data' => $invoice_data],
						201
					);
				} elseif ($payment_method == "cod") {

					$response = $this->orderService->cashOnDelivery($data);
				
					return response(
						["data" => "You'order was created successfully","order_id" => $order->id, 'phone' => $phone,'invoice_number' => $invoice_number, 'invoice_data' => $invoice_data],
						201
					);
				}
			}

		

		} catch (\Exception $e) {
			DB::rollback();
			return response()->error(
				[
					"message" => $e->getMessage(),
					"line" => $e->getLine(),
					"file" => $e->getFile()
				],
				Response::HTTP_BAD_REQUEST
			);
		}
	}

	/**
     * Display the specified resource.
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
	public function show(Order $order)
	{
		try {
			$order = $this->orderService->getOrder(request('order_id'));

			return response()->success(
				$order,
				Response::HTTP_FOUND
			);
		} catch (InvalidArgumentException $e) {
			return response()->error(
				$e->getMessage(),
				Response::HTTP_NOT_FOUND
			);
		}
	}

	/**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */


	/**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */


	public function getPrice(GetPriceRequest $request)
	{
		// $user_id = auth('sanctum')->user()->id;



		try {
		
			$validated_data = $request->validated();
			$order_items_data = $validated_data['order_items'];

			$response = $this->orderService->calculatePrice($order_items_data);
			$gift = 0;
			$code = 0;
			if ($request['coupon']) {
				$code = $validated_data['coupon'];		
			}
			if ($request['gift']) {
				$gift = $validated_data['gift'];		
			}
			if ($code || $gift ) {
				// $code = $validated_data['coupon'];
				return $final = $this->orderService->calculateDiscounted($response, $code, $gift);
			} else {
				return $response;
			}
		} catch (\Exception $e) {
			return response()->error(
				[
					"message" => $e->getMessage(),
					"line" => $e->getLine(),
					"file" => $e->getFile()
				],
				Response::HTTP_BAD_REQUEST
			);
		}
	}

	public function cancelOrder(Request $request)
	{
		try {
		
			$response = $this->orderService->cancelOrder($auth('sanctum')->id(), request('order_id'));
			return response()->success(
				[
					$response
				],
				Response::HTTP_OK
			);
		} catch (\Exception $e) {
			return response()->error(
				[
					"message" => $e->getMessage(),
					"line" => $e->getLine(),
					"file" => $e->getFile()
				],
				Response::HTTP_BAD_REQUEST
			);
		}
	}


	public function checkAvailableInCity(CheckAvailableInCityRequest $request)
	{


		try {
	
			$response = $this->orderService->checkAvailableInCity($request->validated('order_items'), $request->validated('city_id'));
			return response()->success(
				[
					"available" => $response
				],
				Response::HTTP_OK
			);
		} catch (\Exception $e) {
			return response()->error(
				[
					"message" => $e->getMessage(),
				],
				Response::HTTP_BAD_REQUEST
			);
		}
	}

	public function checkAvailable(CheckAvailableCityRequest $request)
	{


		try {

			$response = $this->orderService->checkAvailable($request->validated('order_items'));
			return response()->success(
				[
					"available" => $response
				],
				Response::HTTP_OK
			);
		} catch (\Exception $e) {
			return response()->error(
				[
					"message" => $e->getMessage(),
					//"line" => $e->getLine(),
					//"file" => $e->getFile()
				],
				Response::HTTP_BAD_REQUEST
			);
		}
	}

	public function dates()
	{
		// Create an array of three dates

		
		
		try {
		$dates = [
			now()->addDays(2),
			now()->addDays(3),
			now()->addDays(4),
		];
			
			return response()->success(
			$dates,
			Response::HTTP_OK
		);
		}
		catch (\Exception $e) {
			return response()->error(
				[
					"message" => $e->getMessage(),
					//"line" => $e->getLine(),
					//"file" => $e->getFile()
				],
				Response::HTTP_BAD_REQUEST
			);
		}
		
		// Return the dates as JSON

	}



}
