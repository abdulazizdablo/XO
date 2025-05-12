<?php

namespace App\Http\Requests\GiftRequest;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator;
use App\Models\AppSetting;

class StoreGiftCardRequest extends FormRequest
{
    protected $maxBalance;

    public function authorize(): bool
    {
        return true;
    }

    public function prepareForValidation()
    {
        // استخراج قيمة max من الإعدادات
        $key = AppSetting::where('key', 'GiftCardDetails')->firstOrFail();
        $this->maxBalance = json_decode($key->value)->balance->max ?? 0;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'password' => 'required|string|min:8',
            'amount_off' => 'required|numeric|lte:' . $this->maxBalance,
            'payment_method' => 'required|string|in:syriatel-cash,mtn-cash,ecash,payment_method_1,payment_method_2,payment_method_3',
        ];
    }
}
