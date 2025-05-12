<?php

namespace App\Http\Requests\Employee;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateEmployeeRequest extends FormRequest
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
        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:employees,email',
            'phone' => 'required|string|regex:/^09\d{8}$/',
            'password' => 'required|string|confirmed|regex:/^[^\s]+$/',
			'account_id' => 'required|exists:accounts,id',
            //'role_name' => 'required|exists:roles,name',
            'shift_id' => 'sometimes|exists:shifts,id',            
            'inventory_id' => 'sometimes|exists:inventories,id',
        ];
    }
	
	/*protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new \Illuminate\Validation\ValidationException($validator, response()->json([
            'success' => false,
            'error' => [
                'message' => $validator->errors()->first(),
            ],
        ], 422));
    }*/
}
