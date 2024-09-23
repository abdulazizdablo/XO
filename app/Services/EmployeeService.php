<?php

namespace App\Services;

use App\Models\Employee;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use InvalidArgumentException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;
use Str;
use App\Enums\Roles;


class EmployeeService
{
    public function getAllEmployees()
    {
        $employees = Employee::paginate();

        if (!$employees) {
            throw new InvalidArgumentException('There Is No Employees Available');
        }

        return $employees;
    }

    public function getEmployee(int $employee_id): Employee
    {
        $employee = Employee::findOrFail($employee_id);


        return $employee;
    }

    public function createEmployee(array $data, int $inventory_id, int $shift_id): Employee
    {
        $role_id = $data['role_id'];

        $employee = Employee::create([
            'inventory_id' => $inventory_id,
            'shift_id' => $shift_id,
            'last_name' => $data['last_name'],
            'first_name' => $data['first_name'],
            'email' => $data['email'],
            'password' => Crypt::encryptString($data['password']),
            'address' => $data['address'],
            'phone' => $data['phone'],
            // 'role' => $data['role'],
        ]);
        $role = Role::findOrFail($role_id);

        $name = $role->name;
        if ($name == 'main-admin') {
            $employee->assignRole('main-admin');
        }
        if ($name == 'inventory-admin') {
            $employee->assignRole('inventory-admin');
        }
        if ($name == 'order-admin') {
            $employee->assignRole('order-admin');
        }
        if ($name == 'delivery-man') {
            $employee->assignRole('delivery-man');
        }
        if ($name == 'delivery-boy') {
            $employee->assignRole('delivery-boy');
        }
        if ($name == 'data-entry') {
            $employee->assignRole('data-entry');
        }


        if (!$employee) {
            throw new InvalidArgumentException('Something Wrong Happend');
        }

        return $employee;
    }

    public function updateEmployee(array $data, int $employee_id, int $inventory_id, int $shift_id): Employee
    {
        $employee = Employee::findOrFail($employee_id);
       
        $employee->update([
            'inventory_id' => $inventory_id,
            'shift_id' => $shift_id,
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Crypt::encryptString($data['password']),
            'address' => $data['address'],
            'phone' => $data['phone'],
        ]);

        return $employee;
    }
    public function createToken(int $employee_id)
    {
$employee = Employee::findOrFail($employee_id);


    }
    public function show(int $employee_id): Employee
    {
        $employee = Employee::findOrFail($employee_id);

        return $employee;
    }

    public function delete(int $employee_id): void
    {
        $employee = Employee::findOrFail($employee_id);

        $employee->delete();
    }

    public function forceDelete(int $employee_id): void
    {
        $employee = Employee::findOrFail($employee_id);

        $employee->forceDelete();
    }

    public function loginEmployee(array $data)
    {
        $email = $data['email'];
        $password = $data['password'];

        $employee = Employee::where('email', $email)->firstOrFail();


        if ($employee != null) {
            $account = $employee->account;

            if (!$account) {
                throw new Exception('You do not have an account');
            }
            $role = $account->roles;

            if (!$role) {
                throw new Exception('You do not have a role');
            }

            $employee_password = Crypt::decryptString($employee->password);

            if ($password == $employee_password) {

                if ($employee->hasRole(Roles::MAIN_ADMIN)) {
                    $token = $employee->createToken('authToken', ['main-admin'])->plainTextToken;
                   // $refresh_token =hash('sha256', Str::random(40));
                   // $token->refresh_token =  $refresh_token;
                   // $token->save();
                    $response = [
                        'employee' => $employee,
                        'token' => 'admin_' . $token,
                        //'refresh_token' =>  'admin_' . $refresh_token,
                        'message' => 'Main-admin logged in successfully'
                    ];
                    return response($response, 200);
                } elseif ($employee->hasRole(Roles::WAREHOUSE_ADMIN)) {
                    $token = $employee->createToken('authToken', ['warehouse-admin'])->plainTextToken;
                   // $refresh_token =hash('sha256', Str::random(40));
                   // $token->refresh_token =  $refresh_token;
                  //  $token->save();
                    $response = [
                        'employee' => $employee,
                        'token' => 'ware_' . $token,
                        //'refresh_token' =>  'ware_' . $refresh_token,
                        'message' => 'Warehouse-admin logged in successfully'
                    ];
                    return response($response, 200);
                } elseif ($employee->hasRole(Roles::DELIVERY_ADMIN)) {
                    $token = $employee->createToken('authToken', ['delivery-admin'])->plainTextToken;
                   // $refresh_token =hash('sha256', Str::random(40));
                   // $token->refresh_token =  $refresh_token;
                   // $token->save();
				
                    $response = [
                        'employee' => $employee,
                        'token' => 'del_' . $token,
                       // 'refresh_token' =>  'del_' . $refresh_token,
                        'message' => 'Delivery-admin logged in successfully'
                    ];
                    return response($response, 200);
                } elseif ($employee->hasRole(Roles::DELIVERY_BOY)) {
                    $token = $employee->createToken('authToken', ['delivery-boy'])->plainTextToken;
                   // $refresh_token =hash('sha256', Str::random(40));
                    //$token->refresh_token =  $refresh_token;
                   // $token->save();
					
                    $response = [
                        'employee' => $employee,
                        'token' => 'boy_' . $token,
                        //'refresh_token' =>  'boy_' . $refresh_token,
                        'message' => 'Delivery-Boy logged in successfully'
                    ];
                    return response($response, 200);
                } elseif ($employee->hasRole(Roles::WAREHOUSE_MANAGER)) {
                    $token = $employee->createToken('authToken', ['warehouse-manager'])->plainTextToken;
                   // $refresh_token =hash('sha256', Str::random(40));
                    //$token->refresh_token =  $refresh_token;
                    //$token->save();
                    $response = [
                        'employee' => $employee,
                        'token' => 'mtg_' . $token,
                       // 'refresh_token' =>  'mtg_' . $refresh_token,
                        'message' => 'Warehouse-Manager logged in successfully'
                    ];
                    return response($response, 200);
                } elseif ($employee->hasRole(Roles::DATA_ENTRY)) {
                    $token = $employee->createToken('authToken', ['data-entry'])->plainTextToken;
                  //  $refresh_token =hash('sha256', Str::random(40));
                   // $token->refresh_token =  $refresh_token;
                   // $token->save();
                    $response = [
                        'employee' => $employee,
                        'token' => 'data_' . $token,
                        //'refresh_token' =>  'data_' . $refresh_token,
                        'message' => 'Data-entry logged in successfully'
                    ];
                    return response($response, 200);
                } elseif ($employee->hasRole(Roles::OPERATION_MANAGER)) {
                    $token = $employee->createToken('authToken', ['operation-manager'])->plainTextToken;
                    //$refresh_token =hash('sha256', Str::random(40));
                    //$token->refresh_token =  $refresh_token;
                    //$token->save();
                    $response = [
                        'employee' => $employee,
                        'token' => 'op_' . $token,
                        //'refresh_token' =>  'op_' . $refresh_token,
                        'message' => 'Operation-Manager logged in successfully'
                    ];
                    return response($response, 200);
                }
            } else {
                throw new Exception('Sorry ,You entered a wrong password', 400);
            }
        } else {
            throw new Exception('This User Email is not exist', 400);
        }
    }


    public function revealPassword(int $employee_id)
    {
        $employee = Employee::findOrFail($employee_id);
    
        $employee_password = Crypt::decryptString($employee->password);
        return $employee;
    }



    public function Changerole(int $employee_id, int $role_id)
    {
        // return $roles=Role::all();
        $employee = Employee::findOrFail($employee_id);
        // return $employee;
        $role = Role::findOrFail($role_id);
        // return $role ;
        if (!$employee) {
            throw new InvalidArgumentException('There Is No Employees Available');
        }
        //     $employee_role= $employee->getRoleNames();
        // //   return $employee_role ;
        //     $employee->removeRole($employee_role->name);

        $name = $role->name;
        // return $name;
        // $employee->assignRole($name);
        $employee->syncRoles([$name]);

        return $employee;
    }


    public function getAllRoles()
    {
        $roles = Role::all();

        if (!$roles) {
            throw new InvalidArgumentException('roles not found');
        }

        return $roles;
    }
}
