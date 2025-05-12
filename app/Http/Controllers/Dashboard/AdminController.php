<?php

namespace App\Http\Controllers\Dashboard;


use App\Http\Controllers\Controller;

use App\Http\Requests\Account\CreateAccountRequest;
use App\Http\Requests\Employee\RequiredEmployeeIdRequest;
use App\Http\Requests\Account\RolesFilterRequest;
use App\Http\Requests\Account\UpdateAccountRequest;
use App\Http\Requests\Employee\AssignAcctoEmpRequest;
use App\Http\Requests\Employee\CreateEmployeeRequest;
use App\Http\Requests\Employee\RequiredAccountIdRequest;
use App\Http\Requests\Employee\UpdateEmployeeRequest;
use App\Http\Requests\Employee\UpdateEmployeePassword;
use Illuminate\Support\Str;
use App\Models\Account;
use App\Models\Employee;
use App\Services\AdminService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Traits\TranslateFields;
use Illuminate\Support\Facades\Crypt;
use App\Traits\FirebaseNotificationTrait;
use App\Enums\Roles;
use App\Http\Requests\Employee\RequiredEmployeeIdRequest as EmployeeRequiredEmployeeIdRequest;
use App\Http\Requests\FilterRequest;
use App\Traits\CloudinaryTrait;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    use  CloudinaryTrait, TranslateFields, FirebaseNotificationTrait;

    public function __construct(protected AdminService $adminService) {}

    public function getUnassignedAccounts() //si
    {
        try {
            if (auth('api-employees')->user()->hasRole(Roles::MAIN_ADMIN)) {
                $accountsWithoutLink = Account::leftJoin('employees', 'accounts.id', '=', 'employees.account_id')
                    ->select([
                        'accounts.email',
                        'accounts.id'
                    ])
                    ->whereNull('employees.account_id')
                    ->with(['roles' => function ($query) {
                        $query->select('roles.id', 'name');
                    }])->get();
                return response()->success($accountsWithoutLink, 200);
            }
            else{
                return response()->json(['error' => 'Unauthorized'], 403);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred, please try again.'], 500);
        }
    }

    public function getAccounts(RolesFilterRequest $request) //si
    {
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

            $roles_and_accounts->each(function ($item) {

                $item->makeHidden(['created_at', 'deleted_at', 'updated_at', 'password']);
            });
            return response()->success($roles_and_accounts, 200);
        } else {
            $roles_and_accounts = Account::with('roles:name')->withCount('roles')->latest('created_at')->paginate(10);
            $roles_and_accounts->each(function ($item) {
                $item->makeHidden(['created_at', 'deleted_at', 'updated_at', 'password']);
            });
            return response()->success($roles_and_accounts, 200);
        }
    }

    public function showUnLinkedEmps() //si
    {
        $emps = $this->adminService->displayUnlinkedEmps();
        return response()->success($emps, 200);
    }

    public function createAccount(CreateAccountRequest $request) //si
    {
        try {
                $account = $this->adminService->createAccount($request->validated());
                return response()->json(['created_account' => $account, 'message' => 'Account has been created successfully'], 201);
            } 
        catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while creating the account.'], 500);
        }
    }

    public function updateAccount(UpdateAccountRequest $request) //si
    {
        $account = $this->adminService->updateAccount($request->validated());
        return response()->success(['updated_account' => $account, 'message' => 'Account has been updated successfully'], 200);
    }

    public function deleteAccount(Request $request) //si
    {
        $validatedData = $request->validate([
            'account_id' => ['required', 'integer', 'exists:accounts,id'],
        ]);
        $account = Account::findOrFail($validatedData['account_id']);
        $this->adminService->deleteAccount($account);
        return response()->success('Account has been deleted successfully', 200);
    }

    public function assignAcctoEmp(AssignAcctoEmpRequest $request) //si
    {
        $response_data = $this->adminService->assignAccToEmp($request);
        if ($response_data instanceof JsonResponse) {
            return $response_data;
        } elseif ($response_data instanceof Employee) {
            $this->adminService->createAccDuration($response_data);
            return response()->success([$response_data->account, 'message' => 'Account has been linked to this employee successfully'], 200);
        }

        return response()->success('Account has been linked to this employee successfully', 200);
    }

    public function unassignAcc(RequiredAccountIdRequest $request) //si
    {
        $response = $this->adminService->unassignAcc($request->validated('account_id'));

        if ($response) {
            return $response;
        } else
            return response()->success('Account has been unlinked from this employee successfully', 200);
    }

    public function createEmp(CreateEmployeeRequest $request) //si
    {
        try {
            $emp_data = $request->validated();
            $employee = $this->adminService->createEmp($emp_data);
            $this->adminService->createAccDuration($employee);

            return response()->success(['created_employee' => $employee, 'message' => 'Employee has been created successfully'], 201);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function updateEmp(UpdateEmployeeRequest $request) //si
    {
        $employee = $this->adminService->updateEmp($request->validated());
        return response()->success(['updated_employee' => $employee, 'message' => 'Employee has been updated successfully'], 200);
    }

    public function displayEmps() //si
    {
        $employees = $this->adminService->displayEmps();
        return response()->success($employees, 200);
    }

    public function deliveryAdminDetails(FilterRequest $request) //si
    {
        $validatedData = $request->validate([
            'employee_id' => ['required', 'integer', 'exists:employees,id'],
        ]);
        $employee_id = $validatedData['employee_id'];

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

        $delivery_admin = Employee::findOrFail($employee_id)->load(['account', 'orders.user'])->loadCount('orders');

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
        }else{
            return response()->error('Wrong Employee', 500); 
        }
    }

    public function deliveryAdmin(Request $request) //si
    {
        $validatedData = $request->validate([
            'employee_id' => ['required', 'integer', 'exists:employees,id'],
        ]);
        $employee_id = $validatedData['employee_id'];
        $delivery_admin = Employee::findOrFail($employee_id)->loadCount('orders');

        if ($delivery_admin->account->roles->first()->id == 5) {
            return response()->success($delivery_admin->only(['first_name', 'last_name', 'email', 'phone', 'created_at', 'orders_count']), 200);
        }else{
            return response()->error('Wrong Employee', 500); 
        }
    }

    public function revealPassword(RequiredAccountIdRequest $request) //si
    {
        $account_to_reveal = Account::findOrFail($request->validated('account_id'));
        if($account_to_reveal->password){
            $revealed_password = Crypt::decryptString($account_to_reveal->password);
        }
        else{
            $revealed_password = null;    
        }
        return response()->success(['revealed_password' => $revealed_password], 200);
    }

    public function revealPasswordEmp(RequiredEmployeeIdRequest $request) //si
    {
        $employee = Employee::findOrFail($request->validated('employee_id'));
        $password = Crypt::decryptString($employee->password);
        return response()->success(['revealed_password' => $password], 200);
    }

    public function getCurrentEmp(RequiredAccountIdRequest $request) //si
    {
        $current_emp = $this->adminService->currentEmp($request->validated('account_id'));

        if ($current_emp instanceof JsonResponse) {
            return $current_emp;
        } else
            return response()->success(['current_employee' => $current_emp], 200);
    }

    public function getLastEmps(RequiredAccountIdRequest $request) //si
    {
        $last_emps = $this->adminService->lastEmps($request->account_id);
        return response()->success($last_emps, 200);
    }

    public function updateEmpPass(UpdateEmployeePassword $request) //si
    {
        $employee_id = $request->employee_id;
        $password = Crypt::encryptString($request->password);
        Employee::find($employee_id)->update(['password' => $password]);
        return response()->success('Password changed successfully', 200);
    }
}
