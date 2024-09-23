<?php

namespace App\Http\Requests\Exchange;

use Illuminate\Foundation\Http\FormRequest;

class StoreExchangeRequest extends FormRequest
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
            'order_id' => 'required|exists:orders,id',
            'date' => 'required|string',
            'time' => 'required|string|max:30',
            'payment_method' => 'required|string|max:30',
            'reason' => 'required|string|max:255',
            'order_items' => 'required|array',
            'exchange_items' => 'required|array',
        ];
    }
}
