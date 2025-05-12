<?php

namespace App\Http\Requests\Users;

use Illuminate\Foundation\Http\FormRequest;

class ChangePhoneRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // عدّل حسب نظام الصلاحيات إن لزم
    }

    public function rules(): array
    {
        return [
           'phone' => 'required|string|digits:10',
        ];
    }


}
