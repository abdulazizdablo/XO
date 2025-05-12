<?php

namespace App\Http\Requests\StockLevel;

use Illuminate\Foundation\Http\FormRequest;

class StoreStockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // يمكنك تعديلها لاحقًا حسب الصلاحيات
    }

    public function rules(): array
    {
        return [
                    'stock.name' => 'sometimes|string|max:255',
                    'stock.min_stock_level' => 'sometimes |string|max:255',
                    'stock.max_stock_level' => 'sometimes |string|max:255',
                    'stock.safety_stock_level' => 'sometimes|string|max:255',
                    'stock.target_date' => 'sometimes|string|max:255',
                    'stock.sold_quantity' => 'sometimes|string|max:255',
                    'stock.status' => 'sometimes|string|max:255',
                ]
        ];
    }

    public function messages(): array
    {
        return [
            'stock.name.required' => 'اسم المخزون مطلوب.',
            'stock.min_stock_level.required' => 'الحد الأدنى للمخزون مطلوب.',
            'stock.max_stock_level.required' => 'الحد الأقصى للمخزون مطلوب.',
            'stock.safety_stock_level.required' => 'مستوى الأمان للمخزون مطلوب.',
            'stock.target_date.required' => 'تاريخ الهدف مطلوب.',
            'stock.sold_quantity.required' => 'الكمية المباعة مطلوبة.',
            'stock.status.required' => 'حالة المخزون مطلوبة.',
        ];
    }
}
