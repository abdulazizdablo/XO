<?php

namespace App\Http\Requests\UserAuth;

use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
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
            'phone' => 'required|string|exists:users,phone',
            'password' => 'required|confirmed|min:8',
            'verification_code' => 'required|digits:4'
        ];
    }
/*
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
		return response()->json($validator->errors(), 422);
    }
	*/
}
