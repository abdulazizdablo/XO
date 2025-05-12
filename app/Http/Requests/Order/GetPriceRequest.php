<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

class GetPriceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // عدّلها حسب صلاحيات المستخدم إن لزم الأمر
    }

    public function rules(): array
    {
        return [
            'gift' => 'sometimes|max:8',
            'coupon' => 'sometimes|max:8',
            'order_items' => 'required|array',
            'order_items.*.product_variation_id' => 'required|integer|exists:product_variations,id',
            'order_items.*.quantity' => 'required|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'gift.max' => 'رمز الهدية يجب ألا يتجاوز 8 أحرف.',
            'coupon.max' => 'رمز الكوبون يجب ألا يتجاوز 8 أحرف.',
            'order_items.required' => 'قائمة المنتجات مطلوبة.',
            'order_items.array' => 'تنسيق المنتجات غير صحيح.',
            'order_items.*.product_variation_id.required' => 'رقم تعريف المنتج مطلوب.',
            'order_items.*.product_variation_id.integer' => 'رقم تعريف المنتج يجب أن يكون عددًا صحيحًا.',
            'order_items.*.quantity.required' => 'الكمية مطلوبة.',
            'order_items.*.quantity.integer' => 'الكمية يجب أن تكون عددًا صحيحًا.',
            'order_items.*.quantity.min' => 'الكمية يجب أن تكون على الأقل 1.',
        ];
    }
}
