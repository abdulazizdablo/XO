<?php

namespace App\Http\Controllers\Dashboard;


use App\Http\Controllers\Controller;

use App\Http\Requests\Account\CreateAccountRequest;
use App\Http\Requests\Account\GetAssignDurationsRequest;
use App\Http\Requests\Account\RevealPasswordRequest;
use App\Http\Requests\Account\RolesFilterRequest;
use App\Http\Requests\Account\UnassignAccountRequest;
use App\Http\Requests\Account\UpdateAccountRequest;
use App\Http\Requests\Employee\AssignAcctoEmpRequest;
use App\Http\Requests\Employee\CreateEmployeeRequest;
use App\Http\Requests\Employee\GetCurrentEmp;
use App\Http\Requests\Employee\GetLastEmps;
use App\Http\Requests\Employee\UpdateEmployeeRequest;
use Illuminate\Support\Str;
use App\Models\Account;
use App\Models\AssignDuration;
use App\Models\Employee;
use App\Services\AdminService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Http\JsonResponse;
use App\Traits\TranslateFields;
use Illuminate\Support\Facades\Crypt;
use App\Http\Requests\Employee\RevealPasswordEmpRequest;
use App\Traits\FirebaseNotificationTrait;
use App\Enums\Roles;
use App\Models\Role;
use App\Traits\CloudinaryTrait;
use Carbon\Carbon;
use App\Models\StockLevel;
use Log;


class AdminController extends Controller
{


    use  CloudinaryTrait,TranslateFields, FirebaseNotificationTrait;

    public function __construct(protected AdminService $adminService)
    {
    }
 
	
    public function getAccounts(RolesFilterRequest $request)
    {


        //  $accounts_and_roles = $this->adminService->getAccountsAndRoles($request->validated('role_filter'));
        if ($request->filled('role_filter')) {



            $role_filter = $request->role_filter;

            $roles_and_accounts = Account::with('roles:name')->withCount('roles')->whereHas('roles', function ($query) use ($role_filter) {


                $query->where('name', 'LIKE', $role_filter);
            })->latest('created_at')->paginate(10);



            $roles_and_accounts->transform(function ($account) {
                $account->roles->transform(function ($role) {
                    $role->name = Str::title(str_replace('_', ' ', $role->name));
                    return $role;
                });
                return $account;
            });

            // $employes_to_check = new Collection();

            /*  $employes_to_check =  $roles_and_accounts->map(function ($item, $key) {

                return $item['id'];
            });

            $employees = Employee::whereIn('account_id', $employes_to_check)->get();

*/
            $roles_and_accounts->each(function ($item) {

                $item->makeHidden(['created_at', 'deleted_at', 'updated_at', 'password']);
            });
            return response()->success($roles_and_accounts, 200);

            //    $accounts = new Collection();
            /*  $roles =  new Collection();
            $roles_and_accounts->each(function ($item, $key) use ($accounts, $roles) {

              //  $role_name = $item->roles->pluck('name')->first();

                $roles[$role_name] =  $item->roles_count;
               // $accounts[] = $item->only(['id', 'email', 'password', $role_name => 'roles']);
            });
            $roles['count_all'] = $roles->sum();*/
        } else {
            // $accounts =  AccountResource::collection($roles_and_accounts)->paginate(10);
            $roles_and_accounts = Account::with('roles:name')->withCount('roles')->latest('created_at')->paginate(10);
            $roles_and_accounts->each(function ($item) {

                $item->makeHidden(['created_at', 'deleted_at', 'updated_at', 'password']);
            });



            return response()->success($roles_and_accounts, 200);
        }
        //dd(Account::with('role')->first());
        /*$roles =  new Collection();
$roles_and_accounts->each(function ($item, $key) use ($accounts, $roles) {

    $role_name = $item->roles->pluck('name')->first();

    $roles[$role_name] =  $item->roles_count;


   // $accounts[] = $item->only(['id', 'email', 'password', $role_name => 'roles']);
   
});
$roles['count_all'] = $roles->sum();
}


return */
        /*   $accounts = new Collection();
            $roles =  new Collection();
            $roles_and_accounts->each(function ($item, $key) use ($accounts, $roles) {

                $role_name = $item->roles->pluck('name')->first();

                $roles[$role_name] =  $item->roles_count;

            
                $accounts[] = $item->only(['id', 'email', 'password', $role_name => 'roles']);
               
            });
            $roles['count_all'] = $roles->sum();*/



        // Replace [...] with your actual user data

        // Paginate the collection
        /*  $perPage = 10; // Number of items per page
        $currentPage = request()->get('page', 1);
        
        // Get the subset of items for the current page
        $currentPageItems = $accounts->slice(($currentPage - 1) * $perPage, $perPage)->values();
        
        // Create a LengthAwarePaginator instance
        $paginator = new LengthAwarePaginator(
            $currentPageItems,
            $accounts->count(),
            $perPage,
            $currentPage,
            ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath()]
        );



        $paginatedData = [
            'data' => $paginator->items(),
            'current_page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            // Add any other pagination information you need
        ];


      */

        //  return response()->success($accounts::paginate(30), 200);
    }


    public function showUnLinkedEmps()
    {

        $emps = $this->adminService->displayUnlinkedEmps();

        return response()->success($emps, 200);
    }

    public function createAccount(CreateAccountRequest $request)
    {
        try {
			
					

            $account = $this->adminService->createAccount($request->validated());

            // Assuming you want to send a notification to the main admin
            if (auth('api-employee')->user()->hasRole(Roles::MAIN_ADMAIN)) {
                // Corrected the guard name to 'api-employee' for consistency
                $this->send_notification(auth('api-employee')->user()->fcm_token->fcm_token, '');

                return response()->json(['created_account' => $account, 'message' => 'Account has been created successfully'], 201);
            }

            return response()->success(['message' => 'Account creation successful, but no notification sent.'], 201);

        } catch (\Exception $e) {
            // Log the exception for debugging purposes
            \Log::error('Error creating account: ' . $e->getMessage());

            // Return a generic error response
            return response()->json(['error' => 'An error occurred while creating the account.'], 500);
        }
    }


    public function updateAccount(UpdateAccountRequest $request)
    {

        $account = $this->adminService->updateAccount($request->validated());


        return response()->success(['updated_account' => $account, 'message' => 'Account has been updated successfully'], 200);
    }

    public function deleteAccount(Request $request)
    {

        $account = Account::findOrFail($request->account_id);
		
        $this->adminService->deleteAccount($account);


        return response()->success('Account has been deleted successfully', 200);
    }

    public function forceDeleteAccount(Account $account)
    {
        if (!$account) {

            throw new ModelNotFoundException();
        }
        $this->adminService->forceDeleteAccount($account);

        return response()->success('Account has been deleted successfully', 200);
    }
    public function assignAcctoEmp(AssignAcctoEmpRequest $request)
    {

        $response_data = $this->adminService->assignAccToEmp($request->account_id, $request->employee_id);

//dd($response_data instanceof JsonResponse);
		 if ($response_data instanceof JsonResponse) {

            return $response_data;
        }

		
		
      else   if ($response_data instanceof Employee) {

            $this->adminService->createAccDuration($response_data);
            return response()->success([$response_data->account, 'message' => 'Account has been linked to this employee successfully'], 200);
        }

        return response()->success('Account has been linked to this employee successfully', 200);
    }
public function getRoles(){

    return Role::all('id','name');
}
    public function unassignAcc(UnassignAccountRequest $request)
    {

        $response = $this->adminService->unassignAcc($request->validated('account_id'));
        if ($response) {

            return $response;
        } else

            return response()->success('Account has been unlinked from this employee successfully', 200);
    }

    public function assignAccToRole(Account $account, $roles)
    {
        $this->adminService->assignAccToRole($account, $roles);

        return response()->success('Role has been linked to the desired Account Successfully', 200);
    }
    public function reAssignAccToRole(Account $account, $roles)
    {

        $this->adminService->reAssignAccToRole($account, $roles);

        return response()->success('The Role has been re-linked to the desired Account successfully', 200);
    }



    public function createEmp(CreateEmployeeRequest $request)
    {

        try {
            $emp_data = $request->validated();

            $employee = $this->adminService->createEmp($emp_data);

            // Assuming you want to perform some action or send a notification here
            // For example, sending a notification to the main admin
			
			
			// Super Admin
			
			
			

          /*  $this->send_notification(auth('api-employees')->user()->fcm_token->fcm_token, 'إنشاء حساب موظف', 'لقد تم إنشاء حساب موظف باسم ' . $employee->full_name);
*/

            return response()->success(['created_employee' => $employee, 'message' => 'Employee has been created successfully'], 201);

        } catch (\Exception $e) {
            // Log the exception for debugging purposes
            \Log::error('Error creating employee: ' . $e->getMessage());

            // Return a generic error response
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function updateEmp(UpdateEmployeeRequest $request)
    {


        $employee = $this->adminService->updateEmp($request->validated());


        return response()->success(['updated_employee' => $employee, 'message' => 'Employee has been updated successfully'], 200);
    }
    public function displayEmps()
    {

        $employees = $this->adminService->displayEmps();

        return response()->success($employees, 200);
    }





    public function showEmps()
    {


        $emps = $this->adminService->displayEmps();

        return response()->success($emps, 200);
    }

    public function deliveryAdminDetails(Request $request)
    {

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



        $delivery_admin = Employee::findOrFail($request->employee_id)->load(['account', 'orders.user'])->loadCount('orders');


        if ($delivery_admin->account->roles->first()->id == 5) {
            $orders = $delivery_admin->orders()->with('user');


            if (!empty($filter_data)) {
                $orders = $this->adminService->applyFilters($orders, $filter_data);
            }

            if (!empty($sort_data)) {
                $orders = $this->adminService->applySort($orders, $sort_data);
            }

            $orders = $orders->paginate(8);

            return response()->success($orders, 200);


        }

    }


    public function deliveryAdmin(Request $request)
    {

        $delivery_admin = Employee::findOrFail($request->employee_id)->loadCount('orders');


        if ($delivery_admin->account->roles->first()->id == 5) {


            return response()->success($delivery_admin->only(['first_name', 'last_name', 'email', 'phone', 'created_at', 'orders_count']), 200);
        }



       

        /*  public function accountHistory(GetAssignDurationsRequest $request)
    {


        $employees_data = $this->adminService->accountHistory($request->account_id);


        return response()->success(['current_employee' => $employees_data['current_employee'], 'last_employees' => $employees_data['last_employees']], 200);
    }*/
    }
    public function revealPassword(RevealPasswordRequest $request)
    {

        $account_to_reveal = Account::findOrFail($request->validated('account_id'));

        $revealed_password = Crypt::decryptString($account_to_reveal->password);


        return response()->success(['revealed_password' => $revealed_password], 200);
    }


    public function revealPasswordEmp(RevealPasswordEmpRequest $request)
    {

        $employee = Employee::findOrFail($request->validated('employee_id'));

        $password = Crypt::decryptString($employee->password);

        return response()->success(['revealed_password' => $password], 200);
    }

    public function getCurrentEmp(GetCurrentEmp $request)
    {
        $current_emp = $this->adminService->currentEmp($request->validated('account_id'));


        if ($current_emp instanceof JsonResponse) {



            return $current_emp;
        } else


            return response()->success(['current_employee' => $current_emp], 200);
    }


    public function getLastEmps(GetLastEmps $request)
    {
        $last_emps = $this->adminService->lastEmps($request->account_id);


        return response()->success($last_emps, 200);
    }

    public function createAdminUser()
    {
        //$full_url = "https://bms.syriatel.sy/API/Add_Admin_By_Agent.aspx?agent_user_name=XO&agent_pass=P@1234567&first_name=xo&last_name=textile&user_name=xo_textile&password=xo123456";

        //$full_url= "https://bms.syriatel.sy/API/Add_Normal_By_Admin.aspx?admin_user_name=XO&admin_pass=P@1234567&first_name=xo&last_name=textile&user_name=xo_textile&mobile=963933096270";

        //$full_url = "https://bms.syriatel.sy/API/SendSMS.aspx?job_name=marksJob&user_name=XO&password=P@1234567&msg=TestMSG&sender=xo&to=963993572688";
//$full_url = "https://bms.syriatel.sy/API/CheckJobStatus.aspx?user_name=XO&password=P@1234567&job_id=266943412";
        //return $response = Http::post( $full_url);

        $ch = curl_init();
        curl_setopt(
            $ch,
            CURLOPT_URL,
            "https://bms.syriatel.sy/API/SendSMS.aspx?job_name=XO&user_name=XO&password=P@1234567&msg=Hello_Shafik&sender=xo&to=963933096270"
        );
        //"https://bms.syriatel.sy/API/CheckJobStatus.aspx?user_name=XO&password=P@1234567&job_id=266946392");
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        if (curl_exec($ch) === false) {
            echo 'Curl error: ' . curl_error($ch);
        } else {
            ///echo 'Operation completed without any errors';
        }
        curl_close($ch);

    }
}
