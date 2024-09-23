<?php

namespace App\Http\Requests\GiftRequest;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGiftCardRequest extends FormRequest
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
                'coupon_id' => 'required|exists:coupons,id',
                'value' => 'required|integer|lt:2000000',
                'password' => 'required|string',
                'payment_method' => 'required|string',
            ];
        }
    }
    