<?php

namespace App\Http\Requests\Color;

use Illuminate\Foundation\Http\FormRequest;

class StoreColorRequest extends FormRequest
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
        return 
        [
            'name_en' => 'required|max:25',
            'name_ar' => 'required|max:25',
            'hex_code' => 'required|unique:colors|max:25',
            'sku_code' => 'required|unique:colors|max:25',
        ];
    }
}
