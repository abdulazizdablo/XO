<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateProductRequest extends FormRequest
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
            'sub_category_id' => 'required',
            'item_no' => 'required',
            'name' => 'required',
            'description' => 'required',
            'price' => 'required',
            'material' => 'required',
            'composition' => 'required',
            'fabric' => 'required',
            'care_instructions' => 'required',
            'fit' => 'required',
            'style' => 'required',
            'season' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'sub_category_id.required' => 'this category is required',
            'item_no.required' => 'this item_no is required',
            'name.required' => 'this name is required',
            'description.required' => 'this description is required',
            'price.required' => 'this price is required',
            'material.required' => 'this material is required',
            'composition.required' => 'this composition is required',
            'fabric.required' => 'this fabric is required',
            'care_instructions.required' => 'this care_instructions is required',
            'fit.required' => 'this fit is required',
            'style.required' => 'this style is required',
            'season.required' => 'this season is required',
        ];
    }
}
