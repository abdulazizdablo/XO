<?php

namespace App\Http\Requests\Employee;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmployeeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $employee_id = $this->input('employee_id');

        return [
            'employee_id' => 'required|exists:employees,id',
            'first_name' => 'sometimes|string|max:255',
            'last_name' => 'sometimes|string|max:255',
            'email' => [
                'sometimes',
                'email',
                'max:50',
                Rule::unique('employees', 'email')->ignore($employee_id),
            ],
            'phone' => 'sometimes|string|unique:employees,phone',
            'password' => 'sometimes|string|confirmed|regex:/^[^\s]+$/',
            'shift_id' => 'sometimes|exists:shifts,id',
            'inventory_id' => 'sometimes|exists:inventories,id'
        ];
    }
}
