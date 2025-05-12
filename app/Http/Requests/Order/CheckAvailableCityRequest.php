<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

class CheckAvailableCityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // عدّلها حسب الصلاحيات لو لزم الأمر
    }

    public function rules(): array
    {
        return [
            'order_items' => 'required|array',
            'order_items.*.product_variation_id' => 'required|integer|exists:product_variations,id',
            'order_items.*.quantity' => 'required|integer|min:1',
        ];
    }
}
