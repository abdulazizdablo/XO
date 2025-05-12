<?php

namespace App\Http\Requests\Groups;

use Illuminate\Foundation\Http\FormRequest;

class StoreOfferRequest extends FormRequest
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
            'group_name_ar' => 'required|string|max:255',
            'group_name_en' => 'required|string|max:255',
            'start_date' => 'required_if:group_type,discount|max:255',
            'promotion_name' => 'required|string|max:255',
            'end_date' => 'required_if:group_type,discount|max:255',
            'percentage' => 'required_if:group_type,discount|integer|lte:90',
            'promotion_type' => 'required_if:group_type,offer|in:BOGO,BOGH,BTGO',
            'number_of_items' => 'required_if:group_type,offer|max:255',
            'image' => 'required|image|mimes:jpeg,bmp,png,webp,svg',
        ];
    }
}
