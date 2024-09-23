<?php

namespace App\Http\Controllers;

use App\Http\Requests\SyriatelPayment\PaymentConfirmationRequest;
use App\Http\Requests\SyriatelPayment\PaymentReqRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use GuzzleHttp\Client;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Exception;
use App\Models\Coupon;
use App\Models\Employee;
use App\Models\FcmToken;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\StockLevel;
use Crypt;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use DB;
use App\Traits\FirebaseNotificationTrait;


class SyriatelCashController extends Controller
{
	use FirebaseNotificationTrait;

	public function getToken()
	{
		try {
			//$fullUrl = config('syriatel.getToken.full_url');



			/*	$response = Http::withoutVerifying()->acceptJson()->withOptions([
																												'stream' => true])->post($fullUrl, [
																															'username' => config('syriatel.getToken.username'),
																															'password' => config('syriatel.getToken.password')																								   ]);

																													echo $response->json();*/



			$fullUrl = "https://Merchants.syriatel.sy:1443/ePayment_external_Json/rs/ePaymentExternalModule/getToken";

			$client = new Client([
				'verify' => true, // Disable SSL verification
			]);

			$response = $client->post($fullUrl, [
				'json' => [

					'username' => config('syriatel.getToken.username'),
					'password' => config('syriatel.getToken.password')
				],
				'stream' => true
			]);



			return json_decode($response->getBody(), true);
			//	return $response;
		} catch (\Exception $e) {


			return $e->getMessage();
		}
	}


	public function paymentRequest(PaymentReqRequest $request)
	{
		try {
			// Assuming you have a method to get the token
		
			 $token = $this->getToken()['token'];
			$transactionID = Str::random(5);

			$client = new Client([
				'verify' => true, // Disable SSL verification
			]);
			$fullUrl = "https://Merchants.syriatel.sy:1443/ePayment_external_Json/rs/ePaymentExternalModule/paymentRequest";
			$response = $client->post($fullUrl, [
				'json' => [
					"customerMSISDN" => $request->validated('customerMSISDN'),
					"merchantMSISDN" => config('syriatel.getToken.merchant_number'),
					"amount" => $request->header('X-Amount') ? $request->header('X-Amount') : (string)$request->validated('amount'),
					"transactionID" => $transactionID,
					"token" => $token,
				],
				'stream' => true
			]);

			$data = json_decode($response->getBody(), true);

			if ($data['errorDesc'] == 'Success' && $data['errorCode'] == 0) {

				if ($request->validated('gift_id') && $request->validated('gift_code')) {

				/*	$gift_card = Coupon::where('id', $request->validated('gift_id'))
						->where('code', $request->validated('gift_code'))
						->firstOrFail();
*/
					Transaction::create([
						'user_id' => auth('sanctum')->user()->id,
						'gift_id' => $request->validated('gift_id'),
						'transaction_uuid' => $transactionID,
						'operation_type' => 'create-gift-card',
						'payment_method' => 'syriatel-cash',
						'amount' => $request->header('X-Amount') ? $request->header('X-Amount') : $request->validated('amount'),
						'status' => 'pending',

						// Assuming you have a transaction_id column in your Transaction model
						// 'token' => $token, // Assuming you want to store the token in the Transaction model
					]);
				} else if ($request->validated('order_id')) {
			
 $order = Order::findOrFail($request->validated()['order_id']);
				
				if( $order->gift_id != null && $order->covered_by_gift_card !== 0){
				 
				
				     Transaction::create([
                    'user_id' => auth('sanctum')->user()->id,
                    'order_id' => $request->order_id,
				    'gift_id' => $order->gift_id,
                    'transaction_uuid' => $transactionID,
                    'operation_type' => 'useage-gift-card',
                    'payment_method' => 'syriatel-cash',
                    'amount' => $request->header('X-Amount') ? $request->header('X-Amount') : /*$request->validated('amount')*/ $request->validated()['amount'],
                    'status' => 'pending', // Assuming you have a transaction_id column in your Transaction model
                    // 'token' => $token, // Assuming you want to store the token in the Transaction model
                ]);
				
				}
				
				else {
				
				      Transaction::create([
                    'user_id' => auth('sanctum')->user()->id,
                    'order_id' => $request->order_id,
                    'transaction_uuid' => $transactionID,
                    'operation_type' => 'create-order',
                    'payment_method' => 'syriatel-cash-cash',
                    'amount' => $request->header('X-Amount') ? $request->header('X-Amount') : /*$request->validated('amount')*/ $request->validated()['amount'],
                    'status' => 'pending', // Assuming you have a transaction_id column in your Transaction model
                    // 'token' => $token, // Assuming you want to store the token in the Transaction model
                ]);
				
				}
					$order = Order::findOrFail($request->validated('order_id'));
					
					if($order->status != 'processing'){

					return response()->error(['message' => 'Something went wrong'],400);
					
					}
					
					if($order->delivery_type == 'replacing'){
						$operation_type = 'exchange-order';	
					}
					
					else{
						$operation_type = 'create-order';	
					}
					
					Transaction::create([
						'user_id' => auth('sanctum')->user()->id,
						'order_id' => $order->id,
						'transaction_uuid' => $transactionID,
						'operation_type' => $operation_type,
						'payment_method' => 'syriatel-cash',
						'amount' => $request->header('X-Amount') ? $request->header('X-Amount') : $request->validated('amount'),
						'status' => 'pending',

						// Assuming you have a transaction_id column in your Transaction model
						// 'token' => $token, // Assuming you want to store the token in the Transaction model
					]);

				} 
				
				return response()->success(['api_token' => $token, 'transactionID' => $transactionID, 'response_data' => $data], 200);
			
			} else {
				// Handle the case where the payment request was not successful
				return response()->error(['error' => 'Payment request failed', 'data' => $data], 400);
			}
		} catch (\Exception $e) {

			if (!($e instanceof ModelNotFoundException)) {
				return response()->json(['error' => $e->getMessage()], 400);
			}


			throw $e;

		}
	}

	public function paymentConfirmation(PaymentConfirmationRequest $request)
	{
		//$transaction_id = $request->header('transactionid') ?? $request->header('TransactionID') ;
		try {
			$client = new Client([
				'verify' => true, // Disable SSL verification
			]);

			$fullUrl = "https://Merchants.syriatel.sy:1443/ePayment_external_Json/rs/ePaymentExternalModule/paymentConfirmation";

 // dd($request->header());
			$response = $client->post($fullUrl, [
				'json' => [
					"OTP" => $request->validated('OTP'),
					"customerMSISDN" => $request->validated('customer_number'),
					"merchantMSISDN" => config('syriatel.getToken.merchant_number'),
					"amount" => $request->header('X-Amount') ? $request->header('X-Amount') : $request->validated('amount'),
					"transactionID" => $request->header('Transaction-Id') ?? $request->header('Transactionid'),
					"token" => $request->header('TOKEN-API')
				],
				'stream' => true
			]);

			$data = json_decode($response->getBody(), true);
			if ($data['errorDesc'] == 'Success' && $data['errorCode'] == 0) {
				// Assuming you have a way to retrieve the transaction based on the order_id
				// and that the order_id is passed in the request or can be determined from the request
				DB::beginTransaction();
				if ($request->validated('gift_id') && $request->validated('gift_code')) {

					$gift_card = Coupon::where('id', $request->gift_id)
						->where('type', 'gift')
						->where('code', $request->gift_code)
						->firstOrFail();

					if (!$gift_card->valid && $gift_card->amount_off == 0) {

						$gift_card->valid = 1;
						$new_amount = Crypt::encryptString($request->validated('amount'));
						$gift_card->amount_off = $new_amount;
					} else  {
						$new_amount =  (int) $gift_card->amount_off + $request->validated('amount');
						$gift_card->amount_off = Crypt::encryptString($new_amount);
						$gift_card->status = 1;
					}
					$gift_card->last_recharge = now()->format('Y-m-d H:i:s');
					$gift_card->save();

					// Transaction::create([
					// 	'user_id' => auth('sanctum')->user()->id,
					// 	'gift_id' => $gift_card->id,
					// 	'transaction_uuid' => $request->header('TransactionID'),
					// 	'operation_type' => 'create-gift-card',
					// 	'payment_method' => 'syriatel-cash',
					// 	'amount' => $request->header('X-Amount') ? $request->header('X-Amount') : $request->validated('amount'),
					// 	'status' => 'pending', // Assuming you have a transaction_id column in your Transaction model
					// 	// 'token' => $token, // Assuming you want to store the token in the Transaction model
					// ]);

				} else if ($request->validated('order_id')) {
					Log::debug('order_id: '.$request->validated('order_id'));
					Log::debug('============================================');
					$order = Order::findOrFail($request->validated('order_id'));
					Log::debug('order: '.$order);
					Log::debug('============================================');
					
					if($order->status != 'processing'){
						return response()->error(['message' => 'Something went wrong'],400);
					}
					
					$order_id = $order->id;
					$inventory_id = Order::where('id', $order_id)->first()->inventory_id;
					
					$product_variatins_ids = OrderItem::select(['id', 'product_variation_id', 'quantity', 'to_inventory'])
						->where([['order_id', $order_id],['status','new']])->get();
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
					
					$original = Order::find($order->original_order_id);
					if($original){
						$original->update(['status'=>'replaced']);	
					}
					
					if (!is_null($order->coupon_id)) {
					

						$order->coupon->used_redemption += 1;
						$order->coupon->save();

					}
					
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
					/*
					$title = ["ar"=>"تم إنشاء طلب شراء جديد",
					"en"=>"A new order was created"];

					$body = ["ar"=>"تم إنشاء طلب شراء جديد",
					"en"=>"A new order was created"];

					$fcm_tokens = $user->fcm_tokens()->pluck('fcm_token')->toArray();

					foreach($fcm_tokens as $fcm){
					$this->send_notification($fcm, 
											 'تم إعادة المبلغ إلى حسابك بنجاح',
											 'Your money refunded to you successfully',
											 'تم إعادة المبلغ إلى حسابك بنجاح',
											 'Your money refunded to you successfully', 
											 'user_page', 
											 'flutter_app');
					}

					$user->notifications()->create([s
						'user_id'=>$user->id,
						'type'=> "order_page", // 1 is to redirect to the orders page
						'title'=>$title,
						'body'=>$body
					]);
					*/
					
					// Transaction::create([
					// 	'user_id' => auth('sanctum')->user()->id,
					// 	'order_id' => $order->id,
					// 	'transaction_uuid' => $request->header('TransactionID'),
					// 	'operation_type' => 'order',
					// 	'payment_method' => 'syriatel-cash',
					// 	'amount' => $request->header('X-Amount') ? $request->header('X-Amount') : $request->validated('amount'),
					// 	'status' => 'pending', // Assuming you have a transaction_id column in your Transaction model
					// 	// 'token' => $token, // Assuming you want to store the token in the Transaction model
					// ]);
				}
				
				$transaction = Transaction::where('transaction_uuid',$request->header('Transaction-Id'))->firstOrFail();
				
				if (!$transaction) {
					throw new Exception('Something Went Wrong', 404);
				}
				
				$transaction->update(['status' => 'completed']);
				DB::commit();
				
				if($request->validated('order_id') ){
					return response()->success([
					'message' => 'Transaction Completed Successfully, your order is being processed',
				], 200);
				
				}
				else{
					return response()->success([
						'message' => 'Transaction Completed Successfully, you can use your gift card freely',
					], 200);
				}
			}



			// Handle the case where the payment confirmation was not successful
			return response()->json(['error' => 'Payment confirmation failed', 'data' => $data], 400);
		} catch (\Exception $e) {
			DB::rollback();
			return response()->json(['error' => $e->getMessage()], 400);
		}
	}


	public function resendOTP(Request $request)
	{
		try {



			$fullUrl = "https://Merchants.syriatel.sy:1443/ePayment_external_Json/rs/ePaymentExternalModule/resendOTP";


			$client = new Client([
				'verify' => true, // Disable SSL verification
			]);

			$response = $client->post($fullUrl, [
				'json' => [
					"merchantMSISDN" => config('syriatel.getToken.merchant_number'),
					"transactionID" => $request->header('Transaction-Id'),
					"token" => $request->header('TOKEN-API')
				],
				'stream' => false
			]);

			return json_decode($response->getBody(), true);
		} catch (\Exception $e) {
			return response()->error($e->getMessage(), $e->getCode());
		}
	}
	
	
		public function test(Request $request)
	{
		try {
			DB::beginTransaction();
			
			$transactionID = Str::random(5);
			
			if ($request->gift_id && $request->gift_code) {

				Transaction::create([
					'user_id' => auth('sanctum')->user()->id,
					'gift_id' => $request->gift_id,
					'transaction_uuid' => $transactionID,
					'operation_type' => 'create-gift-card',
					'payment_method' => 'syriatel-cash',
					'amount' => $request->header('X-Amount') ? $request->header('X-Amount') : $request->amount,
					'status' => 'pending',

					// Assuming you have a transaction_id column in your Transaction model
					// 'token' => $token, // Assuming you want to store the token in the Transaction model
				]);
			} else if ($request->order_id) {

				$order = Order::findOrFail($request->order_id);

				if ($order->status != 'processing') {
					return response()->error(['message' => 'Something went wrong'], 400);
				}

				if ($order->gift_id != null && $order->covered_by_gift_card !== 0) {

					Transaction::create([
						'user_id' => auth('sanctum')->user()->id,
						'order_id' => $request->order_id,
						'gift_id' => $order->gift_id,
						'transaction_uuid' => $transactionID,
						'operation_type' => 'useage-gift-card',
						'payment_method' => 'syriatel-cash',
						'amount' => $request->header('X-Amount') ? $request->header('X-Amount') : /*$request->validated('amount')*/ $request->amount,
						'status' => 'pending', // Assuming you have a transaction_id column in your Transaction model
						// 'token' => $token, // Assuming you want to store the token in the Transaction model
					]);
				} else {

				if ($order->delivery_type == 'replacing') {
					$operation_type = 'exchange-order';
				} else {
					$operation_type = 'create-order';
				}

				Transaction::create([
					'user_id' => auth('sanctum')->user()->id,
					'order_id' => $order->id,
					'transaction_uuid' => $transactionID,
					'operation_type' => $operation_type,
					'payment_method' => 'syriatel-cash',
					'amount' => $request->header('X-Amount') ? $request->header('X-Amount') : $request->amount,
					'status' => 'pending',

					// Assuming you have a transaction_id column in your Transaction model
					// 'token' => $token, // Assuming you want to store the token in the Transaction model
				]);
			}

				//return response()->success(['api_token' => $token, 'transactionID' => $transactionID, 'response_data' => $data], 200);
			} else {
				// Handle the case where the payment request was not successful
				return response()->error(['error' => 'Payment request failed'], 400);
			}
			if ($request->gift_id && $request->gift_code) {

				$gift_card = Coupon::where('id', $request->gift_id)
					->where('type', 'gift')
					->where('code', $request->gift_code)
					->firstOrFail();

				if (!$gift_card->valid && $gift_card->amount_off == 0) {

					$gift_card->valid = 1;
					$new_amount = Crypt::encryptString($request->amount);
					$gift_card->amount_off = $new_amount;
				} else {
					$new_amount =  (int) $gift_card->amount_off + $request->amount;
					$gift_card->amount_off = Crypt::encryptString($new_amount);
					$gift_card->status = 1;
				}

				$gift_card->save();
			} else if ($request->order_id) {
				$order = Order::findOrFail($request->order_id);

				if ($order->status != 'processing') {
					return response()->error(['message' => 'Something went wrong'], 400);
				}

				$order_id = $order->id;
				$inventory_id = Order::where('id', $order_id)->first()->inventory_id;

				$product_variatins_ids = OrderItem::select(['id', 'product_variation_id', 'quantity', 'to_inventory'])
					->where([['order_id', $order_id], ['status', 'new']])->get();
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

				$original = Order::find($order->original_order_id);
				if ($original) {
					$original->update(['status' => 'replaced']);
				}

				if (!is_null($order->coupon_id)) {


					$order->coupon->used_redemption += 1;
					$order->coupon->save();
				}

				$employee = Employee::where('inventory_id', $order->inventory_id)->whereHas('account', function ($query) {
					$query->whereHas('roles', function ($query) {
						$query->where('name', 'warehouse_manager');
					});
				})->first();

				if ($employee) {
					$title = [
						"ar" => "تم إنشاء طلب شراء جديد",
						"en" => "A new order was created"
					];

					$body = [
						"ar" => "تم إنشاء طلب شراء جديد",
						"en" => "A new order was created"
					];

					$fcm_tokens = $employee->fcm_tokens()->pluck('fcm_token')->toArray();

					foreach ($fcm_tokens as $fcm) {
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
						'employee_id' => $employee->id,
						'type' => "dashboard_orders", // 1 is to redirect to the orders page
						'title' => $title,
						'body' => $body
					]);
				}
			}

			$transaction = Transaction::where('transaction_uuid', $transactionID)->firstOrFail();

			if (!$transaction) {
				throw new Exception('Something Went Wrong', 404);
			}
			$transaction->update(['status' => 'completed']);
			DB::commit();

			if ($request->order_id) {
				return response()->success([
					'message' => 'Transaction Completed Successfully, your order is being processed',
				], 200);
			} elseif($request->gift_id) {
				return response()->success([
					'message' => 'Transaction Completed Successfully, you can use your gift card freely',
				], 200);
			}
			// Handle the case where the payment confirmation was not successful
			return response()->json(['error' => 'Payment confirmation failed'], 400);
		} catch (\Exception $e) {
			DB::rollback();
			return response()->json(['error' => $e->getMessage()], 400);
		}
	}

	
}
