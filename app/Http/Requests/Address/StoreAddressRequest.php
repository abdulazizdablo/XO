<?php

namespace App\Http\Requests\Address;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\IsKadmousRule;
use Illuminate\Http\JsonResponse;

class StoreAddressRequest extends FormRequest
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
            'address.first_name' => 'sometimes|string|max:255',
			'address.father_name' => 'nullable|string|max:50',
            'address.last_name' => 'sometimes|string|max:255',
            'address.phone' => 'sometimes|string|max:20',
            'address.city' => 'required_without:address.city_id|string|max:255',
            'address.city_id' => 'required_without:address.city|integer|exists:cities,id',   
			'address.branch_id' => ['sometimes','integer','nullable', new IsKadmousRule,'exists:branches,id'],
			'address.isKadmous' => 'required|boolean',
            'address.neighborhood' => 'sometimes|string|max:255',
            'address.street' => 'sometimes|string|max:255',
            'address.another_details' => 'sometimes|string|max:255',
            'address.lat_long' => 'sometimes|regex:/^[+-]?\d+\.\d+,[+-]?\d+\.\d+$/',
            'address.phone_number_two' => 'sometimes|string|max:255'
        ];
    }

 
}
