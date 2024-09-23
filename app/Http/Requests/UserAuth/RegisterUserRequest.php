<?php

namespace App\Http\Requests\UserAuth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;

class RegisterUserRequest extends FormRequest
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
                'phone' => 'required|unique:users|regex:/^09\\d{8}$/',
                'email' => 'email|unique:users|max:255',
                'password' => 'required|string|min:8|max:255',
        ];
    }

    //protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    //{
	//	return response()->json($validator->errors(), 422);
    //}
}
