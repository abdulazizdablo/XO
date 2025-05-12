<?php

namespace App\Http\Controllers\Dashboard;

use App\Enums\Roles;
use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\FcmToken;
use App\Models\User;
use App\Models\Order;
use App\Services\OrderService;
use Exception;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;
use App\Models\Setting;
use App\Models\Transaction;
use App\Traits\FirebaseNotificationTrait;
use App\Exports\OrdersExport;
use Maatwebsite\Excel\Facades\Excel;

class OrderController extends Controller
{
	use FirebaseNotificationTrait;

    protected $ecashPaymentService;


    public function __construct(
        protected OrderService $orderService,
    ) {

    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            $filter_data = $request->only([
                'invoice_number',
                'inventory',
                'status',
                'pricing_min',
                'pricing_max',
                'quantity',
                'date_min',
                'date_max',
                'delivery_min',
                'delivery_max',
                'search'
            ]);

            $sort_data = $request->only([
                'sort_key',
                'sort_value',
            ]);

            // type : fulfilled, open, nothing
            $type = request('type');

            $orders = $this->orderService->getAllOrders($filter_data, $sort_data, $type);

            return response()->success(
                $orders,
                Response::HTTP_OK
            );
        } catch (Exception $th) {
            return response()->error(
                $th->getMessage(),
                Response::HTTP_NOT_FOUND,
            );
        }
    }

    public function OrdersWarehouseAdmin(Request $request)
    {
        try {
            $employee = auth('api-employees')->user();
            
			if (!$employee) {
                return response()->error('Unauthorized', 403);
            }

            $filter_data = $request->only([
                'invoice_number',
                'inventory',
                'status',
                'pricing_min',
                'pricing_max',
                'quantity',
                'day',
                'date_min',
                'date_max',
                'delivery_min',
                'delivery_max',
                'search'
            ]);

            $sort_data = $request->only([
                'sort_key',
                'sort_value',
            ]);

            // type : fulfilled, open, nothing
            $type = request('type');


            // $filter_data = $request->only([
            //     'date',
            //     'search',
            //     'type',
            //     'status',
            // ]);

            // $sort_data = $request->only([
            //     'sort_key',
            //     'sort_value'
            // ]);


            $orders = $this->orderService->getOrdersByWarehouseAdmin($employee, $type, $filter_data, $sort_data, $request);

            return response()->success(
                $orders,
                Response::HTTP_OK
            );
        } catch (\Throwable $th) {
            return response()->json(
                [
                    'error' => $th->getMessage(),
                ]
            );
        }
    }

    public function readyToDeliver()
    {
        try {
            $employee = auth('api-employees')->user();
            if (!$employee) {
                throw new Exception('Employee does not exist');
            }
            $order_id = request('order_id');
            $order = $this->orderService->readyToDeliver($order_id, $employee);

            return response()->success(
                $order,
                Response::HTTP_OK
            );
        } catch (Exception $th) {
            return response()->error(
                $th->getMessage(),
                Response::HTTP_NOT_FOUND,
            );
        }
    }

    public function sendSubOrder()
    {
        try {
            $employee = auth('api-employees')->user();
            if (!$employee) {
                throw new Exception('Employee does not exist');
            }
            $order_id = request('order_id');
            $order = $this->orderService->sendSubOrder($order_id, $employee);

            return response()->success(
                $order,
                Response::HTTP_OK
            );
        } catch (Exception $th) {
            return response()->error(
                $th->getMessage(),
                Response::HTTP_NOT_FOUND,
            );
        }
    }

    public function confirmReceiveSub()
    {
        try {
            $employee = auth('api-employees')->user();
            if (!$employee) {
                throw new Exception('Employee does not exist');
            }
            $order_item_id = request('order_item_id');
            $order = $this->orderService->confirmReceiveSub($order_item_id, $employee);

            return response()->success(
                $order,
                Response::HTTP_OK
            );
        } catch (Exception $th) {
            return response()->error(
                $th->getMessage(),
                Response::HTTP_NOT_FOUND,
            );
        }
    }
	
	public function refundPayment(){
		try{
			$employee = auth('api-employees')->user();

			if(!$employee){
				return response()->error('Unauthorized',403);
			}
			if($employee->hasRole(Roles::MAIN_ADMIN)){ 
				$transaction_id = request('transaction_id');
				$transaction = Transaction::where('transaction_uuid','refund')->find($transaction_id);
				if(!$transaction){
					return response()->error('Transacrion not available',404);
				}
				if($transaction->status == 'completed'){
					return response()->json('Transaction already refunded',400);
				}
				$transaction->update([
					'status' => 'completed'
				]);
				
				$user = User::findOrFail($transaction->user_id);
				
				if(!$user){
					return response()->error('User does not exist',404);	
				}
				
				$title = ["ar"=>"تم إعادة المبلغ إلى حسابك بنجاح",
                "en"=>"Your money refunded to you successfully"];
				
				$body = ["ar"=>"تم إعادة المبلغ إلى حسابك بنجاح",
                "en"=>"Your money refunded to you successfully"];
				
				$fcm_tokens = $user->fcm_tokens()->pluck('fcm_token')->toArray();
			
				foreach($fcm_tokens as $fcm){
					$fcm_token = FcmToken::where([['fcm_token', $fcm],['user_id',$user->id]])->first();
					if($fcm_token->lang == 'en'){
						$this->send_notification($fcm, 
												 'Your money refunded to you successfully',
												 'Your money refunded to you successfully, Your money refunded to you successfully, Your money refunded to you successfully',
												 'Notification', 
												 'flutter_app'); // key source	
					}else{
						$this->send_notification($fcm, 
												 'تم إعادة المبلغ إلى حسابك بنجاح',
												 'تم إعادة المبلغ إلى حسابك بنجاح, تم إعادة المبلغ إلى حسابك بنجاح, تم إعادة المبلغ إلى حسابك بنجاح',
												 'Notification', 
												 'flutter_app'); // key source
					}	
				}
				
			$user->notifications()->create([
				'user_id'=>$user->id,
				'type'=> 'Notification', // 1 is to redirect to the orders page
				'title'=>$title,
				'body'=>$body
			]);
				
				return response()->success('Transaction was completed successfully',
                Response::HTTP_OK
            );
			}
			
        } catch (Exception $th) {
            return response()->error(
                $th->getMessage(),
                Response::HTTP_NOT_FOUND,
            );
        }
	}

    public function openOrderDetails()
    {

        try {
            $employee = auth('api-employees')->user();
            if (!$employee) {
                throw new Exception('Employee does not exist');
            }
            $order_id = request('order_id');
            $order = $this->orderService->showOpenOrderItems($order_id, $employee);

            return response()->success(
                $order,
                Response::HTTP_OK
            );
        } catch (Exception $th) {
            return response()->error(
                $th->getMessage(),
                Response::HTTP_NOT_FOUND,
            );
        }

    }



    public function subOrderDetails()
    {

        try {
            $employee = auth('api-employees')->user();
            if (!$employee) {
                throw new Exception('Employee does not exist');
            }
            $order_id = request('order_id');
            $order = $this->orderService->showSubOrderItems($order_id, $employee);

            return response()->success(
                $order,
                Response::HTTP_OK
            );
        } catch (Exception $th) {
            return response()->error(
                $th->getMessage(),
                Response::HTTP_NOT_FOUND,
            );
        }

    }
	

	public function shippingInfo(Request $request)
{
    // Retrieve the shipping notes setting
    $shipping_info = Setting::where('key', 'shippingNotes')->firstOrFail();

    // Retrieve the order and its shipment details
    $order = Order::findOrFail($request->order_id)->load('shipment');

    // Prepare the shipping details array
    $shipping_details = [
        'city' => $order->shipment->city?? null,
        'street' => $order->shipment->street ?? null,
        'detailed_shipping' => $order->shipment->additional_details ?? null, // Use null coalescing operator to handle cases where additional_details might not exist
        'shipping_info' => $shipping_info->value
    ];

    // Return the shipping details as a JSON response with a 200 status code
    return response()->json($shipping_details, 200);
}

	

    public function counts(Request $request)
    {
        try {
            $filter_data = $request->only(['status', 'date']);
            $orders_count = $this->orderService->ordersCharts($filter_data);
            return response()->success(
                $orders_count,
                Response::HTTP_OK,
            );
        } catch (Exception $th) {
            return response()->error(
                $th->getMessage(),
                Response::HTTP_NOT_FOUND,
            );
        }
    }

    public function inventoriesChart(Request $request)
    {
        try {
            $orders_count = $this->orderService->getInventoriesCountsChart();
            return $orders_count;
        } catch (Exception $th) {
            return response()->error(
                $th->getMessage(),
                Response::HTTP_NOT_FOUND,
            );
        }
    }

    public function cards(Request $request)
    {
        try {
            $employee = auth('api-employees')->user();
            if (!$employee) {
                throw new Exception('Employee does not exist');
            }
			$validate = Validator::make($request->only('inventory_id'),
            [
                'inventory_id' => 'sometimes|integer|exists:inventories,id',
            ]);
			if ($validate->fails()) {
            	return response()->error($validate->errors(), 422);
        	}
            $cards = $this->orderService->getOrderCards($employee, $validate->validated(['inventory_id']));
            return response()->success($cards, Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
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
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */

    public function show(Order $order)
    {
        try {
            $order_id = request('order_id');
            $order = $this->orderService->showDash($order_id);

            return response()->success(
                $order,
                Response::HTTP_OK
            );
        } catch (Exception $th) {
            return response()->error(
                $th->getMessage(),
                Response::HTTP_NOT_FOUND,
            );
        }
    }

    public function showOrderDetails(Request $request)
    {
        try {
			$employee = auth('api-employees')->user();
            if (!$employee) {
                throw new Exception('Employee does not exist');
            }
            $order_id = $request->order_id;
            $order = $this->orderService->showOrderDetails($order_id, $employee);

            return response()->success(
                $order,
                Response::HTTP_OK
            );
        } catch (Exception $th) {
            return response()->error(
                $th->getMessage(),
                Response::HTTP_NOT_FOUND,
            );
        }
    }

    public function showItems()
    {
        try {
            $order_id = request('order_id');
            $order = $this->orderService->showOrderItems($order_id);

            return response()->success(
                $order,
                Response::HTTP_OK
            );
        } catch (Exception $th) {
            return response()->error(
                $th->getMessage(),
                Response::HTTP_NOT_FOUND,
            );
        }
    }

    public function getInvoice()
    {
        try {
            $order_id = request('order_id');
            $invoice_number = request('invoice_number');
            $order = $this->orderService->getInvoice($order_id, $invoice_number);

            return response()->success(
                $order,
                Response::HTTP_OK
            );
        } catch (Exception $th) {
            return response()->error(
                $th->getMessage(),
                Response::HTTP_NOT_FOUND,
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
	
	public function export()
    {
        /*return Product::select("id", "name", "description")
        ->with(['product_variations' => function ($query) {
            $query->select('id');
        }])->get();*/
        return Excel::download(new OrdersExport, 'orders.xlsx');
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
