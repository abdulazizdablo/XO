<?php

namespace App\Http\Controllers\Dashboard;

use Illuminate\Http\Request;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\Refund\RefundTransactionRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;


use App\Http\Controllers\Controller;



class RefundController extends Controller
{
    
	
	public function index(Request $request){
	
		
		
	$refund_transactions = Transaction::where('operation_type','refund')->paginate($request->pageSize);
		
		
		return response()->success($refund_transactions,200);
	
	}
	
	
	
	
	public function refund(RefundTransactionRequest $request){
			DB::beginTransaction();
		try {
	

		$refunded_amount = $request->refund_amount;
	
	$refund_transactions = Transaction::where('id',$request->transaction_id)->where('operation_type','refund')->where('status','pending')->firstOrFail();
//dd($refund_transactions);
			
	//		dd($request->refund_amount < $refund_transactions->amount );
			
		//	dd($request->refund_amount , $refund_transactions->amount);

				if($request->refund_amount > $refund_transactions->amount){
		return response()->error(['message' => 'Please enter equal or less amount'],400);
		}
		
		if($refund_transactions->status !== 'pending'){
		return response()->error(['message' => 'Something went wrong'],400);
		}
		
		
		
		
		
$total_refund = ($refund_transactions->amount - $request->refund_amount); 
			
		//	dd($refund_transactions->amount - $request->refund_amount);
		
		//dd($total_refund);
	// $refund_transactions->update(['status' => 'completed','amount' => ($refund_transactions->amount) - (-$request->refund)]);
		
			 $refund_transactions->update(['status' => 'received','amount' =>  $total_refund, ]);
			
		/*	$refund_transactions->status = 'received';
				$refund_transactions->amount = 1000.0;
			
			$refund_transactions->save();
			*/
		//	dd($refund_transactions);
		DB::commit();
		return response()->success(['refund' =>$refund_transactions ,'message' => 'Refund has been paid successfully'],200);
		
	
	}
		
		catch (ModelNotFoundException $e) {
			
			
throw $e;
            DB::rollBack();
            return response()->error($e->getMessage(), 400);
        }
	catch (\Exception $e) {

            DB::rollBack();
            return response()->error($e->getMessage(), 400);
        }
	}
	
	
}
