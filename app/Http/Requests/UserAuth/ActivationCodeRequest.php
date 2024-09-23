<?php

namespace App\Http\Requests\AuthUser;

use Illuminate\Foundation\Http\FormRequest;

class ActivationCodeRequest extends FormRequest
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
            'code' => 'required|digits:4'
        ];
    }
	
	//    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    //{
	//		return response()->json($validator->errors(), 422);
	//    }
}
