<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\FcmToken;
use App\Models\Notification;
use App\Services\EmployeeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;
use Illuminate\Support\Facades\Crypt;
use Symfony\Component\HttpFoundation\Response;

class EmployeeController extends Controller
{

    public function __construct(
        protected EmployeeService $employeeService
    ) {
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $employees = $this->employeeService->getAllEmployees();

        return response()->success([
            'employee' => $employees
        ], Response::HTTP_OK);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        //
    }
	
	    public function getUserNotifications()
    {
        //$this->send_notification('fEkiuCzgT3aXaoIUWjAOgY:APA91bHhu53TguvlQppO2ODYyEyu4SYYewxPfjj9nUXBus7rwMDUNPSPSLjJcpClUAqEJJwjweIyTPSGag81qr1McOrEuehufGkP8aBYFhwEDY5D0MBF8OBiD8lS6YQDiGVm2rABJxmA', 'توفر المنتج الخاص بك!', 'توفر الآن المقاس واللون اللذي أضفته لقائمة اللإشعارات', 'dress');
        try {
            $employee = auth('api-employees')->user();
            if (!$employee) {
                return response()->json('Unauthorized', 403);
            }
            // $user_id  = $user->id;
            //   $user_id = 1 ;
			$page_size = request('pageSize');
            $notifications = Notification::where('employee_id', $employee->id)->latest();
			if ($page_size >= $notifications->count()) {
						if ($notifications->count() == 0) {						
							return response()->error('There is no notifications for you',404);	
						}

						//  $page_size = 1;

						$notifications = $notifications->paginate($page_size);
					} else {

						$notifications = $notifications->paginate($page_size);
					}

            return response()->success(
                $notifications,
                Response::HTTP_OK
                // OR HTTP_NO_CONTENT
            );
        } catch (\Throwable $th) {
            return response()->error(
                $th->getMessage(),
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    /**
     *
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $inventory_id = request('inventory_id');
            $shift_id = request('shift_id');
            $validate = Validator::make(
                $request->only('first_name', 'last_name', 'phone', 'email', 'password', 'address', 'role_id'),
                [
                    'first_name' => 'required|string|max:255',
                    'last_name' => 'required|string|max:255',
                    'phone' => 'required|string|unique:employees',
                    'email' => 'required|email|unique:employees',
                    'password' => 'required|string|max:255',
                    'address' => 'required|string|max:255',
                    'role_id' => 'required|max:255',
                ]
            );

            if ($validate->fails()) {
                return response()->error(
                    $validate->errors(),
                    422
                );
            }

            $validated_data = $validate->validated();
            // $employee_data = $validated_data['employee'];

            $employee = $this->employeeService->createEmployee($validated_data, $inventory_id, $shift_id);

            return response()->success(
                [
                    // 'message' => 'Employee Is Created',
                    'data' => $employee,
                ],
                Response::HTTP_CREATED
            );
        } catch (\Throwable $th) {
            return response()->error(
                $th->getMessage(),
                Response::HTTP_OK
            );
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Employee  $employee
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        try {
            $employee_id = request('employee_id');
            $employee = $this->employeeService->getEmployee($employee_id);

            return response()->success(
                [
                    'employee' => $employee
                ],
                Response::HTTP_FOUND
            );
        } catch (InvalidArgumentException $e) {
            return response()->error(
                $e->getMessage(),
                Response::HTTP_NOT_FOUND
            );
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Employee  $employee
     * @return \Illuminate\Http\Response
     */
    public function edit(Employee $employee)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Employee  $employee
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        try {
            $inventory_id = request('inventory_id');
            $shift_id = request('shift_id');
            $employee_id = request('employee_id');

           $validate = Validator::make(
    $request->only('first_name', 'last_name', 'email', 'phone', 'address'), // Specify the fields you want to validate
    [
        'first_name' => 'required|string|max:255',
        'last_name' => 'required|string|max:255',
        'email' => 'required|email|unique:employees',
        'phone' => 'sometimes|string|unique:employees', // Optional field
        'address' => 'sometimes|string|max:255', // Optional field
    ]
);

            if ($validate->fails()) {
                return response()->error(

                    $validate->errors(),
                  422
                );
            }

            $validated_data = $validate->validated();
            $employee_data = $validated_data['employee'];

            $employee = $this->employeeService->updateEmployee($employee_data, $employee_id, $inventory_id, $shift_id);

            return response()->success(
                [
                    'message' => 'Employee Updated Successfully',
                    'data' => $employee,
                ],
                Response::HTTP_OK
                // OR HTTP_NO_CONTENT
            );
        } catch (\Throwable $th) {
            return response()->error(
                $th->getMessage(),
                Response::HTTP_OK
            );
        }
    }


    public function logout(Request $request){
 
        try {
            auth('api-employees')->user()->currentAccessToken()->delete();
            return response()->success(
                'Logged out',
                Response::HTTP_OK
            );
        } catch (\Error $ex) {
            return response()->error(
                ["error" => $ex->getMessage(), "message" => "The token is not valid or expired"],
                Response::HTTP_UNAUTHORIZED
            );
        } catch (\Exception $ex) {
            return response()->error(
                ["error" => $ex->getMessage(), "message" => "The token is not valid or expired"],
                Response::HTTP_UNAUTHORIZED
            );
        }

    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Employee  $employee
     * @return \Illuminate\Http\Response
     */
    public function destroy()
    {
        try {
            $employee_id = request('employee_id');
            $employee = $this->employeeService->delete($employee_id);

            return response()->success(
                [
                    'message' => 'Employee deleted successfully',
                    'data' => $employee,
                ],
                Response::HTTP_OK
            );
        } catch (\Throwable $th) {
            return response()->error(
                $th->getMessage(),
                Response::HTTP_OK
            );
        }
    }

    public function delievryToken(Request $request){
$devlevry_boy = Employee::findOrFail($request->employee_id)->createToken();


    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Employee  $employee
     * @return \Illuminate\Http\Response
     */
    public function forceDelete()
    {
        try {
            $employee_id = request('employee_id');
            $employee = $this->employeeService->forceDelete($employee_id);

            return response()->success(
                [
                    'message' => 'Employee deleted successfully',
                    'data' => $employee,
                ],
                Response::HTTP_OK
            );
        } catch (\Throwable $th) {
            return response()->error(
                $th->getMessage(),
                Response::HTTP_OK
            );
        }
    }

    public function loginEmployee(Request $request)
    {
        try {
            $validate = Validator::make(
                $request->all(),
                [
                    'employee.email' => 'required|email|max:255',
                    'employee.password' => 'required|string|max:255',
                ]
            );

            if ($validate->fails()) {
                return response()->error(
                    $validate->errors(),
                    Response::HTTP_BAD_REQUEST
                );
            }

            $validated_data = $validate->validated();
            $employee_data = $validated_data['employee'];

            $employee = $this->employeeService->loginEmployee($employee_data);

            return response()->success(
                [
                    'message' => $employee,
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
    public function getEmployeeDataByToken()
    {

        $employee = auth('api-employees')->user();

        return response()->json($employee, 200);
    }

    public function revealPassword()
    {
        $employee_id = request('employee_id');
        $employee = $this->employeeService->revealPassword($employee_id);
    }

    public function getEmployeeRoleByToken()
    {

        $employee = auth('api-employees')->user();
        // return $employee;
        $role = $employee->getRoleNames();

        return response()->json([$employee, $role], 200);
    }


    public function Changerole(Request $request)
    {

        try {
            $employee_id = request('employee_id');
            $role_id = request('role_id');

            $employee = $this->employeeService->Changerole($employee_id, $role_id);

            return response()->success(
                [
                    'message' => 'Employee Updated Role Successfully',
                    'data' => $employee,
                ],
                Response::HTTP_OK
                // OR HTTP_NO_CONTENT
            );
        } catch (\Throwable $th) {
            return response()->error(
                $th->getMessage(),
                Response::HTTP_OK
            );
        }
    }


    public function getAllRoles()
    {

        try {

            $roles = $this->employeeService->getAllRoles();

            return response()->success(
                [
                    // 'message' => 'Employee Updated Role Successfully',
                    'data' => $roles,
                ],
                Response::HTTP_OK
                // OR HTTP_NO_CONTENT
            );
        } catch (\Throwable $th) {
            return response()->error(
                $th->getMessage(),
                Response::HTTP_OK
            );
        }
    }
	
	    public function addFcmToken(Request $request)
    {
        $employee = auth('api-employees')->user();
        if (!$employee) {
            return response()->json(['message' => 'Not Authenticated'], 401);
        }
        $validator = Validator::make(
            $request->all(),
            [
                'fcm_token' => 'required',
				'lang' => 'nullable|in:en,ar',
                //'device_name' => 'required'
            ]
        );

        if ($validator->fails()) {
            return response()->json(['message' => 'The token field is required'], 400);
        }
        try {
            $exists = $employee->fcm_tokens()->where('fcm_token','=',$request->post('fcm_token'))->exists();
            if(!$exists){
                $employee->fcm_tokens()->create([
                'fcm_token' => $request->post('fcm_token'),
				//'device_name' => 'test'
                'device_name' => $request->post('device_name')??'test',
				'lang' => $request->post('lang')??null,
                ]);
            } 
			$old_tokens = FcmToken::where([['fcm_token',$request->fcm_token],['employee_id','!=',$employee->id]])->get();
			foreach($old_tokens as $old_token){
				$old_token->delete();	
			}
			
			$fcm_tokens = $employee->fcm_tokens()->latest()->take(5)->pluck('id');
			FcmToken::whereNull('user_id')->where([['employee_id',$employee->id],['fcm_token',$request->post('fcm_token')]])
				->update(['lang' => $request->post('lang')??null]);            
			FcmToken::whereNull('user_id')->where('employee_id',$employee->id)->whereNotIn('id',$fcm_tokens)->delete();            
			// $token = FcmToken::where('user_id', $user->id)->Where('device_name', $request->device_name)->first();
            // if ($token != null) {
            //     $fcmToken = $token->update([
            //         'fcm_token' => $request->fcm_token,
            //     ]);
            // } else {

            //     return $fcmToken = FcmToken::create([
            //         'user_id' =>  $user->id,
			// 		'device_name' =>$request->device_name,
            //         'fcm_token' => $request->fcm_token,
            //     ]);
            // }

            return response()->json(['message' => 'User token has been updated successfully'], 200);
        } catch (\Exception $e) {
            //report($e);
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

}
