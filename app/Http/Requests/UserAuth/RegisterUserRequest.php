<?php

namespace App\Http\Requests\UserAuth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use App\Rules\CheckUserNotVerfiedNumber;
use Illuminate\Validation\ValidationException;


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
			    //'phone' => 'required|unique:users|regex:/^09\\d{8}$/',
                'phone' => ['required','regex:/^09\\d{8}$/'],
                'email' => 'email|max:255',
                'password' => 'required|string|min:8|max:255',
        ];
    }
	
	
	
	
/*	protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        $errors = $validator->errors()->all();
        
        foreach ($errors as $key => $error) {
            if (strpos($key, 'phone') !== false) {
                throw ValidationException::withMessages([
                    'phone' => $error,
                ])->status(409);
            }
        }
        
        throw ValidationException::withMessages($validator->errors()->all())->status(422);
    }
	
	*/

    //protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    //{
	//	return response()->json($validator->errors(), 422);
    //}
}
