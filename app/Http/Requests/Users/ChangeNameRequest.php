<?php

namespace App\Http\Requests\Users;

use Illuminate\Foundation\Http\FormRequest;

class ChangeNameRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // تأكد من أن المستخدم مخوّل
    }

    public function rules(): array
    {
        return [
            'user.first_name' => 'required|string|max:255',
            'user.last_name' => 'required|string|max:255',
        ];
    }
}
