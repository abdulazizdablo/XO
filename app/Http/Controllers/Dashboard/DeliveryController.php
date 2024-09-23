<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Role;
use Illuminate\Http\Request;
use App\Services\DeliveryService;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;
use App\Services\EmployeeService;
use App\Services\AdminService;



use Exception;

class DeliveryController extends Controller
{
    protected $deliveryService;
    protected $employeeService;
    protected $adminService;

    public function __construct(DeliveryService $deliveryService,
                                EmployeeService $employeeService,
                                AdminService $adminService)
    {
        $this->deliveryService = $deliveryService;
        $this->employeeService = $employeeService;
        $this->adminService = $adminService;
        
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $sort_data = request(['sort_key','sort_value']);
        $inventory_id = request(['inventory_id']);
        try{
            // $employee = auth('api-employees')->user();
            $employee = Employee::find(5);
            if(!$employee){
                throw new Exception('Employee not found');
            }
            //use the service class to get the orders by employee
            $data = $this->deliveryService->getAllDeliveryBoys($employee, $sort_data, $inventory_id);
            //return the data as a response
            return response()->success(
            $data
            , Response::HTTP_OK);
            
        } catch(\Exception $e){
            return response()->error(
                $e->getMessage(),
                Response::HTTP_BAD_REQUEST);
        } 
    }
    
    public function getDeliveries(Request $request){
        try {
            // $employee = auth('api-employees')->user();
            $employee = Employee::findOrFail(5);

            if(!$employee){
                return response()->error('Unauthorized',403);
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

            $type = request('type');

            $orders = $this->deliveryService->getOrdersDashboard($employee, $type, $filter_data, $sort_data );
    
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
        try{
             $employee = auth('api-employees')->user();
    //        $employee = Employee::find(5);
            if(!$employee){
                throw new Exception('Employee not found');
            }
            $inventory_id = request('inventory_id');
            $shift_id = request('shift_id');
         $validate = Validator::make(
    $request->only('first_name', 'last_name', 'phone', 'email', 'password', 'address', 'inventory_id', 'shift_id'),
    [
        'first_name' => 'required|string|max:255',
        'last_name' => 'required|string|max:255',
        'phone' => 'required|string|unique:employees',
        'email' => 'required|email|unique:employees',
        'password' => 'required|string|max:255',
        'address' => 'required|string|max:255',
        'inventory_id' => 'required|integer|exists:inventories,id',
        'shift_id' => 'required|integer|exists:shifts,id',
        // 'role_id' => 'required|integer|exists:roles,id' // Uncomment if you plan to validate this field
    ]
);

            if ($validate->fails()) {
                return response()->error(
                    $validate->errors(),
                    Response::HTTP_UNPROCESSABLE_ENTITY
                );
            }

            $validated_data = $validate->validated();

            if ($validated_data['role_id'] != Role::where('name','delivery_boy')->first()->id){
                throw new Exception('You only can create new delivery boys');
            }
            //use the service class to get the orders by employee
            $data = $this->employeeService->createEmployee($validated_data, $inventory_id, $shift_id);
            //return the data as a response
            return response()->success(
            $data,
            Response::HTTP_OK);
            
        } catch(\Exception $e){
            return response()->error(
                $e->getMessage(),
                Response::HTTP_BAD_REQUEST);
        }    
    }
    
	
	public function getDeliveryBoy(){
        try {
            // $employee = auth('api-employees')->user();
            $employee = Employee::findOrFail(5);

            if(!$employee){
                return response()->error('Unauthorized',403);
            } 
            $boy_id = request('boy_id');

            $orders = $this->deliveryService->getDeliveryBoy($boy_id, $employee);

            return response()->success(
                $orders,
                Response::HTTP_OK
            );
        } catch (Exception $th) {
            return response()->error(
                $th->getMessage(),
                Response::HTTP_NOT_FOUND);
        }    
    }
	
    public function getOrderBoys(){
        try {
            // $employee = auth('api-employees')->user();
            $employee = Employee::findOrFail(5);

            if(!$employee){
                return response()->error('Unauthorized',403);
            } 
            $order_id = request('order_id');

            $order = $this->deliveryService->getOrderBoys($order_id, $employee);

            return response()->success(
                $order,
                Response::HTTP_OK
            );
        } catch (Exception $th) {
            return response()->error(
                $th->getMessage(),
                Response::HTTP_NOT_FOUND);
        }        
    }
    
    public function getOrdersByBoy(){
        try {
            // $employee = auth('api-employees')->user();
            $employee = Employee::findOrFail(5);

            if(!$employee){
                return response()->error('Unauthorized',403);
            } 
            $boy_id = request('boy_id');

            $orders = $this->deliveryService->getOrdersByBoy($boy_id, $employee);

            return response()->success(
                $orders,
                Response::HTTP_OK
            );
        } catch (Exception $th) {
            return response()->error(
                $th->getMessage(),
                Response::HTTP_NOT_FOUND);
        }     
    }
	public function customNotification(){
        try {
            $employee = auth('api-employees')->user();
            // $employee = Employee::find(1);

            if(!$employee){
                return response()->error('Unauthorized',403);
            } 
            $title = request('title');
            $body = request('body');
            $delivery_id = request('delivery_id');
            $notification = $this->deliveryService->customNotification($title, $body, $delivery_id, $employee);

            return response()->success(
                $notification,
                Response::HTTP_CREATED
            );
        } catch (Exception $th) {
            return response()->error(
                $th->getMessage(),
                Response::HTTP_NOT_FOUND);
        }
    }
    
    
    public function assignOrder(){
        try {
            $employee = auth('api-employees')->user();
            // $employee = Employee::find(1);

            if(!$employee){
                return response()->error('Unauthorized',403);
            } 
            $order_id = request('order_id');
            $delivery_id = request('delivery_id');
            $order = $this->deliveryService->assignOrder($order_id, $delivery_id, $employee);

            return response()->success(
                $order,
                Response::HTTP_CREATED
            );
        } catch (Exception $th) {
            return response()->error(
                $th->getMessage(),
                Response::HTTP_NOT_FOUND);
        } 
    }


    public function startDelivery(){
        try {
            $employee = auth('api-employees')->user();
            // $employee = Employee::find(1);

            if(!$employee){
                return response()->error('Unauthorized',403);
            } 
            $order_id = request('order_id');
            $order = $this->deliveryService->startDelivery($order_id, $employee);

            return response()->success(
                $order,
                Response::HTTP_CREATED
            );
        } catch (Exception $th) {
            return response()->error(
                $th->getMessage(),
                Response::HTTP_NOT_FOUND);
        } 
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
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
    public function destroy()
    {
        try {
            $employee_id = request('employee_id');
            $employee = $this->employeeService->delete($employee_id);

            return response()->success(
                [
                    'message' => 'Delivery boy deleted successfully',
                    'data' => $employee,
                ],
                Response::HTTP_OK
            );
        } catch (\Throwable $th) {
            return response()->error(
                $th->getMessage(),
                Response::HTTP_BAD_REQUEST
            );
        }
    }
}
