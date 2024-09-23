<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateUserRequest extends FormRequest
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
            'name' => 'required',
            'email' => 'required',
            'password' => 'required|regex:/^[^\s]+$/',
            'address' => 'required',
            'phone' => 'required',
        ];
    }

    public function messages(){
        return [
            'name.required' => 'Name Is Required',
            'email.required' => 'Email Is Required',
            'password.required' => 'Password Is Required',
            'address.required' => 'Address Is Required',
            'phone.required' => 'Phone Is Required',
        ];
    }
}
