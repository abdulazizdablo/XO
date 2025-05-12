<?php

namespace App\Http\Requests\Offer;

use Illuminate\Foundation\Http\FormRequest;

class StoreOfferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'        => 'required|string|max:255',
            'type'        => 'required|string|max:255',
            'valid'       => 'required|boolean',
            'description' => 'required|string|max:255',
            'expired_at'  => 'required|date', // الأفضل تحديد نوع التاريخ لو متوقع يكون تاريخ
        ];
    }
}
