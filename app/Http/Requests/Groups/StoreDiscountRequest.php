<?php

namespace App\Http\Requests\Groups;

use Illuminate\Foundation\Http\FormRequest;

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
                    'date',
                    'after_or_equal:today',
                    'max:255'
                ],
                'end_date' => [
                    'required_if:group_type,discount',
                    'date',
                    'after:start_date',
                    'max:255'
                ],
                'percentage' => [
                    'required_if:group_type,discount',
                    'integer',
                    'lte:90',
                    'gt:0'
                ],
                'promotion_type' => [
                    'required_if:group_type,discount',
                    'in:flash_sales'
                ],
                'image' => 'required|image|mimes:jpeg,bmp,png,webp,svg',
            ];
    }
    
        protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator){
                    throw new \Illuminate\Validation\ValidationException($validator, response()->json([
                            'errors' => $validator->errors(),
                        ],400));

        }

}
