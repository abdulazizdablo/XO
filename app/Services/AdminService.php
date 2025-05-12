<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Employee;
use App\Models\Shift;
use App\Models\Inventory;
use App\Models\Role;
use App\Models\AssignDuration;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use App\Utils\PaginateCollection;
use Exception;

class AdminService
{
    public function __construct(protected PaginateCollection $paginate_collection) {}

    public function createAccount(array $account_data) //si
    {
        $account = Account::create([
            'email' => $account_data['email'],
        ]);
        $role = Role::where('name', 'LIKE', $account_data['role'])->firstOrFail();
        $account->roles()->attach($role);
        return $account->makeHidden(['created_at', 'updated_at', /*'password',*/ 'deleted_at'])->load('roles:name');
    }

    public function updateAccount($account_data) //si
    {
        $account = Account::findOrFail($account_data['account_id']);
        if (isset($account_data['password'])) {

            $account->update(array_merge($account_data, ['password' => Crypt::encryptString($account_data['password'])]));
        } else {
            $account->update($account_data);
        }
        return $account->makeHidden(['created_at', 'deleted_at', 'updated_at', 'password']);
    }

    public function deleteAccount(Account $account) //si
    {
        try {
            $account->deleted_at = now();
            $account->save();
        } catch (\Exception $e) {
            return response()->error($e->getMessage(), 404);
        }
    }

    public function assignAccToEmp($data) //si
    {
        $employee = Employee::findOrFail($data->employee_id)->load('account');
        $reponse = new JsonResponse();
        $account = Account::findOrFail($data->account_id);
        $acc = $account->roles()->first();
        
        if (!$acc) {
            throw new Exception('Account does not exist', 422);
        }

        $role = $acc->name;
        $exists = Employee::where('account_id', $data->account_id)->exists();

        if ($exists) {
            throw new Exception('Account already in use', 422);
        }

        if ($role == 'delivery_boy') {
            if (!isset($data->shift_id)) {
                throw new Exception('employee shift is required', 422);
            } else {
                $shift_ids = Shift::get()->pluck('id')->toArray();
                if (!in_array($data->shift_id, $shift_ids)) {
                    throw new Exception('shift does not exist', 422);
                }
            }
            if (!isset($data->inventory_id)) {
                throw new Exception('employee inventory is required', 422);
            } else {
                $inventory_ids = Inventory::get()->pluck('id')->toArray();
                if (!in_array($data->inventory_id, $inventory_ids)) {
                    throw new Exception('Inventory does not exist', 422);
                }
            }
        }

        if ($role == 'warehouse_manager') {
            if (!isset($data->inventory_id)) {
                throw new Exception('employee inventory is required', 422);
            } else {
                $inventory_ids = Inventory::get()->pluck('id')->toArray();
                if (!in_array($data->inventory_id, $inventory_ids)) {
                    throw new Exception('Inventory does not exist', 422);
                }
            }
        }
        if ($employee->account()->exists()) {
            $reponse = response()->error('There is already linked employee to this account', 400);
            return $reponse;
        } else if ($employee->trashed()) {
            $reponse = response()->error('the Employee has been deleted', 400);
            return $reponse;
        } else {
            $employee->account()->associate($data->account_id);
            $employee->shift_id = $data->shift_id;
            $employee->inventory_id = $data->inventory_id;
            $employee->save();
            return $employee;
        }

        $check_employee = Employee::where('account_id', $account->id)->first();
       
        if ($check_employee) {

            $this->unAssignAcc($account);

            $employee->account()->associate($account_id);
            $employee->save();
            return $employee;
        };

        $check_employee = Employee::where('account_id', $account->id)->first();
        if ($check_employee) {

            $this->unAssignAcc($account);

            $employee->account()->associate($account_id);
            $employee->save();
            return $employee;
        };
        return $reponse;
    }

    public function unAssignAcc($account_id)//si
    {
        $employee = Employee::where('account_id', $account_id)->first();
        
        if (!$employee) {
            return response()->error(['There isnt employee with this account'], 404);
        }

        if (!$employee->account->exists()) {
            return response()->error("There isn't any linked account to this employee", 401);
        }

        $account = $employee->account;
        $employee->update([
            'shift_id' => null,
            'inventory_id' => null
        ]);
        $employee->unassignAccount($account);
        return response()->success([$account->makeHidden('isLinked'), 'message' => 'Account has been unlinked successfully'], 200);
    }

    public function createAccDuration(Employee $employee)//si
    {
        AssignDuration::create([
            'employee_id' => $employee->id,
            'account_id' => $employee->account->id,
            'assign_from' => now(),
            'assign_to' => null
        ]);
    }

    public function displayUnlinkedEmps()//si
    {
        $employees = Employee::doesntHave('account')->get();
        $employees->each(function ($item) {
            $item->makeHidden(['created_at', 'deleted_at', 'updated_at', 'password']);
        });
        return $employees;
    }

    public function displayEmps()//si
    {
        $employees = Employee::with([
            'account' => function ($q) {
                $q->select('id', 'email'); 
            },
            'account.roles' => function ($q) {
                $q->select('name',); 
            }
        ])->latest('created_at')->paginate(10);

        return $employees;
    }

    public function createEmp(array $emp_data)//si
    {
        $acc = Account::find($emp_data['account_id'])->roles()->first();

        if (!$acc) {
            throw new Exception('Account does not exist', 422);
        }

        $role = $acc->name;
        $exists = Employee::where('account_id', $emp_data['account_id'])->exists();
        
        if ($exists) {
            throw new Exception('Account already in use', 422);
        }

        if ($role == 'delivery_boy') {
            if (!isset($emp_data['shift_id'])) {
                throw new Exception('employee shift is required', 422);
            } else {
                $shift_ids = Shift::get()->pluck('id')->toArray();
                if (!in_array($emp_data['shift_id'], $shift_ids)) {
                    throw new Exception('shift does not exist', 422);
                }
            }
            if (!isset($emp_data['inventory_id'])) {
                throw new Exception('employee inventory is required', 422);
            } else {
                $inventory_ids = Inventory::get()->pluck('id')->toArray();
                if (!in_array($emp_data['inventory_id'], $inventory_ids)) {
                    throw new Exception('Inventory does not exist', 422);
                }
            }
        }
        
        if ($role == 'warehouse_manager') {
            if (!isset($emp_data['inventory_id'])) {
                throw new Exception('employee inventory is required', 422);
            } else {
                $inventory_ids = Inventory::get()->pluck('id')->toArray();
                if (!in_array($emp_data['inventory_id'], $inventory_ids)) {
                    throw new Exception('Inventory does not exist', 422);
                }
            }
        }

        $password = Crypt::encryptString($emp_data['password']);
        $employee = Employee::create(array_merge($emp_data, ['password' => $password]));
        $employee->account()->associate($emp_data['account_id']);
        return $employee->makeHidden(['created_at', 'deleted_at', 'updated_at', 'password']);
    }


    public function updateEmp(array $emp_data)//si
    {
        $employee = Employee::findOrFail($emp_data['employee_id']);
        if (isset($emp_data['password'])) {
            $password = Crypt::encryptString($emp_data['password']);
            $employee->update(array_merge($emp_data, ['password' => $password]));
        } else {
            $employee->update($emp_data);
        }
        return $employee->makeHidden(['created_at', 'deleted_at', 'updated_at', 'password']);
    }



    public function currentEmp(int $account_id)//si
    {
        $account = Account::findOrFail($account_id)->load('assign_durations');
        $account_history = $account->assign_durations;
        $current_account = $account_history->whereNull('assign_to');
        $response = 0;

        if ($current_account->isEmpty()) {
            $response = response()->success([], 200);
        } else {
            $current_account = $current_account->pluck('employee_id')[0];
            $current_employee = Employee::findOrFail($current_account)->load(['assign_durations', 'account.roles']);
            $is_delivery_admin = false;
            if ($account->roles()->first()->id == 5) {
                $is_delivery_admin = !$is_delivery_admin;
            }

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


    public function lastEmps(int $account_id)//si
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
        foreach ($accountHistory as $single_history) {
            $single_history->isDeleivery_Admin = $is_delivery_admin;
        }
        return $accountHistory;
    }

    public function applyFilters($query, array $filters)//si
    {
        $appliedFilters = [];
        foreach ($filters as $attribute => $value) {
            $column_name = Str::before($attribute, '_');
            $method = 'filterBy' . Str::studly($column_name);
            if (method_exists($this, $method) && isset($value) && !in_array($column_name, $appliedFilters)) {
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

    public function applySort($query, array $sort_data)//si
    {
        if ($sort_data['sort_key'] == '' && $sort_data['sort_value'] == '') {
            return $query;
        }

        return $query->orderBy($sort_data['sort_key'], $sort_data['sort_value']);
    }
}
