<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductPhotosRequest extends FormRequest
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
                    // product id
                    'product_id' => 'required|exists:products,id',
                    // photos
                    'photos' => 'required|array',
                    'photos.*.color_id' => 'required',
                    'photos.*.main_photo' => 'required',
					//'photos.*.image' => 'required',/*|dimensions:min_width=600,min_height=600*/
			
                    'photos.*.image' => 'image|mimes:jpg,jpeg,bmp,png,webp,svg,dng',/*|dimensions:min_width=600,min_height=600*/
                ];
    }
	
	
	protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new \Illuminate\Validation\ValidationException($validator, response()->json([
            'success' => false,
            'error' => [
                'message' => $validator->errors()->first(),
            ],
        ], 422));
    }
}
