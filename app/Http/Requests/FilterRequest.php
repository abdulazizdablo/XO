<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class  FilterRequest extends FormRequest
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
                'sku_code' => 'nullable|string|max:25',
                'invoice_number' => 'nullable|string|max:25',
                'inventory' => 'nullable|integer|exists:inventories,id',
                'status' => 'nullable|string|max:25',
                'pricing_min' => 'nullable|numeric|min:0',
                'pricing_max' => 'nullable|numeric|min:0|gte:pricing_min',
                'quantity' => 'nullable|integer|min:1',
                'date_min' => 'nullable|date',
                'date_max' => 'nullable|date|after_or_equal:date_min',
                'date_send_min' => 'nullable|date',
                'date_send_max' => 'nullable|date|after_or_equal:date_send_min',
                'date_created_min'=>'nullable|date',
                'date_created_max'=>'nullable|date|after_or_equal:date_created_min',
                'date_shipped_min'=>'nullable|date',
                'date_shipped_max'=>'nullable|date|after_or_equal:date_shipped_min',
                'date_received_min'=>'nullable|date',
                'date_received_max'=>'nullable|date|after_or_equal:date_received_min',
                'delivery_min' => 'nullable|date',
                'delivery_max' => 'nullable|date|after_or_equal:delivery_min',
                'search' => 'nullable|string|max:25',
                'sort_key' => 'nullable|string|max:25',
                'sort_value' => 'nullable|in:asc,desc',
                'product_name' => 'nullable|string|max:25'
            ];
    }
}
