<?php

namespace App\Http\Controllers\Users\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CheckAvailableInCityRequest;
use App\Models\Address;
use App\Models\Coupon;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Setting;
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
        $user = auth('sanctum')->user();
        if (!$user) {
            return response()->error('Unauthorized', 403);
        }
        $user_id = $user->id;
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

        $order = $this->orderService->getAllUserOrders($user_id, $filter_data, $sort_data, $page_size);

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

    public function store(Request $request)
    {

        $user = auth('sanctum')->user();
        if (!$user) {
            return response()->json('Unauthorized', 403);
        }
        $user_id = $user->id;

        // $user_id = 1;

        try {
            $validate = Validator::make(
                $request->all(),
                [
                    'address_id' => 'required_if:shipping_info.*.lat,null,shipping_info.*.long,null,shipping_info.*.city,null,shipping_info.*.city_id,null,shipping_info.*.street,null,shipping_info.*.neighborhood,null|exists:addresses,id',
                    'branch_id' => [
                        Rule::when(function ($input) {
                            return $input['shipping_info'][0]['type'] == 'kadmous';
                        }, 'required|exists:branches,id')
                    ],
                    'order' => 'required|array',
                    //'order.*.type' => 'sometimes|max:50', //delivery type
                    'order.*.payment_method' => 'required|max:30|in:ecash,syriatel-cash,mtn-cash,cod,Free,payment_method_1,payment_method_2,payment_method_3',
                    'order.*.coupon' => 'sometimes|max:8',
                    'order.*.is_gift' => 'nullable|boolean',
                    'order.*.gift_message' => 'nullable|string|max:255',
                    'order.*.gift_card' => 'sometimes|max:8',
                    'order.*.gift_card_password' => 'sometimes|max:50',
                    // 'order.*.total_quantity' => 'required|integer',
                    // 'order.*.shipping_fee' => 'required|integer',
                    'order.*.qr_code' => 'required|max:255', //  to delete

                    'order_items' => 'required|array',
                    'order_items.*.product_variation_id' => 'required|integer|exists:product_variations,id',
                    'order_items.*.quantity' => 'required|integer|min:1',

                    'shipping_info' => 'array|required',
                    'shipping_info.*.type' => 'required|max:25',

                    'shipping_info.*.express' => [
                        Rule::when(function ($input) {
                            return $input['shipping_info'][0]['type'] == 'xo-delivery';
                        }, 'required_without:shipping_info.*.data|boolean')
                    ],
                    'shipping_info.*.date' => [
                        Rule::when(function ($input) {
                            return $input['shipping_info'][0]['type'] == 'xo-delivery' && $input['shipping_info'][0]['express'] == false;
                        }, 'required|max:255')
                    ],
                    'shipping_info.*.time' => [
                        Rule::when(function ($input) {
                            return $input['shipping_info'][0]['type'] == 'xo-delivery' && $input['shipping_info'][0]['express'] == false;
                        }, 'required|max:255')
                    ],
                    'shipping_info.*.city' => 'required_without_all:shipping_info.*.city_id,address_id|string|max:255',
                    'shipping_info.*.city_id' => 'required_without_all:shipping_info.*.city,address_id|integer|exists:cities,id',
                    'shipping_info.*.street' => [
                        Rule::when(function ($input) {
                            return $input['shipping_info'][0]['type'] == 'xo-delivery';
                        }, 'required_without:address_id|max:255')
                    ],
                    'shipping_info.*.neighborhood' => [
                        Rule::when(function ($input) {
                            return $input['shipping_info'][0]['type'] == 'xo-delivery';
                        }, 'required_without:address_id|max:255')
                    ],
                    'shipping_info.*.lat' => 'required|sometimes|max:255',
                    'shipping_info.*.long' => 'required|sometimes|max:255',
                    'shipping_info.*.receiver_first_name' => 'required|max:255|string',
					'shipping_info.*.receiver_father_name' => [
                        Rule::when(function ($input) {
                            return $input['shipping_info'][0]['type'] == 'kadmous';
                        }, 'required|max:255|string')
                    ],
                    'shipping_info.*.receiver_last_name' => 'required|max:255|string',
                    'shipping_info.*.receiver_phone' => 'required|max:255|string',
                    'shipping_info.*.receiver_phone2' => 'nullable|sometimes|max:255',
                    'shipping_info.*.additional_details' => 'nullable|sometimes|max:255',
                ]
            );

            if ($validate->fails()) {
                // return response()->eroor(
                //     $validate->errors()
                // );
                return response()->error(
                    $validate->errors(),
                    422
                );
            }
          $validated_data = $validate->validated();

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
            $order_data = $validated_data['order'][0];
            $payment_method = Str::lower($order_data['payment_method']);
            $order_items_data = $validated_data['order_items'];
            $shipping_info_data = $validated_data['shipping_info'][0];
            $address_id = null;
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
            $city_id = optional($shipping_info_data)['city_id'] ?? optional($city)->id;

            $lat = $shipping_info_data['lat'] ?? 36.2167;
            $long = $shipping_info_data['long'] ?? 37.1667;
            DB::beginTransaction();

            // Create Order
            $order = $this->orderService->createOrder(
                $order_data,
                $user_id,
                $address_id,
                $branch_id,
                $city_id,
                $lat,
                $long
            );

           /* $amount = 0;
            $fees = Setting::where('key','fee')->firstOrFail();

if($fees['en']['free_shipping'] <= $order->paid_by_user){

$amount = $order->paid_by_user;

}

else {
    $amount = $order->paid_by_user + $fees['en']['shipping_fee'];

}
*/
            if (Str::lower($shipping_info_data['type']) == 'xo-delivery' || Str::lower($shipping_info_data['type']) == 'kadmous') {
                $order_info = $this->orderService->storeShippingInfo(
                    $shipping_info_data,
                    $order->id,
                    optional($address),
                    $city_id,
                    $city
                );
            }
            $response = $this->orderService->createOrderItem($order->id, $order_items_data, $city_id);
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
			
			$invoice = Invoice::create([
                'order_id'=>$order->id,
                'user_id'=>$user_id,
                'shipment_id'=>$order->shipment->id,
                'total_price'=>$order->paid_by_user,
                'fees'=>$order->shipping_fee,
                'total_payment'=>$order->paid_by_user + $order->shipping_fee ,
                'invoice_number'=>$order->invoice_number,
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
            $data = [
                "amount" => $order->paid_by_user + $order->shipping_fee,
                "order_ref" => $order->id.'-'.$invoice->id,
                "lang" => "EN"
            ];
				if ($final['order']['paid_by_user'] == 0) {
					//$gift_card->amount_off = Crypt::encrypt($gift_card->amount_off - $paid_by_user);
					//$gift_card->save();
					// Crypt::encryptString(Crypt::decryptString($final['gift_card']->amount_off) - $final['covered_by_gift_card']));
					//$final['gift_card']->amount_off = Crypt::encryptString(Crypt::decryptString($final['gift_card']->amount_off) - $final['covered_by_gift_card']);
					//$final['gift_card']->save();
					$final['order']->update(['paid' => 1]);
					return response(
                        ["message" => "Gift card covered the whole order with fees", "amount" => ($order->paid_by_user + $order->shipping_fee), "order_id" => $order->id,'phone' => $phone,'invoice_number' => $invoice_number],
                        201
                    );
					//return response()->success([
					//	"Gift card covered the whole order with fees"],
					//	Response::HTTP_CREATED
					//);
				}
				
             else {

                if ($payment_method == "ecash" || $payment_method == "payment_method_3") {

                    $full_url = $this->ecashPaymentService->sendPayment($data);

                    return response()->json(
                        ['data' => $full_url,
						'phone' => $phone,
						'invoice_number' => $invoice_number],
                        Response::HTTP_CREATED
                    );
                } elseif ($payment_method == "mtn-cash" || $payment_method == "payment_method_2") {
                    return response(
                        ["message" => "You will be redircted to MTN Cash", "amount" => ($order->paid_by_user + $order->shipping_fee), "order_id" => $order->id,'phone' => $phone,'invoice_number' => $invoice_number],
                        201
                    );
                } elseif ($payment_method == "syriatel-cash" || $payment_method == "payment_method_1") {

                    return response(
                        ["message" => "You will be redircted to Syriatel Cash", "amount" => ($order->paid_by_user + $order->shipping_fee) ,"order_id" => $order->id,'phone' => $phone,'invoice_number' => $invoice_number],
                        201
                    );
                } elseif ($payment_method == "cod") {

                    $response = $this->orderService->cashOnDelivery($data);
					return response(
                        ["data" => "You'order was created successfully",'phone' => $phone,'invoice_number' => $invoice_number],
                        201
                    );
                }
            }

            // $order = $order->load('order_items');
            // return response()->success(
            //     $order,
            //     Response::HTTP_CREATED
            // );

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
            $order_id = request('order_id');
            $order = $this->orderService->getOrder($order_id);

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
    public function edit(Order $order)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Order $order)
    {
        //
    }

    public function getPrice(Request $request)
    {
        // $user_id = auth('sanctum')->user()->id;



        try {
            $validate = Validator::make(
                $request->all(),
                [
					'gift' => 'sometimes|max:8',
                    'coupon' => 'sometimes|max:8',
                    'order_items' => 'required|array',
                    'order_items.*.product_variation_id' => 'required|integer',
                    'order_items.*.quantity' => 'required|integer',
                ]
            );

            if ($validate->fails()) {
                // return response()->eroor(
                //     $validate->errors()
                // );
                return response()->error(
                    $validate->errors(),
                    422
                );
            }

            $validated_data = $validate->validated();
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
            $user = auth('sanctum')->user();
            if (!$user) {
                return response()->error('Unauthorized', 403);
            }
            $order_id = request('order_id');
            $response = $this->orderService->cancelOrder($user->id, $order_id);
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
        // $user_id = auth('sanctum')->user()->id;



        try {

            $validated_data = $request->validated();
            $order_items_data = $validated_data['order_items'];
            $city_id = $validated_data['city_id'];
            $response = $this->orderService->checkAvailableInCity($order_items_data, $city_id);
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
                    "line" => $e->getLine(),
                    "file" => $e->getFile()
                ],
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    public function checkAvailable(Request $request)
    {
         //    $user = auth('sanctum')->user();
        //         if(!$user){
        //         return response()->error('Unauthorized',403);
        //     }
        //     $user_id  = $user->id;

        try {
            $validated_data = $request->validate([
                'order_items' => 'required|array',
                'order_items.*.product_variation_id' => 'required|integer',
                'order_items.*.quantity' => 'required|integer',
            ]);

            $order_items_data = $validated_data['order_items'];



            $response = $this->orderService->checkAvailable($order_items_data);
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
                    "line" => $e->getLine(),
                    "file" => $e->getFile()
                ],
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    public function dates()
    {
        // Create an array of three dates
      
		$dates = [
            now()->addDays(2),
            now()->addDays(3),
            now()->addDays(4),
        ];

        // Return the dates as JSON
        return response()->success(
            $dates,
            Response::HTTP_OK
        );
    }

	
	
	
	
	
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function destroy(Order $order)
    {
        //
    }
}
