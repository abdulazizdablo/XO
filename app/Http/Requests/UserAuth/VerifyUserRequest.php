<?php

namespace App\Http\Requests\UserAuth;

use Illuminate\Foundation\Http\FormRequest;

class VerifyUserRequest extends FormRequest
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
        return  [
            'phone' => 'required|string|digits:10|exists:users,phone',
            'verification_code' => 'required|digits:4'
        ];
    }
}
