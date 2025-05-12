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
            'address.isKadmous' => 'required|boolean',
            'address.first_name' => 'sometimes|string|max:25',
            'address.father_name' => ['nullable','string','max:25', 'required_if:address.isKadmous,true'],
            'address.last_name' => 'sometimes|string|max:25',
            'address.phone' => 'sometimes|string|max:20',
            'address.city' => ['required_without:address.city_id','string','max:255'],
            'address.city_id' => ['required_without:address.city','integer','exists:cities,id'],   
			'address.branch_id' => ['nullable','integer','exists:branches,id', 'required_if:address.isKadmous,true'],
            'address.neighborhood' => 'nullable|string|max:255',
            'address.street' => 'sometimes|string|max:255',
            'address.another_details' => 'nullable|string|max:255',
            'address.lat_long' => 'sometimes|regex:/^[+-]?\d+\.\d+,[+-]?\d+\.\d+$/',
            'address.phone_number_two' => 'sometimes|string|max:20'
        ];
    }

 
}
