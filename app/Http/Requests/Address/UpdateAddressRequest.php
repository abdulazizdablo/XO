<?php

namespace App\Http\Requests\Address;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;

class UpdateAddressRequest extends FormRequest
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
            'address.id' => 'required|exists:addresses,id',
            'address.city' => 'required_without:address.city_id|string|max:255',
            'address.city_id' => 'required_without:address.city|integer|exists:cities,id',  
            'address.neighborhood' => 'nullable|string|max:255',
            'address.street' => 'sometimes|string|max:255',
            'address.another_details' => 'nullable|string|max:255',
            'address.lat_long' => 'sometimes|regex:/^[+-]?\d+\.\d+,[+-]?\d+\.\d+$/',
            'address.phone_number_two' => 'sometimes|string|max:20',
			'address.phone' => 'sometimes|string|max:20'
        ];
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        $response = new JsonResponse([
            'status' => 'error',
            'message' => 'error',
            'errors' => $validator->errors(),
        ], 400);

        throw new \Illuminate\Validation\ValidationException($validator, $response);
    }
}
