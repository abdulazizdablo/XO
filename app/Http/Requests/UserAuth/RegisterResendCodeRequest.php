<?php

namespace App\Http\Requests\UserAuth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;

class RegisterResendCodeRequest extends FormRequest
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
                'phone' => 'required|exists:users,phone|regex:/^09\\d{8}$/',
        ];
    }

	//    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    //{
	//	return response()->json($validator->errors(), 422);
    //}
}
