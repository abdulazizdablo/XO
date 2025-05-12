<?php

namespace App\Http\Requests\Users;

use Illuminate\Foundation\Http\FormRequest;

class ChangePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // عدّل حسب نظام الصلاحيات إن لزم
    }

    public function rules(): array
    {
        return [
            'user.old_password' => 'required|string|max:255',
            'user.new_password' => 'required|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'user.old_password.required' => 'كلمة المرور القديمة مطلوبة.',
            'user.new_password.required' => 'كلمة المرور الجديدة مطلوبة.',
        ];
    }
}
