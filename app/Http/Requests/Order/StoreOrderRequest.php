<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // يمكن ضبطها لاحقًا حسب الصلاحيات
    }

    public function rules(): array
    {
        return [
            'inventory_id' => 'nullable|exists:inventories,id',

            'address_id' => 'required_if:shipping_info.*.lat,null,shipping_info.*.long,null,shipping_info.*.city,null,shipping_info.*.city_id,null,shipping_info.*.street,null,shipping_info.*.neighborhood,null|exists:addresses,id',

            'branch_id' => [
                Rule::when($this->input('shipping_info.0.type') === 'kadmous', 'required|exists:branches,id'),
            ],

            'order' => 'required|array',
            'order.*.payment_method' => 'required|max:30|in:ecash,syriatel-cash,mtn-cash,cod,Free,free,payment_method_1,payment_method_2,payment_method_3',
            'order.*.coupon' => 'sometimes|max:8',
            'order.*.is_gift' => 'nullable|boolean',
            'order.*.gift_message' => 'nullable|string|max:255',
            'order.*.gift_card' => 'sometimes|max:8',
            'order.*.gift_card_password' => 'sometimes|max:50',
            'order.*.qr_code' => 'required|max:255',

            'order_items' => 'required|array',
            'order_items.*.product_variation_id' => 'required|integer|exists:product_variations,id',
            'order_items.*.quantity' => 'required|integer|min:1',

            'shipping_info' => 'required|array',
            'shipping_info.*.type' => 'required|max:25',

            'shipping_info.*.express' => [
                Rule::when(
                    $this->input('shipping_info.0.type') === 'xo-delivery',
                    'required_without:shipping_info.*.data|boolean'
                ),
            ],
            'shipping_info.*.date' => [
                Rule::when(
                    $this->input('shipping_info.0.type') === 'xo-delivery' && !$this->input('shipping_info.0.express'),
                    'required|max:255'
                ),
            ],
            'shipping_info.*.time' => [
                Rule::when(
                    $this->input('shipping_info.0.type') === 'xo-delivery' && !$this->input('shipping_info.0.express'),
                    'required|max:255'
                ),
            ],
            'shipping_info.*.city' => 'required_without_all:shipping_info.*.city_id,address_id|string|max:255',
            'shipping_info.*.city_id' => 'required_without_all:shipping_info.*.city,address_id|integer|exists:cities,id',

            'shipping_info.*.street' => [
                Rule::when(
                    $this->input('shipping_info.0.type') === 'xo-delivery',
                    'required_without:address_id|max:255'
                ),
            ],
            'shipping_info.*.neighborhood' => [
                Rule::when(
                    $this->input('shipping_info.0.type') === 'xo-delivery',
                    'required_without:address_id|max:255'
                ),
            ],

            'shipping_info.*.lat' => 'required|sometimes|max:255',
            'shipping_info.*.long' => 'required|sometimes|max:255',
            'shipping_info.*.receiver_first_name' => 'required|max:255|string',

            'shipping_info.*.receiver_father_name' => [
                Rule::when(
                    $this->input('shipping_info.0.type') === 'kadmous',
                    'required|max:255|string'
                ),
            ],

            'shipping_info.*.receiver_last_name' => 'required|max:255|string',
            'shipping_info.*.receiver_phone' => 'required|max:255|string',
            'shipping_info.*.receiver_phone2' => 'nullable|sometimes|max:255',
            'shipping_info.*.additional_details' => 'nullable|sometimes|max:255',
        ];
    }
}
