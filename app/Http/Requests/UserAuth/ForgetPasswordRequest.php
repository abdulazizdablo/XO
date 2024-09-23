<?php

namespace App\Http\Requests\UserAuth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
class ForgetPasswordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            //'email' => 'required|email',
            'phone' => 'required|max:255|string|exists:users,phone',
        ];
    }
	
	// protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    //{
    //    $response = new JsonResponse(
    //        $validator->errors(), 422);
    //    throw new \Illuminate\Validation\ValidationException($validator, $response);
    //}
}
