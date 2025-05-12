<?php

namespace App\Http\Requests\Shipments;

use Illuminate\Foundation\Http\FormRequest;

class SendCargoRequest extends FormRequest
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
            'cargo_request' => ['required', 'array'],
            'cargo_request.ship_date' => ['required', 'date_format:Y-m-d H:i:s', 'after:now'],
            'cargo_request.inventory_id' => ['nullable', 'integer', 'exists:inventories,id'],
            'cargo_request.status' => ['sometimes', 'string', 'in:open,closed,pending,canceled'],
            'cargo_request.employee_id' => ['sometimes', 'integer', 'exists:employees,id'],
            'cargo_request_items' => ['required', 'array', 'min:1'],
            'cargo_request_items.*.cargo_request_id' => ['sometimes', 'integer', 'exists:cargo_requests,id'],
            'cargo_request_items.*.product_variation_id' => ['required', 'integer', 'exists:product_variations,id'],
            'cargo_request_items.*.requested_from_inventory' => ['required', 'integer',],
        ];
    }
}
