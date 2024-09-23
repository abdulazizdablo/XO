<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Employee;
use App\Models\Role;
use App\Models\AssignDuration;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use App\Utils\PaginateCollection;
use Illuminate\Database\Eloquent\ModelNotFoundException;


class AdminService
{


    public function __construct(protected PaginateCollection $paginate_collection)
    {
    }


    public function getAccountsAndRoles(?string $role_filter)
    {

        $accounts_and_roles_data = [];

        if (!is_null($role_filter)) {


            $roles_and_accounts = Account::with('roles:name')->withCount('roles')->whereHas('roles', function ($query) use ($role_filter) {


                $query->where('name', 'LIKE', $role_filter);
            })->latest('created_at');



            $accounts = new Collection();
            $roles = new Collection();
            $roles_and_accounts->each(function ($item, $key) use ($accounts, $roles) {

                $role_name = $item->roles->pluck('name')->first();

                $roles[$role_name] = $item->roles_count;
                $accounts[] = $item->only(['id', 'email', 'password', $role_name => 'roles']);
            });
            $roles['count_all'] = $roles->sum();

            $accounts_and_roles_data['accounts'] = $accounts;
            $accounts_and_roles_data['roles_count'] = $roles;

            return $accounts_and_roles_data;
        }

        $roles_and_accounts = Account::with('roles:name')->withCount('roles')->get();
        $accounts = new Collection();
        $roles = new Collection();
        $roles_and_accounts->each(function ($item, $key) use ($accounts, $roles) {

            $role_name = $item->roles->pluck('name')->first();

            $roles[$role_name] = $item->roles_count;
            $accounts[] = $item->only(['id', 'email', 'password', $role_name => 'roles']);
        });
        $roles['count_all'] = $roles->sum();
        $accounts_and_roles_data['accounts'] = $accounts;
        $accounts_and_roles_data['roles_count'] = $roles;

        return $accounts_and_roles_data;
    }

    public function createAccount(array $account_data)
    {
		


        $account = Account::create([
            'email' => $account_data['email'],
            'password' => Crypt::encryptString($account_data['password'])
        ]);


        $role = Role::where('name', 'LIKE', $account_data['role'])->firstOrFail();
        // $role = Role::findOrFail($account_data['role_id']);

        $account->roles()->attach($role);

        //  $account_response['account'] = $account->except('password');
        return $account->makeHidden(['created_at', 'updated_at', 'password', 'deleted_at'])->load('roles:name');
    }


    public function showEmps()
    {

        //get the current assign employee to the account

        $current_emp = Employee::whereHas('assign_durations', function ($q) {

            $q->whereNull('assign_to');
        })->get();


        $last_emps = Employee::whereHas('assign_durations', function ($q) {

            $q->whereNotNull('assign_to');
        })->latest();
    }


    public function updateAccount($account_data)
    {

        $account = Account::findOrFail($account_data['account_id']);
        if (isset ($account_data['password'])) {

            $account->update(array_merge($account_data, ['password' => Crypt::encryptString($account_data['password'])]));
        } else {
            $account->update($account_data);
        }

        return $account->makeHidden(['created_at', 'deleted_at', 'updated_at', 'password']);
    }
    public function deleteAccount(Account $account)
    {

        try {






            $account->deleted_at = now();
            $account->save();
        } catch (\Exception $e) {

            return response()->error($e->getMessage(), 404);

        }
    }

    public function forceDeleteAccount(Account $account)
    {

        $account->delete();
    }
    public function assignAccToEmp(int $account_id, int $employee_id)
    {
        $employee = Employee::findOrFail($employee_id)->load('account');



        $reponse = new JsonResponse();

        $account = Account::findOrFail($account_id);

	//	dd($employee->account()->exists());
		
			if ($employee->account()->exists()) {

            $reponse = response()->error('There is already linked employee to this account', 400);
				return $reponse;
        } else if ($employee->trashed()) {


            $reponse = response()->error('the Employee has been deleted', 400);
				return $reponse;
        } else {
            $employee->account()->associate($account_id);
            $employee->save();

            return $employee;
        }
		
		
		
		
		   $check_employee = Employee::where('account_id', $account->id)->first();
        if ($check_employee) {

            $this->unAssignAcc($account);

            $employee->account()->associate($account_id);
            $employee->save();
            return $employee;
        }
        ;

		
		


	
		
		
		
     
		
		
		   $check_employee = Employee::where('account_id', $account->id)->first();
        if ($check_employee) {

            $this->unAssignAcc($account);

            $employee->account()->associate($account_id);
            $employee->save();
            return $employee;
        }
        ;

		
		

        

        return $reponse;
    }




    public function unAssignAcc($account_id)
    {

        $employee = Employee::where('account_id', $account_id)->first();


        if (!$employee) {


            return response()->error(['There isnt employee with this account'], 404);
        }


        if (!$employee->account->exists()) {

            return response()->error("There isn't any linked account to this employee", 401);
        }

        $account = $employee->account;

        $employee->unassignAccount($account);

        //make the period finished when unassign the account
  

    

        return response()->success([$account->makeHidden('isLinked'), 'message' => 'Account has been unlinked successfully'], 200);
    }



    public function createAccDuration(Employee $employee)
    {
        AssignDuration::create([
            'employee_id' => $employee->id,
            'account_id' => $employee->account->id,
            'assign_from' => now(),
            'assign_to' => null
        ]);
    }


    public function assignAccToRole($account, $roles)
    {
        $account = Account::findOrFail($account);
        $account->roles()->attach($roles);
    }

    public function reAssignAccToRole(Account $account, $role_collection)
    {

        $account->roles()->sync($role_collection);
    }


    public function displayUnlinkedEmps()
    {

        $employees = Employee::doesntHave('account')->get();

        $employees->each(function ($item) {


            $item->makeHidden(['created_at', 'deleted_at', 'updated_at', 'password']);
        });


        return $employees;
    }
    public function displayEmps()
    {
        $employees = Employee::with([
            'account' => function ($q) {
                // Select the specific attributes from the account table
                $q->select('id', 'email'); // Add the attributes you need
    

            },
            'account.roles' => function ($q) {
                // Select the relevant columns from the roles table
                $q->select('name', ); // Add the columns you need
    
                // Access the roles for the account
                // Add any additional conditions if needed
            }
        ])->latest('created_at')->paginate(10);

        /* $employees =  Employee::with(['account.roles' => function($q){



$q->select('name');


        }])->latest('created_at')->paginate(10);

        $employees->each(function ($item) {

            $item->makeHidden(['created_at', 'deleted_at', 'password', 'updated_at',]);
            $item->account ? $item->account->only('email') : null;
        });*/

        return $employees;
    }

    public function createEmp(array $emp_data)
    {

        $password = Crypt::encryptString($emp_data['password']);


        $employee = Employee::create(array_merge($emp_data, ['password' => $password]));


        return $employee->makeHidden(['created_at', 'deleted_at', 'updated_at', 'password']);
    }


    public function updateEmp(array $emp_data)
    {
        $employee = Employee::findOrFail($emp_data['employee_id']);


        if (isset ($emp_data['password'])) {



            $password = Crypt::encryptString($emp_data['password']);

            $employee->update(array_merge($emp_data, ['password' => $password]));
        } else {
            $employee->update($emp_data);
        }


        return $employee->makeHidden(['created_at', 'deleted_at', 'updated_at', 'password']);
    }



    public function currentEmp(int $account_id)
    {

        //  $employees_data = [];

        //  $employees_data = [];

        $account = Account::findOrFail($account_id)->load('assign_durations');

        $account_history = $account->assign_durations;
        $current_account = $account_history->whereNull('assign_to');




        $response = 0;

        if ($current_account->isEmpty()) {


            $response = response()->success([], 200);
        } else {


            $current_account = $current_account->pluck('employee_id')[0];

            //dd($current_account);
            $current_employee = Employee::findOrFail($current_account)->load(['assign_durations', 'account.roles']);

            $is_delivery_admin = false;

            if ($account->roles()->first()->id == 5) {
                $is_delivery_admin = !$is_delivery_admin;
            }
            //dd($current_employee->assign_durations->first());


            //get last employees

            /* $employee_ids = [];

        foreach ($account_history as $single_account_history) {

            $employee_ids[] = $single_account_history->employee_id;
        };


        $last_employees = Employee::findOrFail($employee_ids);*/

            //  $employees_data['current_employee'] = $current_employee;

            // $employees_data['last_employees'] =  $last_employees;


            // dd($current_employee->account?->first());
            $response = [
                'id' => $current_employee->id,
                'account_id' => $current_employee->account_id,
                'first_name' => $current_employee->first_name,
                'last_name' => $current_employee->last_name,
                'phone' => $current_employee->phone,
                'email' => $current_employee->email,
                'start_date' => $current_employee->assign_durations->first()->assign_from->format('Y-m-d H:i:s'),
                'end_date' => 'current',
                'isDeleivery_Admin' => $is_delivery_admin
            ];
        }
        return $response;
    }


    public function lastEmps(int $account_id)
    {
        $account = Account::findOrFail($account_id)->load('assign_durations');
        $is_delivery_admin = false;

        if ($account->roles()->first()->id == 5) {
            $is_delivery_admin = !$is_delivery_admin;
        }

		$accountHistory = AssignDuration::where('assign_durations.account_id', $account->id)
			->where('assign_durations.assign_to', '<>', null) // Exclude null assign_to
			->leftJoin('employees', 'assign_durations.employee_id', '=', 'employees.id')
			->select([
				'assign_durations.assign_from as start_date',
				'assign_durations.assign_to as end_date',
				'employees.id as id',
				'employees.first_name as first_name',
				'employees.last_name as last_name',
				'employees.email as email',
				'employees.phone as phone'
			])->paginate(10);
		foreach ($accountHistory as $single_history){
			$single_history->isDeleivery_Admin = $is_delivery_admin;
		}
		return $accountHistory;
        /*$account_history = $account->assign_durations;
        $employee_ids = [];

        foreach ($account_history as $single_account_history) {
            $employee_ids[] = $single_account_history->employee_id;
        }

        $last_employees = Employee::findOrFail($employee_ids)->load('assign_durations');

        $last_emps = $last_employees->reject(function ($item) use ($account_id) {

            return $item->assign_durations->where('account_id', $account_id)->whereNull('assign_to')->get('id');
        })->map(function ($item) use ($account_id, $is_delivery_admin) {


            return [
                'id' => $item->id,
                'first_name' => $item->first_name,
                'last_name' => $item->last_name,
                'email' => $item->email,
                'phone' => $item->phone,
                'start_date' => $item->assign_durations->where('account_id', $account_id)->max('assign_from')->format('Y-m-d H:i:s'),
                'end_date' => $item->assign_durations->where('account_id', $account_id)->max('assign_to')->format('Y-m-d H:i:s'),
                'isDeleivery_Admin' => $is_delivery_admin, // corrected variable name
            ];
        })->values()->values();

        $collection = collect($last_emps);
        return $this->paginate_collection::paginate($collection, 10);*/
    }





    public function applyFilters($query, array $filters)
    {
        $appliedFilters = [];
        foreach ($filters as $attribute => $value) {
            $column_name = Str::before($attribute, '_');
            $method = 'filterBy' . Str::studly($column_name);
            if (method_exists($this, $method) && isset ($value) && !in_array($column_name, $appliedFilters)) {
                $query = $this->{$method}($query, $filters);
                $appliedFilters[] = $column_name;
            }
        }

        return $query;
    }

    public function filterByInvoice($query, $filter_data)
    {
        return $query->where('invoice_number', 'LIKE', '%' . $filter_data['invoice_number'] . '%');
    }

    public function filterByQuantity($query, $filter_data)
    {
        return $query->where('total_quantity', $filter_data['quantity']);
    }

    public function filterByStatus($query, $filter_data)
    {
        return $query->where('status', $filter_data['status']);
    }

    public function filterByPricing($query, $filter_data)
    {
        return $query->whereBetween('total_price', [$filter_data['pricing_min'], $filter_data['pricing_max']]);
    }



    public function filterByDate($query, $filter_data)
    {
        $date_min = $filter_data['date_min'] ?? 0;
        $date_max = $filter_data['date_max'] ?? date('Y-m-d');

        return $query->whereBetween('created_at', [$date_min, $date_max]);
    }

    public function filterByDelivery($query, $filter_data)
    {
        $delivery_min = $filter_data['delivery_min'] ?? 0;
        $delivery_max = $filter_data['delivery_max'] ?? date('Y-m-d');

        return $query->whereBetween('created_at', [$delivery_min, $delivery_max]);
    }

    public function filterByInventory($query, $filter_data)
    {
        return $query->where('inventory_id', $filter_data['inventory']);
        // return $query->get();

    }
    public function filterBySearch($query, $filter_data)
    {
        // return $query->whereLike('shipment_name', $filter_data['search']);
        $search = $filter_data['search'];
        // dd($search);
        return $query->where('invoice_number', 'LIKE', '%' . $search . '%')
            ->orWhereHas('user', function ($query) use ($search) {
                $query->Where('first_name', 'LIKE', '%' . $search . '%')
                    ->orWhere('last_name', 'LIKE', '%' . $search . '%')
                    ->orWhere('phone', 'LIKE', '%' . $search . '%');
            });
    }
    // Sort
    public function applySort($query, array $sort_data)
    {

        if ($sort_data['sort_key'] == '' && $sort_data['sort_value'] == '') {

            return $query;
        }
        return $query->orderBy($sort_data['sort_key'], $sort_data['sort_value']);
    }
}
