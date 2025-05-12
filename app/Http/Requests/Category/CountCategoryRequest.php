<?php

namespace App\Http\Requests\Category;

use Illuminate\Foundation\Http\FormRequest;

class CountCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'inventory_id' => ['nullable', 'integer', 'exists:inventories,id'],
            'product_id'   => ['required', 'integer', 'exists:products,id'],
        ];
    }
}
