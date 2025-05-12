<?php

namespace App\Http\Requests\Shipments;

use Illuminate\Foundation\Http\FormRequest;

class SendShipmentRequest extends FormRequest
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
            'cargo_shipment' => ['required', 'array'],
            // 'cargo_shipment.ship_date' => ['required', 'date_format:Y-m-d H:i:s', 'after:now'],
            'cargo_shipment.to_inventory' => ['required', 'integer', 'exists:inventories,id'],
            //'cargo_shipment.from_inventory' => ['required', 'integer', $this->validateFromInventory()],
            'cargo_shipment.from_inventory' => ['required', $this->validateFromInventory()],
            // 'cargo_shipment.status' => ['required', 'string', 'in:open,closed,pending,canceled'],
            'cargo_shipment.cargo_request_id' => ['nullable', 'integer', 'exists:cargo_requests,id'],
            'cargo_shipment_items' => ['required', 'array'],
            'cargo_shipment_items.*.product_variation_id' => ['required', 'integer', 'exists:product_variations,id'],
            'cargo_shipment_items.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }

    private function validateFromInventory()
    {
        return function ($attribute, $value, $fail) {
            $existsInInventory = \DB::table('inventories')->where('id', $value)->exists();
            $firstPoint = 'first_point'; 

            if (!$existsInInventory && $value !== $firstPoint) {
                $fail("The {$attribute} must either exist in the inventories table or be equal to {$firstPoint}.");
            }
        };
    }
}
