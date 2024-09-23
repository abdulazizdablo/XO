<?php

namespace App\Services;

use App\Http\Requests\SyriatelPayment\PaymentConfirmationRequest;
use App\Http\Requests\SyriatelPayment\PaymentReqRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use GuzzleHttp\Client;
use Illuminate\Http\Request;


class SyriateCashService
{
	public function getToken()
	{
		try {
			//$fullUrl = config('syriatel.getToken.full_url');



			/*	$response = Http::withoutVerifying()->acceptJson()->withOptions([
    'stream' => true])->post($fullUrl, [
				'username' => config('syriatel.getToken.username'),
				'password' => config('syriatel.getToken.password'),
			]);

		echo $response->json();*/



			$fullUrl = "https://Merchants.syriatel.sy:1443/ePayment_external_Json/rs/ePaymentExternalModule/getToken";

			$client = new Client([
				'verify' => false, // Disable SSL verification
			]);

			$response = $client->post($fullUrl, [
				'json' => [

					'username' => config('syriatel.getToken.username'),
					'password' => config('syriatel.getToken.password')
				],
				'stream' => true
			]);



			return	json_decode($response->getBody(), true);
			//	return $response;
		} catch (\Exception $e) {


			return $e->getMessage();
		}
	}

	private function generateAndStoreTransactionID(int $order_id)
	{
		$transactionID = Str::random(5);
		//$transactionID = Str::random(8); // Generate a unique transaction ID



	//	$token = $this->getToken()['token'];

		// Use unique cache keys for token and transaction ID
		$token = Cache::remember('token_' . $order_id, 60 * 13, function () {
			return $this->getToken()['token'];
		});


		/*$data = [

			'token' => $token,
			'transactionID' => $transactionID

		];
*/


		Cache::put('transactionID_' . $order_id, $transactionID);




		return [

			'token' => $token,
			'transactionID' => $transactionID

		];

		// Return the generated transaction ID
		//return $data;
	}

	public function paymentRequest( array $payment_request_data)
	{


		try {


			//$transactionID = $this->generateAndStoreTransactionID($request->order_id)['transactionID'];
			//$token = $this->generateAndStoreTransactionID($request->order_id)['token'];
$token = $this->getToken()['token'];
$transactionID =  Str::random(5);
			//$token = Cache::get('token_' . $request->order_id);

			/*$fullUrl = config('syriatel.paymentRequest.full_url');

			$response = Http::post($fullUrl, [
				"customerMSISDN" => $request->customer_number,
				"merchantMSISDN" => config('syriatel.getToken.merchant_number'),
				"amount" => $request->validated('amount'),
				"transactionID" => Cache::get('transactionID'),
				"token" => $token,
			]);*/

			$client = new Client([
				'verify' => false, // Disable SSL verification
			]);
			$fullUrl = "https://Merchants.syriatel.sy:1443/ePayment_external_Json/rs/ePaymentExternalModule/paymentRequest";
			$response = $client->post($fullUrl, [
				'json' => [

					"customerMSISDN" => $payment_request_data['customerMSISDN'],
					"merchantMSISDN" => config('syriatel.getToken.merchant_number'),
					"amount" => $payment_request_data['amount'],
					"transactionID" =>	$transactionID,
					"token" => $token,
				],
				'stream' => true
			]);

			
			
			Transaction::create([
				'order_id' => $payment_request_data['order_id'],
				//'transaction_id' => $transactionID,
				'amount' =>  $payment_request_data['amount']
			    'payment_method' => $request->payment_method,
				'status' => 'pending',
				
			
			
			
			]);

			$data =  json_decode( $response->getBody(), true);
			return response(['api_token' => $token,'transactionID' => $transactionID,$data],200);
			
		} catch (\Exception $e) {
			return response()->error($e->getMessage(), $e->getCode());
		}
	}

	public function paymentConfirmation( array $payment_confirmation_data)
	{
		try {
			/*	$fullUrl = config('syriatel.paymentConfirmation.full_url');

			$response = Http::post($fullUrl, [
				"customerMSISDN" => $request->validated(''),
				"merchantMSISDN" => config('syriatel.getToken.merchant_number'),
				"amount" => $request->validated('amount'),
				"transactionID" => "xxxxx",
				"token" => Cache::get('token' . cache('transactionID')),
			]);
			
			*/

			$client = new Client([
				'verify' => false, // Disable SSL verification
			]);

			$fullUrl = "https://Merchants.syriatel.sy:1443/ePayment_external_Json/rs/ePaymentExternalModule/paymentConfirmation";


			$response = $client->post($fullUrl, [
				'json' => [
					"OTP" => $payment_confirmation_data('OTP'),
					"customerMSISDN" => $payment_confirmation_data('customer_number'),
					"merchantMSISDN" => config('syriatel.getToken.merchant_number'),
					"amount" => $payment_confirmation_data['amount'],
					"transactionID" => //Cache::get('transactionID_' . $request->order_id),
					$request->header('TransactionID'),
					"token" => //Cache::get('token_' . $request->order_id),
					$request->header('TOKEN_API')
				],
				'stream' => true
			]);

		 json_decode($response->getBody(), true);
			
			if($response->getBody()['errorMsg'] == 'Success' && $response->getBody()['errorCode'] === 0  ){
			
				$transaction = Transaction::where('order_id',$payment_request_data['order_id']);
				$transaction->update([
				'status' => 'completed'
				
				]);
				
			/*Transaction::create([
				'order_id' => $payment_request_data['order_id'],
				'transaction_id' => $transactionID,
				'amount' =>  $payment_request_data['amount']
			    'payment_method' => $request->payment_method,
				'status' => 'completed',
				
			
			
			
			]);
			*/
				
				return json_decode($response->getBody(), true);
				
				
			}
			
		} catch (\Exception $e) {
			return response()->error($e->getMessage(), $e->getCode());
		}
	}

	public function resendOTP(Request $request)
	{
		try {
			/*$fullUrl = config('syriatel.resendOTP.full_url');

			$response = Http::post($fullUrl, [
				"merchantMSISDN" => config('syriatel.getToken.merchant_number'),
				"transactionID" => Cache::get('transacionID'),
				"token" => Cache::get('token' . cache('transactionID')),
			]);
			*/
			$fullUrl = "https://Merchants.syriatel.sy:1443/ePayment_external_Json/rs/ePaymentExternalModule/resendOTP";


			$client = new Client([
				'verify' => false, // Disable SSL verification
			]);

			$response = $client->post($fullUrl, [
				'json' => [
					"merchantMSISDN" => config('syriatel.getToken.merchant_number'),
					"transactionID" => Cache::get('transactionID_' . $request->order_id),
					"token" => Cache::get('token_' . $request->order_id),
				],
				'stream' => true
			]);

			return json_decode($response->getBody(), true);
		} catch (\Exception $e) {
			return response()->error($e->getMessage(), $e->getCode());
		}
	}
}
