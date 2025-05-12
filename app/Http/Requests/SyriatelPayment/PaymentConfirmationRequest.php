<?php

namespace App\Http\Requests\SyriatelPayment;

use App\Rules\OrderAmount;
use Illuminate\Foundation\Http\FormRequest;

class PaymentConfirmationRequest extends FormRequest
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

        $rules = [
            'OTP' => 'required|string',//|digits:4',
            'order_id' => 'sometimes|exists:orders,id',
            'gift_id' => 'required_with:gift_code|exists:coupons,id',
            'gift_code' => 'required_with:gift_id|exists:coupons,code|string',
        ];
//'present_unless:gift_card,gift_id'
        // Conditionally apply the OrderAmount rule based on the presence of order_id
        if ($this->has('order_id')) {
            $rules['amount'] = [
                'required',
                'integer',
                'not_in:0',
                new OrderAmount($this->input('order_id'),$this->input('amount')),
            ];
        } else {
            $rules['amount'] = [
                'required',
                'integer',
                'not_in:0',
            ];
        }

        return $rules;
    }
}

