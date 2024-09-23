<?php

namespace App\Http\Controllers\Users\v1;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Order;
use Exception;
use App\Services\DeliveryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use App\Enums\Roles;

class DeliveryController extends Controller
{
    protected $deliveryService;

    public function __construct(DeliveryService $deliveryService)
    {
        $this->deliveryService = $deliveryService;
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
		
        try{
        
			$employee = auth('api-employees')->user();	
		
			if(!$employee){
				return response()->json('Unautorized',403);
			}
			
			if(!$employee->hasRole(Roles::DELIVERY_BOY)){
			
			return response()->json('Permission denied',403);
			
			}

            //$employee = Employee::find(8);
            // return $employee->account()->first();
            $type = request('type');
            $date = request('date');
            //use the service class to get the orders by employee
            $data = $this->deliveryService->getAllDeliveryOrders($employee, $type, $date);

            //return the data as a response
            return response()->json($data);
            
        } catch(\Exception $e){
            return response()->error(
                $e->getMessage(),
                Response::HTTP_BAD_REQUEST);
        }
    }
    
    public function mainPage(){
        try{
             $employee = auth('api-employees')->user();
			if(!$employee){
				return response()->json('Unautorized',403);
			}
            //$employee = Employee::find(8);
            // return $employee->account()->first();
            $type = request('type');
            $order_num = request('order_num');
            //use the service class to get the orders by employee
            $data = $this->deliveryService->getMainPageDeliveries($employee, $type, $order_num);

            //return the data as a response
            return response()->json($data);
            
        } catch(\Exception $e){
            return response()->error(
                $e->getMessage(),
                Response::HTTP_BAD_REQUEST);
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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        try{
            $order_id = request('order_id');
            $employee = auth('api-employees')->user();
            //$employee = Employee::find(8);
            // return $employee->account()->first();
			if(!$employee){
				return response()->json('Unautorized',403);
			}
            //use the service class to get the order by id
            $order = $this->deliveryService->getDeliveryOrder($employee, $order_id);

            //return the order as a response
            return response()->json($order);
            
        } catch(\Exception $e){
            return response()->error(
                $e->getMessage(),
                Response::HTTP_BAD_REQUEST);
        }    
    }

    public function confirmOrderIsDelivered(Request $request)
    {
        try {
            $employee = auth('api-employees')->user();
			if(!$employee){
				return response()->json('Unautorized',403);
			}
            //$employee = Employee::find(19);
            $validate = Validator::make(
                $request->all(),
                [
                    'order_id' => 'required|exists:orders,id',
                ]
            );
            if ($validate->fails()) {
                return response()->error(
                    $validate->errors(),
                    Response::HTTP_BAD_REQUEST
                );
            }
        $this->deliveryService->confirmOrderIsDelivered($request, $employee);
		return response()->success( trans('products.order_delivery_successfull',[],$request->header('Content-Language')), 200);
        } catch (\Exception $e) {
            return response()->error(
                $e->getMessage(),
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    public function myAccount()
    {
        try { 
            $employee = auth('api-employees')->user();
			if(!$employee){
				return response()->json('Unautorized',403);
			}
            $employee = Employee::with('shift')
            // ->where('id', 8)
            ->where('id', $employee->id)
            ->select('id', 'first_name', 'last_name', 'shift_id')->first();
            
            return $employee;
        } catch (\Exception $e) {
            return response()->error(
                $e->getMessage(),
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    public function deliveryHistory(){
        try{
            $employee = auth('api-employees')->user();
			$date = request('date');

            if(!$employee){
				return response()->json('Unautorized',403);
			}
			//$employee = Employee::find(8);
            // return $employee->account()->first();

            //use the service class to get the orders by employee
            $data = $this->deliveryService->getDeliveryHistory($employee, $date);

            //return the data as a response
            return response()->json($data);
            
        } catch(\Exception $e){
            return response()->error(
                $e->getMessage(),
                Response::HTTP_BAD_REQUEST);
        }    
    }
	
	public function cancelDelivery(Request $request){
		$validate = Validator::make(
                $request->all(),
                [
                    'order_id' => 'required|exists:orders,id',
                ]
            );
            if ($validate->fails()) {
                return response()->error(
                    $validate->errors(),
                    Response::HTTP_BAD_REQUEST
                );
            }
		$employee = auth('api-employees')->user();
		if(!$employee){
				return response()->json('Unautorized',403);
			}
		$data = $this->deliveryService->cancelDelivery($employee, $request);
	}


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
