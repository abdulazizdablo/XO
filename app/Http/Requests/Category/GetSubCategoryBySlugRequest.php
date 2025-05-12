<?php

namespace App\Http\Requests\Category;

use Illuminate\Foundation\Http\FormRequest;

class GetSubCategoryBySlugRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
           'slug' => ['required', 'string', 'exists:categories,slug'],
        ];
    }
}
