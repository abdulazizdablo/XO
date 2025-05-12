<?php

namespace App\Http\Controllers\Users\v1;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\FcmToken;
use App\Models\Transaction;
use App\Models\Employee;
use App\Models\Order;
use App\Models\StockLevel;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use App\Traits\FirebaseNotificationTrait;

class PaymentController extends Controller
{
	use FirebaseNotificationTrait;

    private $merchant_id;
    private $merchant_secret;

    public function __construct()
    {
        $this->merchant_id = config('app.ecash_merchant_id');
        $this->merchant_secret = config('app.ecash_merchant_secret');
    }  

    public function handleCallback(Request $request)
    {
		$validatedData = $request->validate([
			'IsSuccess' => 'required|boolean',
			//'Message' => 'required|string',
			'OrderRef' => 'required',
			'TransactionNo' => 'required',
			'Amount' => 'required|numeric',
			'Token' => 'required|string',
		]);
		$isValid = strtoupper(hash('md5', $this->generateHashString($validatedData)))=== $validatedData['Token'];
		if (!$isValid) {
			
			// The callback is not valid, log the error and abort
			Log::error('Invalid callback received from e-Cash Transaction Processor.');
			abort(403, 'Invalid callback.');
		}else{
			$order = Order::findOrFail($validatedData['OrderRef']);

			if($order->status != 'processing'){
				return response()->error(['message' => 'Something went wrong'],400);
			}

			$order->update([
				'paid'=>1
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
			
			$product_variatins_ids = OrderItem::select(['id','product_variation_id','quantity','to_inventory'])
				->where('order_id',$order->id)->get();
			
			foreach ($product_variatins_ids as $pv){
				$stock = StockLevel::where([['inventory_id',$pv->to_inventory],['product_variation_id',$pv->product_variation_id]])->first();
				$stock->update(['on_hold'=> $stock->on_hold - $pv->quantity,
				'sold_quantity'=> $stock->sold_quantity + $pv->quantity]);
			}
				$value = $validatedData['Amount'];
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
				if($fcm_token?->lang == 'en'){
					$this->send_notification($fcm, 
											 'A new order was created',
											 'A new order was created', 
											 'dashboard_orders,'.$order->id, 
											 'dashboard'); // key source	
				}else{
					$this->send_notification($fcm, 
											 'تم إنشاء طلب شراء جديد',
											 'تم إنشاء طلب شراء جديد',
											 'dashboard_orders,'.$order->id, 
											 'dashboard'); // key source
				}	
			}

			$employee->notifications()->create([
				'employee_id'=>$employee->id,
				'type'=> "dashboard_orders", // 1 is to redirect to the orders page
				'title'=>$title,
				'body'=>$body
			]);	
		}

		
		Transaction::create([
                    'user_id' => $order->user_id,
                    'gift_id' => null,
                    'order_id' => $order->id,
                    'transaction_uuid' => Str::random(5),
                    'operation_type' => 'create-order',
                    'payment_method' => 'ecash',
					'transaction_source' => $validatedData['TransactionNo'],
                    'amount' => $validatedData['Amount'],
                    'status' => 'completed',
                ]);
		
		// Respond with a success status
		return response()->json(['status' => 'success']);
	}
	
		public function handleGiftCallback(Request $request)
    {
		//Log::debug('order: ');
		$validatedData = $request->validate([
			'IsSuccess' => 'required|boolean',
			//'Message' => 'required|string',
			'OrderRef' => 'required',
			'TransactionNo' => 'required',
			'Amount' => 'required|numeric',
			'Token' => 'required|string',
		]);
		//$isValid = hash('md5', $this->generateHashString($validatedData)) === $validatedData['Token'];
		$isValid = strtoupper(hash('md5', $this->generateHashString($validatedData)))=== $validatedData['Token'];
		$transaction = Transaction::where('transaction_uuid',$validatedData['OrderRef'])->first();
		//Log::debug('transaction: '.$transaction);
		//Log::debug('============================= ');

		if (!$isValid) {
			
			// The callback is not valid, log the error and abort
			//Log::error('Invalid callback received from e-Cash Transaction Processor.');
			$transaction->update(['status'=>'failed',
								  'payment_method'=>'ecash']);
			abort(403, 'Invalid callback.');
		}else{
			if($transaction->operation_type == 'recharge-gift-card'){
				$value = $validatedData['Amount'];
				//Log::debug('value: '.$value);
				//Log::debug('============================= ');
				$transaction->update(['amount'=>$value,
									  'transaction_source' => $validatedData['TransactionNo'],
									  'payment_method'=>'ecash']);
				//Log::debug('transaction: '.$transaction);
				//Log::debug('============================= ');
				$coupon = Coupon::where('id',$transaction->gift_id)->first();
				//Log::debug('coupon: '.$coupon);
				//Log::debug('============================= ');
				$amount_off = $coupon->amount_off;
				//Log::debug('amount_off: '.$amount_off);
				//Log::debug('============================= ');
				$new_amount = $value + $amount_off;
				//Log::debug('new_amount: '.$new_amount);
				//Log::debug('============================= ');
				$coupon->amount_off = Crypt::encryptString($new_amount);
				$coupon->last_recharge = now()->format('Y-m-d H:i:s');
				
				$coupon->save();
			}elseif($transaction->operation_type == 'create-gift-card'){
				$value = $validatedData['Amount'];
				//Log::debug('value: '.$value);
				$transaction->update(['amount'=>$value,
									  'payment_method'=>'ecash',
									  'transaction_source' => $validatedData['TransactionNo'],
									  'status'=>'paid']);
				//Log::debug(' transaction: '.$transaction);
				$coupon = Coupon::where('id',$transaction->gift_id)->first();
				//Log::debug(' coupon: '.$coupon);
				$amount_off = $coupon->amount_off;
				//Log::debug(' amount_off: '.$amount_off);
				$new_amount = $value + $amount_off;
				//Log::debug(' new_amount: '.$new_amount);
				$coupon->update([
					'amount_off' => Crypt::encryptString($new_amount),
					'valid' => 1,
					'last_recharge' => now()->format('Y-m-d H:i:s')
				]);
				//Log::debug(' coupon: '.$coupon);
			}
			else{
				
			}
			
		}
		 // The callback is valid, proceed with processing the transaction
		// Update the order status in your database based on the transaction status
		// ...

		// Respond with a success status
		return response()->json(['status' => 'success']);
	}
	
	    public function handleReplaceCallback(Request $request)
    {
		$validatedData = $request->validate([
			'IsSuccess' => 'required|boolean',
			//'Message' => 'required|string',
			'OrderRef' => 'required',
			'TransactionNo' => 'required',
			'Amount' => 'required|numeric',
			'Token' => 'required|string',
		]);
		$isValid = strtoupper(hash('md5', $this->generateHashString($validatedData)))=== $validatedData['Token'];
		if (!$isValid) {
			
			// The callback is not valid, log the error and abort
			Log::error('Invalid callback received from e-Cash Transaction Processor.');
			abort(403, 'Invalid callback.');
		}else{
				$order = Order::findOrFail($validatedData['OrderRef']);
				$order->update([
					'paid'=>1
				]);
				
			$original = Order::find($order->original_order_id);
					if($original){
						$original->update(['status'=>'replaced']);	
					}
			
			$product_variatins_ids = OrderItem::select(['id','product_variation_id','quantity','to_inventory'])
					->where('order_id',$order->id)->get();
			foreach ($product_variatins_ids as $pv){
				$stock = StockLevel::where([['inventory_id',$pv->to_inventory],['product_variation_id',$pv->product_variation_id]])->first();
				$stock->update(['on_hold'=> $stock->on_hold - $pv->quantity,
				'sold_quantity'=> $stock->sold_quantity + $pv->quantity]);
			}
				$value = $validatedData['Amount'];
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
											 'dashboard_orders,'.$order->id, 
											 'dashboard'); // key source	
				}else{
					$this->send_notification($fcm, 
											 'تم إنشاء طلب شراء جديد',
											 'تم إنشاء طلب شراء جديد',
											 'dashboard_orders,'.$order->id, 
											 'dashboard'); // key source
				}	
			}

			$employee->notifications()->create([
				'employee_id'=>$employee->id,
				'type'=> "dashboard_orders", // 1 is to redirect to the orders page
				'title'=>$title,
				'body'=>$body
			]);	
		}

		
		Transaction::create([
                    'user_id' => $order->user_id,
                    'gift_id' => null,
                    'order_id' => $order->id,
                    'transaction_uuid' => Str::random(5),
                    'operation_type' => 'replace-order',
                    'payment_method' => 'ecash',
					'transaction_source' => $validatedData['TransactionNo'],
                    'amount' => $validatedData['Amount'],
                    'status' => 'completed',
                ]);
		
		// Respond with a success status
		return response()->json(['status' => 'success']);
	}
	
	private function generateHashString($data)
	{
		return sprintf(
			'%s%s%s%s%s',
			$this->merchant_id,
			$this->merchant_secret, // Assuming you have stored your Merchant Secret in your .env file
			$data['TransactionNo'],
			$data['Amount'],
			$data['OrderRef']
		);
	}
}