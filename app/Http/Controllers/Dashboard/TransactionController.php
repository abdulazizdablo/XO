<?php

namespace App\Http\Controllers\Dashboard;

use App\Enums\Roles;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Traits\DateScope;
use App\Models\Employee;
use App\Models\Order;
use App\Utils\PaginateCollection;

class TransactionController extends Controller
{
	use DateScope;
	
	
	  public function __construct(protected PaginateCollection $paginatecollection)
    {
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
		
		/*	$employee = auth('api-employees')->user();
			
			if(!$employee){
				return response()->error('Unauthorized', 403);	
			}
			
			if (!$employee->hasRole(Roles::MAIN_ADMIN)) {
				return response()->error('Unauthorized', 403);
			}
			*/
			$type = request('type');
				$pageSize = $request->pageSize;
			$filter_data = $request->only([
                'date_min',
                'date_max',
                'search',
                'status',
			    'price_min',
			    'price_max',
                'user_id',
				'operation_type',
				'payment_method',
				'amount',
				'sub-operation_type',
				'order_gift',
			    'sort_key',
		
            ]);
			
			if($type == 'refunds'){
				$transactions = Transaction::where('transaction_uuid','refund');
			}else{
				//$transactions = Transaction::;
			}
			
			
			
			$transactions = Transaction::with(['order','exchange.order','refund.order', 'gift_card','user']); // Use with() for eager loading

			
			
				if(isset($filter_data['status'])){
				$transactions = $transactions->where('status', $filter_data['status']);	
			}
			
		     	if(isset($filter_data['order_gift'])){
					
					
				$transactions = $transactions->whereNotNull($filter_data['order_gift']);	
			}
			
					if(isset($filter_data['operation_type'])){
				$transactions = $transactions->where('operation_type', $filter_data['operation_type']);	
			}
				
			
	
			
			if(isset($filter_data['date_min']) && isset($filter_data['date_max'])){
				$date_min = $filter_data['date_min'] ?? 0;
        		$date_max = $filter_data['date_max'] ?? date('Y-m-d');

         		$transactions = $transactions->whereBetween('created_at', [$date_min, $date_max]);	
			}
			
			if(isset($filter_data['user_id'])){
				$transactions = $transactions->where('user_id', $filter_data['user_id']);	
			}
			
			
			
				if(isset($filter_data['payment_method'])){
				$transactions = $transactions->where('payment_method', $filter_data['payment_method']);	
			}
			
			
			
			if(isset($filter_data['price_min']) && isset($filter_data['price_max'])){
				$price_min = $filter_data['price_min'] ?? 0;
        		$price_max = $filter_data['price_max'] ?? 1000000;

         		$transactions = $transactions->whereBetween('amount', [$price_min, $price_max]);	
			}
			
					if(isset($filter_data['sort_key'])){
	

         		$transactions_orders = $transactions_orders->orderBy('amount',$filter_data['sort_key'] );	
			}
			
			
			
						$transactions = $transactions->paginate($pageSize);
			
			$transactions_procced = $transactions->map(function($item){
			
			
		    $item->order_invoice_number = $item->order?->invoice_number;
			$item->exchange_invoice_number = $item->exchange?->order?->invoice_number;
			$item->refund_invoice_number = $item->refund?->order?->invoice_number;
			
			
			/*	if( $item->exchange && $item->gift_card ){
					
					$item->ammount_details = ['exchange' => $item->exchange->total_exchange, 'gift_card' => $item->exchange->order->]
				
				
				
				}*/
			
		   	$item->user_full_name = $item->user?->full_name;
				
		
				
				if ($item->covered_by_gift !== 0) {
				
				$item->amount_details = collect(['paid_from_gift' => $item->covered_by_gift_card , 'paid_amount' => $item->amount]);
				
				}
				
				
		  //  $item->gift_card = $item->gift_card?->code;
				
				
			
			
	
	 
			});

			$order_gift = [
				'gift_card' => ['create_gift_card' => 'create-gift-card'],
								
									'recharge_gift_card' => 'recharge-gift-card',
								
									'usage_gift_card' => ['gift_id','order_id'],
						   
						   
						  'order' => ['order' , 'refund','exchange' ]];
	
		$order_variations =	['normal_order' => 'order_id',
			'refund' => 'refund_id',
			'exchange' => 'exchange_id'];
			
			$gift_card_variations = ['create_gift_card' => 'gift_id',
									'recharge_gift_card' => 'recharge_gift_card',
									'usage_gift_card' => 'usage_gift_card' ];
			
			
		
		
			
		
			
			
			
            return response()->success([
                $transactions
            ], Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->error(
                $th->getMessage(),
                Response::HTTP_OK
            );
        }
    }
	
	
	
	public function transactionGiftCards(Request $request){
	$pageSize = $request->pageSize;
		
			$filter_data = $request->only([
                'date_min',
                'date_max',
                'search',
                'status',
			    'price_min',
			    'price_max',
                'user_id',
				'operation_type',
				'payment_method',
				'amount',
				'sub-operation_type',
				'order_gift',
			    'sort_key',
		
            ]);
			
		
	
	$transactions_gift_cards = Transaction::whereNotNull('gift_id')->whereIn('operation_type',['recharge','create','usage']);
		
		
			
			if(isset($filter_data['status'])){
				$transactions_gift_cards = $transactions_gift_cards->where('status', $filter_data['status']);	
			}
			
		     	if(isset($filter_data['order_gift'])){
					
					
				$transactions_gift_cards = $transactions_gift_cards->whereNotNull($filter_data['order_gift']);	
			}
			
					if(isset($filter_data['operation_type'])){
				$transactions_gift_cards = $transactions_gift_cards->where('operation_type', $filter_data['operation_type']);	
			}
				
			
		if(isset($filter_data['payment_method'])){
				$transactions_gift_cards = $transactions_gift_cards->where('payment_method', $filter_data['payment_method']);	
			}
			
		
			if (isset($filter_data['inventory_id'])) {
    $transactions_gift_cards = $transactions_gift_cards->whereHas('order.inventory', function($query) use ($filter_data) {
        $query->where('inventories.id', $filter_data['inventory_id']);
    });
}

			
			
			if(isset($filter_data['date_min']) && isset($filter_data['date_max'])){
				$date_min = $filter_data['date_min'] ?? 0;
        		$date_max = $filter_data['date_max'] ?? date('Y-m-d');

         		$transactions_gift_cards = $transactions_gift_cards->whereBetween('created_at', [$date_min, $date_max]);	
			}
			
			if(isset($filter_data['user_id'])){
				$transactions_gift_cards = $transactions_gift_cards->where('user_id', $filter_data['user_id']);	
			}
			if(isset($filter_data['price_min']) && isset($filter_data['price_max'])){
				$price_min = $filter_data['price_min'] ?? 0;
        		$price_max = $filter_data['price_max'] ?? 1000000;

         		$transactions_gift_cards = $transactions_gift_cards->whereBetween('amount', [$price_min, $price_max]);	
			}
		
	$transactions_gift_cards = $transactions_gift_cards->with('order.shipment')->paginate($pageSize);
		
		
			return response()->success($transactions_gift_cards,200);

		
		
	}
	
	
		public function transacionRefunds(Request $request){
			
	     $pageSize = $request->pageSize;
			
		$filter_data = $request->only([
                'date_min',
                'date_max',
			    'inventory_id',
                'search',
                'status',
			    'price_min',
			    'price_max',
                'user_id',
				'operation_type',
				'payment_method',
				'amount',
				'sub-operation_type',
				'order_gift',
		     	'sort_key',
		
            ]);
	
	   $transactions_orders= Transaction::where('transaction_uuid','refund')->where('status','pending');
			
			
			if (isset($filter_data['search'])) {
    $transactions_orders->whereHas('order', function ($query) use ($filter_data) {
        $query->where('invoice_number', 'LIKE', '%' . $filter_data['search'] . '%');
    });
}



			
			
			if(isset($filter_data['status'])){
				$transactions_orders = $transactions_orders->where('status', $filter_data['status']);	
			}
			
		     	if(isset($filter_data['order_gift'])){
					
					
				$transactions_orders = $transactions_orders->whereNotNull($filter_data['order_gift']);	
			}
			
					if(isset($filter_data['operation_type'])){
				$transactions_orders = $transactions_orders->where('operation_type', $filter_data['operation_type']);	
			}
			
			
	if (isset($filter_data['inventory_id'])) {
    $transactions_orders = $transactions_orders->whereHas('order.inventory', function($query) use ($filter_data) {
        $query->where('inventories.id', $filter_data['inventory_id']);
    });
}

														   
														   
			
				if(isset($filter_data['payment_method'])){
				$transactions_orders = $transactions_orders->where('payment_method', $filter_data['payment_method']);	
			}
			
			
			
			if(isset($filter_data['date_min']) && isset($filter_data['date_max'])){
				$date_min = $filter_data['date_min'] ?? 0;
        		$date_max = $filter_data['date_max'] ?? date('Y-m-d');

         		$transactions_orders = $transactions_orders->whereBetween('created_at', [$date_min, $date_max]);	
			}
			
			if(isset($filter_data['user_id'])){
				$transactions_orders = $transactions_orders->where('user_id', $filter_data['user_id']);	
			}
			
				if(isset($filter_data['price_min']) && isset($filter_data['price_max'])){
				$price_min = $filter_data['price_min'] ?? 0;
        		$price_max = $filter_data['price_max'] ?? 1000000;

         		$transactions_orders = $transactions_orders->whereBetween('amount', [$price_min, $price_max]);	
			}
			
		
			
					if(isset($filter_data['sort_key'])){
	

         		$transactions_orders = $transactions_orders->orderBy('amount',$filter_data['sort_key'] );	
			}
			
			

			
						$transactions_orders = $transactions_orders->with('order.shipment')->paginate($pageSize);
			
			$transactions_procced = $transactions_orders->map(function($item){
			
			
		    $item->order_invoice_number = $item->order?->invoice_number;
			$item->exchange_invoice_number = $item->exchange?->order?->invoice_number;
			$item->refund_invoice_number = $item->refund?->order?->invoice_number;
			
			/*	if( $item->exchange && $item->gift_card ){
					
					$item->ammount_details = ['exchange' => $item->exchange->total_exchange, 'gift_card' => $item->exchange->order->]
				
				
				
				}*/
			
		   	$item->user_full_name = $item->user?->full_name;
				
		
				
				if ($item->covered_by_gift !== 0) {
				
				$item->amount_details = collect(['paid_from_gift' => $item->covered_by_gift_card , 'paid_amount' => $item->amount]);
				
				}
				
				
		  //  $item->gift_card = $item->gift_card?->code;
				
				
			
			
	
	 
			});

			return response()->success($transactions_orders,200);
		
	}
										
	
	
	
	
		public function transactionOrders(Request $request){
			
	     $pageSize = $request->pageSize;
			
		$filter_data = $request->only([
                'date_min',
                'date_max',
			    'inventory_id',
                'search',
                'status',
			    'price_min',
			    'price_max',
                'user_id',
				'operation_type',
				'payment_method',
				'amount',
				'sub-operation_type',
				'order_gift',
		     	'sort_key',
		
            ]);
	
	   $transactions_orders= Transaction::whereIn('operation_type',['create','refund','exchange']);
			
			
			if (isset($filter_data['search'])) {
    $transactions_orders->whereHas('order', function ($query) use ($filter_data) {
        $query->where('invoice_number', 'LIKE', '%' . $filter_data['search'] . '%');
    });
}



			
			
			if(isset($filter_data['status'])){
				$transactions_orders = $transactions_orders->where('status', $filter_data['status']);	
			}
			
		     	if(isset($filter_data['order_gift'])){
					
					
				$transactions_orders = $transactions_orders->whereNotNull($filter_data['order_gift']);	
			}
			
					if(isset($filter_data['operation_type'])){
				$transactions_orders = $transactions_orders->where('operation_type', $filter_data['operation_type']);	
			}
			
			
	if (isset($filter_data['inventory_id'])) {
    $transactions_orders = $transactions_orders->whereHas('order.inventory', function($query) use ($filter_data) {
        $query->where('inventories.id', $filter_data['inventory_id']);
    });
}

														   
														   
			
				if(isset($filter_data['payment_method'])){
				$transactions_orders = $transactions_orders->where('payment_method', $filter_data['payment_method']);	
			}
			
			
			
			if(isset($filter_data['date_min']) && isset($filter_data['date_max'])){
				$date_min = $filter_data['date_min'] ?? 0;
        		$date_max = $filter_data['date_max'] ?? date('Y-m-d');

         		$transactions_orders = $transactions_orders->whereBetween('created_at', [$date_min, $date_max]);	
			}
			
			if(isset($filter_data['user_id'])){
				$transactions_orders = $transactions_orders->where('user_id', $filter_data['user_id']);	
			}
			
				if(isset($filter_data['price_min']) && isset($filter_data['price_max'])){
				$price_min = $filter_data['price_min'] ?? 0;
        		$price_max = $filter_data['price_max'] ?? 1000000;

         		$transactions_orders = $transactions_orders->whereBetween('amount', [$price_min, $price_max]);	
			}
			
		
			
					if(isset($filter_data['sort_key'])){
	

         		$transactions_orders = $transactions_orders->orderBy('amount',$filter_data['sort_key'] );	
			}
			
			

			
						$transactions_orders = $transactions_orders->with('order.shipment')->paginate($pageSize);
			
			$transactions_procced = $transactions_orders->map(function($item){
			
			
		    $item->order_invoice_number = $item->order?->invoice_number;
			$item->exchange_invoice_number = $item->exchange?->order?->invoice_number;
			$item->refund_invoice_number = $item->refund?->order?->invoice_number;
			
			/*	if( $item->exchange && $item->gift_card ){
					
					$item->ammount_details = ['exchange' => $item->exchange->total_exchange, 'gift_card' => $item->exchange->order->]
				
				
				
				}*/
			
		   	$item->user_full_name = $item->user?->full_name;
				
		
				
				if ($item->covered_by_gift !== 0) {
				
				$item->amount_details = collect(['paid_from_gift' => $item->covered_by_gift_card , 'paid_amount' => $item->amount]);
				
				}
				
				
		  //  $item->gift_card = $item->gift_card?->code;
				
				
			
			
	
	 
			});

			return response()->success($transactions_orders,200);
		
	}
														   
	
	
	

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }
	
	
		public function transactionRefundsCards(Request $request){
			
	    $dateScope = request('date_scope');
	 $inventory_id = request('inventory_id');
              $expected = 0;
        $from_date = null;
        $to_date = null ;
        if ($dateScope == null) {
            $dateScope == 'Today';
        }
		
		$transactions = null;
			
                $modelName =null;
       $all_transactions = null;
		
	//	dd( $all_transactions);
            $completed_transactions = null;
            $pending_transactions = null;
        $failed_transactions = null;
		
		
		
		
		if ($inventory_id !== null){
			
			 $transactions= Transaction::whereHas('inventory',function($query) use($inventory_id){
			 $query->where('inventories.id',$inventory_id);
			 
			 });
			
			
			
			
		//	dd($transactions->get());
			
			
				
$all_transactions = (clone $transactions)->where('transaction_uuid', 'refund')->count();
        $completed_transactions = (clone $transactions)
            ->where('status', 'received')
            ->where('transaction_uuid', 'refund')
            ->count();
        $pending_transactions = (clone $transactions)
            ->where('status', 'pending')
            ->where('transaction_uuid', 'refund')
            ->count();
        $failed_transactions = (clone $transactions)
            ->where('status', 'failed')
            ->where('transaction_uuid', 'refund')
            ->count();
       // $failed_transactions =$transactions->where('status','failed')->where('transaction_uuid','refund')->count();
		//dd(   $failed_transactions);
return response()->success( [
         'all_transactions' => $all_transactions,
         'completed_transactions' => $completed_transactions,
         'pending_transactions' => $pending_transactions,
         'failed_transactions' => $failed_transactions,
        ],200);
		

}
		
		
		
	

		
		
		else {
		
			//$transactions = Transaction::query(); 
			
		//	dd(	$transactions->where('transaction_uuid','refund')->count());
                $all_transactions =Transaction::where('transaction_uuid','refund')->count();
			//	dd(	  $all_transactions);
		
	//	dd( $all_transactions);
            $completed_transactions = Transaction::where('status','received')->where('transaction_uuid','refund')->count();
            $pending_transactions = Transaction::where('status','pending')->where('transaction_uuid','refund')->count();
			
	//	dd( $transactions->('status','failed')->count());
        $failed_transactions = Transaction::where('status','failed')->where('transaction_uuid','refund')->count();
		
	
		return response()->success( [
         'all_transactions' => $all_transactions,
         'completed_transactions' => $completed_transactions,
         'pending_transactions' => $pending_transactions,
         'failed_transactions' => $failed_transactions,
        ],200);

		}

			return response()->success($transactions_orders,200);
		
	}
														   
	
	
	
	
	public function transactionCards(Request $request){
			//dd(Transaction::find(1)->inventory);

	
	
	 $dateScope = request('date_scope');
	 $inventory_id = request('inventory_id');
              $expected = 0;
        $from_date = null;
        $to_date = null ;
        if ($dateScope == null) {
            $dateScope == 'Today';
        }
		
		$transactions = null;
			
                $modelName =null;
       $all_transactions = null;
		
	//	dd( $all_transactions);
            $completed_transactions = null;
            $pending_transactions = null;
        $failed_transactions = null;
		
		
		
		
		if ($inventory_id !== null){
			
			 $transactions= Transaction::whereHas('inventory',function($query) use($inventory_id){
			 $query->where('inventories.id',$inventory_id);
			 
			 });
			
			
			
			
				
		  $all_transactions = $transactions->count();
		
	//	dd( $all_transactions);
            $completed_transactions = $transactions->where('status','received')->count();
            $pending_transactions = $transactions->where('status','pending')->count();
        $failed_transactions =$transactions->where('status','failed')->count();
			
			dd(Transaction::where('status','failed')->count());
		
return response()->success( [
         'all_transactions' => $all_transactions,
         'completed_transactions' => $completed_transactions,
         'pending_transactions' => $pending_transactions,
         'failed_transactions' => $failed_transactions,
        ],200);
		}
		
		
		
		
	

		
		
		else {
		
			
                 $modelName = \App\Models\Transaction::class;
       $all_transactions = Transaction::scopeDateRange($transactions, $modelName, $dateScope, $from_date, $to_date)->count();
		
	//	dd( $all_transactions);
            $completed_transactions = Transaction::scopeDateRange($transactions, $modelName, $dateScope, $from_date, $to_date)->where('status','received')->count();
            $pending_transactions = Transaction::scopeDateRange($transactions, $modelName, $dateScope, $from_date, $to_date)->where('status','pending')->count();
        $failed_transactions = Transaction::scopeDateRange($transactions, $modelName, $dateScope, $from_date, $to_date)->where('status','failed')->count();
		
	
		return response()->success( [
         'all_transactions' => $all_transactions,
         'completed_transactions' => $completed_transactions,
         'pending_transactions' => $pending_transactions,
         'failed_transactions' => $failed_transactions,
        ],200);

		}
	
	} 
	
	

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Transaction  $transaction
     * @return \Illuminate\Http\Response
     */
    public function show(Transaction $transaction)
    {
        //
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Transaction  $transaction
     * @return \Illuminate\Http\Response
     */
    public function edit(Transaction $transaction)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Transaction  $transaction
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Transaction $transaction)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Transaction  $transaction
     * @return \Illuminate\Http\Response
     */
    public function destroy(Transaction $transaction)
    {
        //
    }
}
