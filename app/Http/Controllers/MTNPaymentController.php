<?php

namespace App\Http\Controllers;

use App\Http\Requests\MTNPayment\PaymentConfirmationRequest;
use App\Http\Requests\MTNPayment\PaymentInitiateRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use App\Models\Transaction;
use Exception;
use App\Models\Coupon;
use App\Http\Requests\MTNPayment\CreateInvoiceRequest;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\FcmToken;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\StockLevel;
use Validator;
use Crypt;
use DB;
use App\Rules\OrderAmount;
use App\Traits\FirebaseNotificationTrait;

class MTNPaymentController extends Controller
{
	use FirebaseNotificationTrait;
	
    protected $private_key;

    public function __construct()
    {
		
		//dd(env('PRIVATE_KEY_PATH'));
        $this->private_key = file_get_contents(env('PRIVATE_KEY_PATH'));
    }


    public function confirmPayment(PaymentConfirmationRequest $request)
    {
        

		/*	Log::debug('request: '.$request);
			Log::debug('-----------------------------------');
			Log::debug('X-Guid: type: '.gettype($request->header('X-Guid')).' ,value: '.$request->header('X-Guid'));
			Log::debug('-----------------------------------');
			Log::debug('OperationNumber: type: '.gettype($request->header('X-OperationNumber')).' ,value: '.$request->header('X-OperationNumber'));
			Log::debug('-----------------------------------');
			Log::debug('Invoice: type: '.gettype($request->header('X-Invoice')).' ,value: '.$request->header('X-Invoice'));
			Log::debug('-----------------------------------');*/

            $code = base64_encode(hash('sha256', $request->validated('Code'), true));


            $json_body_parameter = [
                'Phone' => $request->validated('Phone'),
                'Guid' => $request->header('X-Guid') ?? '',
                'OperationNumber' => (int) $request->header('X-OperationNumber') ?? '',
                'Invoice' => (int) $request->header('X-Invoice') ?? '',
                'Code' => $code

            ];


            $jsonString = json_encode($json_body_parameter, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
		//	Log::debug('$jsonString: '.$jsonString);
		//	Log::debug('-----------------------------------');
            $privateKeyResource = openssl_pkey_get_private($this->private_key);
		
            openssl_sign($jsonString, $signature, $privateKeyResource, OPENSSL_ALGO_SHA256);

            $encryptedString = base64_encode($signature);
			//Log::debug('$encryptedString: '.$encryptedString);
			//Log::debug('-----------------------------------');
			///return "end of testing";
// dd($encryptedString);

            $full_url = "https://cashmobile.mtnsyr.com:9000/pos_web/payment_phone/confirm";
            // Create a new Guzzle client
            $client = new Client([
                'verify' => true, // Disable SSL verification
            ]);

            $response = $client->post($full_url, [
                'headers' => [
                    'Request-Name' => 'pos_web/payment_phone/confirm',
                    'X-Signature' => $encryptedString,
                    'Subject' => '9001000000048983',
                    'Accept-Language' => 'en'
                ],
                'json' => [
                    'Phone' => $request->validated('Phone'),
                    'Guid' => $request->header('X-Guid') ?? '',
                    'OperationNumber' => (int) $request->header('X-OperationNumber') ?? '',
                    'Invoice' => (int) $request->header('X-Invoice')?? '',
                    'Code' => $code
                ],
                'stream' => true,


            ]);
			
            $data = json_decode($response->getBody(), true);
		
		
	
			/*
			if ($response->getStatusCode() == 200 && $data['Errno'] == 0) {
				return response()->json('test completed');
			}
			else{
				return response()->json('error:'. $data['Errno']);
			}*/
            // Check the response status and return the response body
            if ($response->getStatusCode() == 200 && $data['Errno'] == 0) {

                DB::beginTransaction();


                if ($request->filled('gift_id') && $request->filled('gift_code')) {



                    $coupon = Coupon::where('id', $request->gift_id)
                        ->where('code', $request->gift_code)
                        ->firstOrFail();

                    if (!$coupon->valid && $coupon->amount_off == 0) {

                        $coupon->amount_off = Crypt::encryptString((int)$request->header('X-Amount'));
                        $coupon->valid = 1;
                    } else if ($coupon->valid == 1 && $coupon->amount_off != 0) {
                        $new_amount = (int)$coupon->amount_off + (int)$request->header('X-Amount');
                        $coupon->amount_off = Crypt::encryptString($new_amount);
                    }
					$coupon->last_recharge = now()->format('Y-m-d H:i:s');
                    $coupon->save();
                }

                if ($request->filled('order_id')) {

                    $order = Order::findOrFail($request->order_id)->load('coupon');
					if($order->status != 'processing'){

return response()->error(['message' => 'Something went wrong'],400);
}
                    $order_id = $order->id;
                    $inventory_id = Order::where('id', $order_id)->firstOrFail()->inventory_id;
                    $product_variatins_ids = OrderItem::select(['id', 'product_variation_id', 'quantity', 'to_inventory'])->where([['order_id', $order_id],['status','new']])->get();
                    foreach ($product_variatins_ids as $pv) {
                        $stock = StockLevel::where([['inventory_id', $pv->to_inventory], ['product_variation_id', $pv->product_variation_id]])->first();
                        $stock->update([
                            'on_hold' => $stock->on_hold - $pv->quantity,
                            'sold_quantity' => $stock->sold_quantity + $pv->quantity
                        ]);
                    }
                    $order->update([
                        'paid' => 1
                    ]);
					
					$original = Order::find($order->original_order_id);
					if($original){
						$original->update(['status'=>'replaced']);	
					}
					
					
				
					

                    if (!is_null($order->coupon_id)) {

                        // $coupon = Coupon::findOrFail($order->coupon_id);
                        $order->coupon->used_redemption += 1;
                        $order->coupon->save();
                    }
                }
				
				if ($request->filled('replacing_order_id')) {

                    $order = Order::findOrFail($request->replacing_order_id)->load('coupon');
					if($order->status != 'processing'){

return response()->error(['message' => 'Something went processing'],400);
}
                    $order_id = $order->id;
                    $inventory_id = Order::where('id', $order_id)->first()->inventory_id;
                    $product_variatins_ids = OrderItem::select(['id', 'product_variation_id', 'quantity', 'to_inventory'])->where('order_id', $order_id)->where('status','new')->get();
                    foreach ($product_variatins_ids as $pv) {
                        $stock = StockLevel::where([['inventory_id', $pv->to_inventory], ['product_variation_id', $pv->product_variation_id]])->firstOrFail();
                        $stock->update([
                            'on_hold' => $stock->on_hold - $pv->quantity,
                            'sold_quantity' => $stock->sold_quantity + $pv->quantity
                        ]);
                    }
                    $order->update([
                        'paid' => 1
                    ]);
					
					$employee = Employee::where('inventory_id',$order->inventory_id)->whereHas('account', function ($query) {
						$query->whereHas('roles', function ($query) {
							$query->where('name','warehouse_manager');	
						});
					})->first();
					
					if($employee){				
						$title = ["ar"=>"تم إنشاء طلب شراء جديد",
						"en"=>"A new order was created"];

						$body = ["ar"=>"تم إنشاء طلب شراء جديد",
						"en"=>"A new order was created"];

						$fcm_tokens = $employee->fcm_tokens()->pluck('fcm_token')->toArray();

						foreach($fcm_tokens as $fcm){
							$fcm_token = FcmToken::where([['fcm_token', $fcm],['employee_id',$employee->id]])->first();
								if($fcm_token->lang == 'en'){
									$this->send_notification($fcm, 
															 'A new order was created',
															 'A new order was created', 
															 'dashboard_orders', 
															 'flutter_app'); // key source	
								}else{
									$this->send_notification($fcm, 
															 'تم إنشاء طلب شراء جديد',
															 'تم إنشاء طلب شراء جديد',
															 'dashboard_orders', 
															 'flutter_app'); // key source
								}	
						}
						$employee->notifications()->create([
							'employee_id'=>$employee->id,
							'type'=> "dashboard_orders", // 1 is to redirect to the orders page
							'title'=>$title,
							'body'=>$body
						]);	
					}

                    if (!is_null($order->coupon_id)) {

                        // $coupon = Coupon::findOrFail($order->coupon_id);
                        $order->coupon->used_redemption += 1;
                        $order->coupon->save();
                    }
                }

                $transaction = Transaction::where('transaction_uuid', $request->header('X-Guid'))->firstOrFail();

                if (!$transaction) {

                    throw new Exception('Something Went transaction_uuid', 404);
                } else {

                    if ($transaction->status == 'pending') {

                        $transaction->update(['status' => 'completed']);

                    } else {

                        return response()->error(['message' => 'Something went completed'], 400);
                    }

                    DB::commit();

                    if ($transaction->gift_id != null) {

                        return response()->success([
                            'message' => 'Transaction Completed Successfully, you can use your gift card freely',

                            // 'transaction' => $transaction,
                            'coupon' => $coupon,


                        ], 200);
                    } else if ($transaction->order_id != null) {

                        return response()->success([
                            'message' => 'Transaction Completed Successfully, your order is being processed',

                            // 'transaction' => $transaction,
                            // 'coupon' => $coupon,


                        ], 200);
                    }
                }
				
            }elseif($data['Error'] == "Incorrect request signature"){
					return response()->error(['message'=>"Network error, please try again later"], 400);	
				} else {

                return response()->error(['message'=>$data['Error']], 400);
                //return response()->error($data, 400);
            }
    }



    public function activate(Request $request)
    {



        $private_key = env('PRIVATE_KEY_PATH');
        $private_key = file_get_contents($private_key);
        $publicKey = env('PUBLIC_KEY_PATH');

        $publicKey = file_get_contents($publicKey);
        $publicKey = trim(str_replace(["\n", "-----BEGIN PUBLIC KEY-----", "-----END PUBLIC KEY-----"], "", $publicKey));



        $jsonBody = [
            'Secret' => "18263555",
            'Serial' => "9001000000048983",
            'Key' => $publicKey
        ];

        $jsonString = json_encode($jsonBody, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        $hashedString = hash('sha256', $jsonString, true);

        // Encrypt the hashed string using the private key
        openssl_private_encrypt($hashedString, $signature, $private_key);

        // Base64 encode the encrypted data
        $encryptedString = base64_encode($signature);


        $client = new Client([
            'verify' => true, // Disable SSL verification
        ]);
        $full_url = "https://cashmobile.mtnsyr.com:9000/pos_web/invoice/create";
        $response = $client->post($full_url, [
            'headers' => [
                'Request-Name' => 'pos_web/pos/activate',
                'X-Signature' => $encryptedString,
                'Subject' => "9001000000048983",
                'Accept-Language' => 'en'
            ],
            'json' => [
                'Secret' => "18263555",
                'Serial' => "9001000000048983",
                'Key' => $publicKey
            ],
            'stream' => false,

        ]);



        return $response->getBody();
    }

    public function initiatePayment(Request $request)
    {

        $validate = Validator::make(
            $request->only('gift_id', 'order_id', 'gift_code', 'Amount', 'Phone'),
            [
                'gift_id' => 'required_with:gift_code|integer|exists:coupons,id',
                'order_id' => ['sometimes', 'integer', 'exists:orders,id', 'filled'],
                'gift_code' => 'required_with:gift_id|string|exists:coupons,code',
                'Amount' => 'required|integer|min:1',
                'Phone' => 'required|string|digits:12'

            ]
        );

        if ($validate->fails()) {
            return response()->error($validate->errors(), 422);
        }

        $create_invoice_data = $validate->validated();


        $create_invoice_data['X-Amount'] = $request->header('X-Amount') ?? null;

        $create_invoice = $this->createInvoice($create_invoice_data);

        $invoice_number = $create_invoice['Receipt']['Invoice'];

        $guid = Str::random(5);

        $json_body_parameter = [
            'Phone' => $validate->validated()['Phone'], //$request->validated('Phone'),
            'Invoice' => $invoice_number, // (int) $request->header('X-Invoice') ?? '',
            'Guid' => $guid
        ];


        $jsonString = json_encode($json_body_parameter, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $privateKeyResource = openssl_pkey_get_private($this->private_key);





        openssl_sign($jsonString, $signature, $privateKeyResource, OPENSSL_ALGO_SHA256);
        //$signature = hash('sha256',  $signature);
        // Encrypt the hashed string using the private key
        //openssl_private_encrypt($hashedString, $signature, $private_key);

        // Base64 encode the encrypted data
        $encryptedString = base64_encode($signature);

        $full_url = "https://cashmobile.mtnsyr.com:9000/pos_web/payment_phone/initiate";

        // Create a new Guzzle client
        $client = new Client([
            'verify' => true, // Disable SSL verification
        ]);

        // Make the HTTP POST request using Guzzle's Client
        $response = $client->post($full_url, [
            'headers' => [
                'Request-Name' => 'pos_web/payment_phone/initiate',
                'X-Signature' => $encryptedString,
                'Subject' => '9001000000048983',
                'Accept-Language' => 'en'
            ],
            'json' => [
                'Phone' => $validate->validated()['Phone'], //$request->validated('Phone'),
                'Invoice' => $invoice_number, //(int)$request->header('X-Invoice'),
                'Guid' => $guid
            ],
            'stream' => true,


        ]);

        $data = json_decode($response->getBody(), true);
        // Check the response status and return the response body
        if ($response->getStatusCode() == 200 && $data['Errno'] == 0) {


            if ((isset($validate->validated()['gift_id']) && $validate->validated()['gift_id']) && (isset($validate->validated()['gift_code']) && $validate->validated()['gift_code'])) {

                //$operation_type = 'create-gift-card';

                Transaction::create([
                    'user_id' => auth('sanctum')->user()->id,
                    'gift_id' => $request->gift_id,
                    'transaction_uuid' => $guid,
                    'operation_type' => 'create-gift-card',
                    'payment_method' => 'mtn-cash',
                    'amount' => $request->header('X-Amount') ? $request->header('X-Amount') : /*$request->validated('Amount')*/ $validate->validated()['Amount'],
                    'status' => 'pending',

                    // Assuming you have a transaction_id column in your Transaction model
                    // 'token' => $token, // Assuming you want to store the token in the Transaction model
                ]);
            } else if (isset($validate->validated()['order_id']) && $validate->validated()['order_id']) {

       $order = Order::findOrFail($validate->validated()['order_id']);
				
				if( $order->gift_id != null && $order->covered_by_gift_card !== 0){
				 
				
				     Transaction::create([
                    'user_id' => auth('sanctum')->user()->id,
                    'order_id' => $request->order_id,
				    'gift_id' => $order->gift_id,
                    'transaction_uuid' => $guid,
                    'operation_type' => 'useage-gift-card',
                    'payment_method' => 'mtn-cash',
                    'amount' => $request->header('X-Amount') ? $request->header('X-Amount') : /*$request->validated('amount')*/ $validate->validated()['Amount'],
                    'status' => 'pending', // Assuming you have a transaction_id column in your Transaction model
                    // 'token' => $token, // Assuming you want to store the token in the Transaction model
                ]);
				
				}
				
				else {
				
				      Transaction::create([
                    'user_id' => auth('sanctum')->user()->id,
                    'order_id' => $request->order_id,
                    'transaction_uuid' => $guid,
                    'operation_type' => 'order',
                    'payment_method' => 'mtn-cash',
                    'amount' => $request->header('X-Amount') ? $request->header('X-Amount') : /*$request->validated('amount')*/ $validate->validated()['Amount'],
                    'status' => 'pending', // Assuming you have a transaction_id column in your Transaction model
                    // 'token' => $token, // Assuming you want to store the token in the Transaction model
                ]);
				
				}
				

 
            }

            //return json_decode([  $response->getBody(), true);

            return response()->success(['create_invoice' => $create_invoice, 'initiate_payment' => $data, 'guid' => $guid, /*'transaction' => $transaction*/], 200);
        } else {
            // Handle the error as needed
            return response()->json(['error' => 'Failed to create invoice'], 500);
        }
    }

    public function createInvoice(array $invoice_data)
    {
        /*  $rules = [
              'Amount' => 'nullable|integer|not_i',
              'X-Amount' => 'nullable|integer',
          ];


          $x_amount = $request->header('X-Amount');
  */
        // Validate the request data
        //  $validator = Validator::make([$request->only('Amount'), $x_amount], $rules);

        // Check if the validation fails
        /*  if ($validator->fails()) {
              // Handle the validation errors
              // For example, return a response with the validation errors
              return response()->json($validator->errors(), 422);
          }
  */


        //$amount = $request->validated('Amount') ? $request->validated('Amount') * 100 : ($request->header('X-Amount') * 100);
        $amount = $invoice_data['Amount'] ? (int) $invoice_data['Amount'] : (int) ($invoice_data['X-Amount']);

        $invoice_number = rand(10000, 99999);




        // Prepare the JSON body
        $json_body_parameter = [
            'Amount' => $amount,
            'Invoice' => $invoice_number,
            'TTL' => 15
        ];


        $jsonString = json_encode($json_body_parameter, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $privateKeyResource = openssl_pkey_get_private($this->private_key);



        // Hash the JSON body

        openssl_sign($jsonString, $signature, $privateKeyResource, OPENSSL_ALGO_SHA256);

        // Encrypt the hashed string using the private key
        //openssl_private_encrypt($hashedString, $signature, $private_key);

        // Base64 encode the encrypted data
        $encryptedString = base64_encode($signature);

        // Define the full URL
        $full_url = "https://cashmobile.mtnsyr.com:9000/pos_web/invoice/create";

        // Create a new Guzzle client
        $client = new Client([
            'verify' => true, // Disable SSL verification
        ]);

        // Make the HTTP POST request using Guzzle's Client
        $response = $client->post($full_url, [
            'headers' => [
                'Request-Name' => 'pos_web/invoice/create',
                'X-Signature' => $encryptedString,
                'Subject' => '9001000000048983',
                'Accept-Language' => 'en'
            ],
            'json' => [
                'Amount' => $amount,
                'Invoice' => $invoice_number,
                'TTL' => 15
            ],
            'stream' => true,


        ]);


        // Check the response status and return the response body
        if ($response->getStatusCode() == 200) {
            return json_decode($response->getBody(), true);
        } else {
            // Handle the error as needed
            return response()->json(['error' => 'Failed to create invoice'], 500);
        }
    }

    public function initiateRefund(Request $request)
    {
        $invoice_number = rand(10000, 99999);
        $json_body_parameter = [

            'Invoice' => $invoice_number

        ];
        $jsonString = json_encode($json_body_parameter, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $privateKeyResource = openssl_pkey_get_private($this->private_key);



        // Hash the JSON body

        openssl_sign($jsonString, $signature, $privateKeyResource, OPENSSL_ALGO_SHA256);
        $encryptedString = base64_encode($signature);

        $full_url = "https://cashmobile.mtnsyr.com:9000/pos_web/invoice/refund/initiate";

        // Create a new Guzzle client
        $client = new Client([
            'verify' => true, // Disable SSL verification
        ]);

        // Make the HTTP POST request using Guzzle's Client
        $response = $client->post($full_url, [
            'headers' => [
                'Request-Name' => 'pos_web/invoice/refund/initiate',
                'X-Signature' => $encryptedString,
                'Subject' => '9001000000048983',
                'Accept-Language' => 'en'
            ],
            'json' => [

                'Invoice' => $invoice_number

            ],
            'stream' => false,


        ]);
        if ($response->successful()) {
            return json_decode($response->getBody(), true);
        } else {
            // Handle the error as needed
            return response()->json(['error' => 'Failed to create refund'], 500);
        }
    }

    public function confirmRefund(Request $request)
    {
        $json_body_parameter = [

            'Invoice' => (int) $request->header('X-Invoice') ?? ''

        ];
        $jsonString = json_encode($json_body_parameter, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $privateKeyResource = openssl_pkey_get_private($this->private_key);



        // Hash the JSON body

        openssl_sign($jsonString, $signature, $privateKeyResource, OPENSSL_ALGO_SHA256);

        $encryptedString = base64_encode($signature);
        $full_url = "https://cashmobile.mtnsyr.com:9000/pos_web/invoice/refund/confirm";

        // Create a new Guzzle client
        $client = new Client([
            'verify' => true, // Disable SSL verification
        ]);

        // Make the HTTP POST request using Guzzle's Client
        $response = $client->post($full_url, [
            'headers' => [
                'Request-Name' => 'pos_web/invoice/refund/confirm',
                'X-Signature' => $encryptedString,
                'Subject' => '9001000000048983',
                'Accept-Language' => 'en'
            ],
            'json' => [

                'Invoice' => (int) $request->header('X-Invoice') ?? ''

            ],
            'stream' => false,


        ]);
        if ($response->getStatusCode() == 200) {
            return json_decode($response->getBody(), true);
        } else {
            // Handle the error as needed
            return response()->json(['error' => 'Failed to create refund'], 500);
        }
    }

    public function cancelRefund(Request $request)
    {
        $json_body_parameter = [

            'Invoice' => (int) $request->header('X-Invoice') ?? ''

        ];
        $jsonString = json_encode($json_body_parameter, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $privateKeyResource = openssl_pkey_get_private($this->private_key);



        // Hash the JSON body

        openssl_sign($jsonString, $signature, $privateKeyResource, OPENSSL_ALGO_SHA256);

        $encryptedString = base64_encode($signature);
        $full_url = "https://cashmobile.mtnsyr.com:9000/pos_web/invoice/refund/confirm";

        // Create a new Guzzle client
        $client = new Client([
            'verify' => true, // Disable SSL verification
        ]);

        // Make the HTTP POST request using Guzzle's Client
        $response = $client->post($full_url, [
            'headers' => [
                'Request-Name' => 'pos_web/invoice/refund/confirm',
                'X-Signature' => $encryptedString,
                'Subject' => '9001000000048983',
                'Accept-Language' => 'en'
            ],
            'json' => [

                'Invoice' => (int) $request->header('X-Invoice') ?? ''

            ],
            'stream' => false,


        ]);
        if ($response->getStatusCode() == 200) {
            return json_decode($response->getBody(), true);
        } else {
            // Handle the error as needed
            return response()->json(['error' => 'Failed to create refund'], 500);
        }
    }




    public function authenticateMerchant()
    {
        try {


            /*   $credintals = ['inputObj' => [
            'userName' => 'shafeech@xo',
            'password' => ' Mtn@123456789',
            'merchantGSM' => '963912345678'
        ]];/*
        $response = Http::withHeaders([
            'Content-Type' => 'application/x-www-form-urlencoded',


        ])->post('https://services.mtnsyr.com:2021/authenticateMerchant');


        return $response->json();*/

            /* ['inputObj' => [
            'userName' => 'shafeech@xo',
            'password' => ' Mtn@123456789',
            'merchantGSM' => '1963912345678'
        ]];


        $full_url  = 'https://services.mtnsyr.com:2021/authenticateMerchant';



        $response = Http::withHeaders([
              'Content-Type' => 'application/json',


        ])->post($full_url,$credintals);


    
    */
            //$ch = curl_init("https://Merchants.syriatel.sy:1443/ePayment_external_Json/rs/ePaymentExternalModule/getToken");
            //curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            //$data = curl_exec($ch);
            //echo($data);
            //echo 'Curl error: ' . curl_error($ch);

            // $ch = curl_init();
            // curl_setopt($ch, CURLOPT_URL, 
            // "https://Merchants.syriatel.sy:1443/ePayment_external_Json/rs/ePaymentExternalModule/getToken");
            // curl_setopt($ch, CURLOPT_HEADER, 0);
            // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE); 
            // if(curl_exec($ch) === false)
            // {
            // echo 'Curl error: ' . curl_error($ch);
            // }
            // else
            // {
            // echo 'Operation completed without any errors';
            // }
            // curl_close($ch);

            // }
            //$full_url = "https://Merchants.syriatel.sy:1443/ePayment_external_Json/rs/ePaymentExternalModule/getToken";
            //  return $response = Http::post( $full_url);
            // }



            $full_url = 'https://services.mtnsyr.com:2021/authenticateMerchant';

            $client = new Client([
                'verify' => true, // Disable SSL verification
            ]);

            $response = $client->post($full_url, [
                'json' => [
                    'inputObj' => [
                        'userName' => 'shafeech@xo',
                        'password' => ' Mtn@123456789',
                        'merchantGSM' => '963912345678'
                    ]
                ],
                'stream' => false
            ]);

            // Get the response body
            $response = json_decode($response->getBody(), true);

            // You can then return or use $responseBody as needed
            return $response;

            //  echo $response;

            // You can then return or use $responseBody as needed
            //return $response;


        } catch (\Exception $e) {

            return $e;
        }
    }
	
	    public function test(Request $request)
    {

        $validate = Validator::make(
            $request->only('gift_id', 'order_id', 'gift_code', 'Amount', 'Phone'),
            [
                'gift_id' => 'required_with:gift_code|integer|exists:coupons,id',
                'order_id' => ['sometimes', 'integer', 'exists:orders,id', 'filled'],
                'gift_code' => 'required_with:gift_id|string|exists:coupons,code',
                'Amount' => 'required|integer|min:1',
                'Phone' => 'required|string|digits:12'

            ]
        );

        if ($validate->fails()) {
            return response()->error($validate->errors(), 422);
        }

        $guid = Str::random(5);

        if ((isset($validate->validated()['gift_id']) && $validate->validated()['gift_id']) && (isset($validate->validated()['gift_code']) && $validate->validated()['gift_code'])) {

            //$operation_type = 'create-gift-card';

            Transaction::create([
                'user_id' => auth('sanctum')->user()->id,
                'gift_id' => $request->gift_id,
                'transaction_uuid' => $guid,
                'operation_type' => 'create-gift-card',
                'payment_method' => 'mtn-cash',
                'amount' => $request->header('X-Amount') ? $request->header('X-Amount') : /*$request->validated('Amount')*/ $validate->validated()['Amount'],
                'status' => 'pending',

                // Assuming you have a transaction_id column in your Transaction model
                // 'token' => $token, // Assuming you want to store the token in the Transaction model
            ]);
        } else if (isset($validate->validated()['order_id']) && $validate->validated()['order_id']) {

            $order = Order::findOrFail($validate->validated()['order_id']);

            if ($order->gift_id != null && $order->covered_by_gift_card !== 0) {


                Transaction::create([
                    'user_id' => $order->user_id,
                    'order_id' => $request->order_id,
                    'gift_id' => $order->gift_id,
                    'transaction_uuid' => $guid,
                    'operation_type' => 'useage-gift-card',
                    'payment_method' => 'mtn-cash',
                    'amount' => $request->header('X-Amount') ? $request->header('X-Amount') : /*$request->validated('amount')*/ $validate->validated()['Amount'],
                    'status' => 'pending', // Assuming you have a transaction_id column in your Transaction model
                    // 'token' => $token, // Assuming you want to store the token in the Transaction model
                ]);
            } else {

                Transaction::create([
                    'user_id' => $order->user_id,
                    'order_id' => $request->order_id,
                    'transaction_uuid' => $guid,
                    'operation_type' => 'order',
                    'payment_method' => 'mtn-cash',
                    'amount' => $request->header('X-Amount') ? $request->header('X-Amount') : /*$request->validated('amount')*/ $validate->validated()['Amount'],
                    'status' => 'pending', // Assuming you have a transaction_id column in your Transaction model
                    // 'token' => $token, // Assuming you want to store the token in the Transaction model
                ]);
            }
        }

        DB::beginTransaction();


        if ($request->filled('gift_id') && $request->filled('gift_code')) {



            $coupon = Coupon::where('id', $request->gift_id)
                ->where('code', $request->gift_code)
                ->firstOrFail();

            if (!$coupon->valid && $coupon->amount_off == 0) {

                $coupon->amount_off = Crypt::encryptString($validate->validated()['Amount']);
                $coupon->valid = 1;
            } else if ($coupon->valid == 1 && $coupon->amount_off != 0) {
                $new_amount = $coupon->amount_off + $request->header('X-Amount');
                $coupon->amount_off = Crypt::encryptString($new_amount);
            }
            $coupon->last_recharge = now()->format('Y-m-d H:i:s');
            $coupon->save();
        }

        if ($request->filled('order_id')) {

            $order = Order::findOrFail($request->order_id)->load('coupon');
            if ($order->status != 'processing') {

                return response()->error(['message' => 'Something went wrong'], 400);
            }
            $order_id = $order->id;
            $product_variatins_ids = OrderItem::select(['id', 'product_variation_id', 'quantity', 'to_inventory'])->where([['order_id', $order_id], ['status', 'new']])->get();
            foreach ($product_variatins_ids as $pv) {
                $stock = StockLevel::where([['inventory_id', $pv->to_inventory], ['product_variation_id', $pv->product_variation_id]])->first();
                $stock->update([
                    'on_hold' => $stock->on_hold - $pv->quantity,
                    'sold_quantity' => $stock->sold_quantity + $pv->quantity
                ]);
            }
            $order->update([
                'paid' => 1
            ]);

            $original = Order::find($order->original_order_id);
            if ($original) {
                $original->update(['status' => 'replaced']);
            }

            if (!is_null($order->coupon_id)) {

                // $coupon = Coupon::findOrFail($order->coupon_id);
                $order->coupon->used_redemption += 1;
                $order->coupon->save();
            }
        }

        $transaction = Transaction::where('transaction_uuid', $guid)->firstOrFail();

        if (!$transaction) {

            throw new Exception('Something Went transaction_uuid', 404);
        } else {

            if ($transaction->status == 'pending') {

                $transaction->update(['status' => 'completed']);
            } else {

                return response()->error(['message' => 'Something went completed'], 400);
            }

            DB::commit();

            if ($transaction->gift_id != null) {

                return response()->success([
                    'message' => 'Transaction Completed Successfully, you can use your gift card freely',

                    // 'transaction' => $transaction,
                    'coupon' => $coupon,


                ], 200);
            } else if ($transaction->order_id != null) {

                return response()->success([
                    'message' => 'Transaction Completed Successfully, your order is being processed',

                    // 'transaction' => $transaction,
                    // 'coupon' => $coupon,


                ], 200);
            }
        }
    }

}
