<?php

namespace App\Http\Requests\Category;

use Illuminate\Foundation\Http\FormRequest;

class StoreCategoryRequest extends FormRequest
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
                'section_id' => 'required',
                'integer',
                'exists:sections,id',
                'name_en' => 'required|string|max:255',
                'name_ar' => 'required|string|max:255',
                'image' => 'required|image|mimes:jpeg,jpg,bmp,png,webp,svg|max:512',
                //'image' => 'required'
            ];
    }
}
