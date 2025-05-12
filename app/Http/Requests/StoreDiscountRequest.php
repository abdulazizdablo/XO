<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
class  StoreDiscountRequest extends FormRequest
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
        'promotion_name' => 'required|string|max:255',
        'start_date' => [
            'required_if:group_type,discount',
            function ($attribute, $value, $fail) {
                if (!Carbon::instance($value)->isAfter(Carbon::today())) {
                    return $fail('The start date must be after today.');
                }
            },
            'max:255'
        ],
        'end_date' => [
            'required_if:group_type,discount',
            function ($attribute, $value, $fail) {
                if (!Carbon::instance($value)->isAfter(Carbon::instance($request->input('start_date')))) {
                    return $fail('The end date must be after the start date.');
                }
            },
            'max:255'
        ],
        'percentage' => [
            'required_if:group_type,discount|integer|lte:90',
        ],
        'promotion_type' => [
            'required_if:group_type,discount|in:flash_sales',
        ],
        'image' => 'required|image|mimes:jpeg,bmp,png,webp,svg',
    ];
    }
}
