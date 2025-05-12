<?php

namespace App\Http\Requests\GiftRequest;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePasswordWithCouponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // أو تحقق من صلاحية المستخدم إذا لزم
    }

    public function rules(): array
    {
        return [
            'coupon_id'    => 'required|integer|exists:coupons,id',
            'old_password' => 'required|string|min:8',
            'new_password' => 'required|string|min:8',
        ];
    }
}
