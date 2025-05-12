<?php

namespace App\Http\Requests\Shipments;

use Illuminate\Foundation\Http\FormRequest;

class ShipmentArrivedRequest extends FormRequest
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
            'cargo_shipment_id' => ['required', 'integer', 'exists:cargo_shipments,id'],
            'items_received' => ['required', 'array'],
            'items_received.*.item_id' => ['required', 'integer', 'exists:shipment_product_variations,id'],
            'items_received.*.product_variation_id' => ['required', 'integer', 'exists:product_variations,id'],
            'items_received.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }

}
