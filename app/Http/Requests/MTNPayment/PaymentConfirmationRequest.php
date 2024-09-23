<?php

namespace App\Http\Requests\MTNPayment;

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
        return [

            'Phone' => 'required|string|digits:12',
            'Code' => 'required|string|digits:6',
            'order_id' => 'sometimes|exists:orders,id',
            'gift_id' => 'required_without:order_id|required_with:gift_code|exists:coupons,id',
            'gift_code' => 'required_without:order_id|required_with:gift_id|exists:coupons,code|string',
            //'transactionID' => 'required|',
        ];
    }
}
