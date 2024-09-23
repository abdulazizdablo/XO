<?php

namespace App\Http\Controllers\Dashboard;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Http\Controllers\Controller;



class RefundController extends Controller
{
    
	
	public function index(Request $request){
	
		
		
	$refund_transactions = Transaction::where('operation_type','refund_order')->paginate($request->pageSize);
		
		
		return response()->success($refund_transactions,200);
	
	}
	
	
	
	
	public function refund(Request $request){
		
		
		$refunded_amount = $request->refund_amount;
	
	$refund_transactions = Transaction::where('id',$request->transaction_id)->where('operation_type','refund_order')->where('status','pending')->firstOrFail();
		
		
		if($refund_transactions->status !== 'pending'){
		return response()->error(['message' => 'Something went wrong']);
		
		
		}
		
		
		

		
		
	// $refund_transactions->update(['status' => 'completed','amount' => ($refund_transactions->amount) - (-$request->refund)]);
		
			 $refund_transactions->update(['status' => 'completed','amount' => 0]);
		
		return response()->success(['refund' =>$refund_transactions ,'message' => 'Refund has been paid successfully'],200);
		
	
	}
	
	
	
	
}
