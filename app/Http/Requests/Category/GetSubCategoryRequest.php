<?php

namespace App\Http\Requests\Category;

use Illuminate\Foundation\Http\FormRequest;

class GetSubCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => ['required', 'string', 'exists:categories,id'],
        ];
    }
}
