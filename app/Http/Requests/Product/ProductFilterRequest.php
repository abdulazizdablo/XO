<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class ProductFilterRequest extends FormRequest
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
            'pageSize' => 'nullable|integer|min:1',
            'section_id' => 'nullable|integer|exists:sections,id',
            'category_id' => 'nullable|integer|exists:categories,id',
            'price_min' => 'nullable|numeric|min:0',
            'price_max' => 'nullable|numeric|max:10000000',
            'color' => 'nullable|array',
            'size' => 'nullable|array',
            'sort' => 'nullable|string|max:25',
            'sub_category' => 'nullable|integer|exists:sub_categories,id',
			'product_slug' => 'nullable|exists:products,slug',
            'sku' => 'nullable|exists:products,item_no',
			'width' => 'nullable|numeric|min:0',
			'height' => 'nullable|numeric|min:0'
			
        ];
    }
}
