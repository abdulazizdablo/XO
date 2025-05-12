<?php

namespace App\Http\Requests\Offer;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOfferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
			
			'offer_id'    => 'required|exists:offers,id',
            'name'        => 'sometimes|string|max:255',
            'type'        => 'sometimes|string|max:255',
            'valid'       => 'sometimes|boolean',
            'description' => 'sometimes|string|max:255',
            'expired_at'  => 'sometimes|date', // يمكن استخدام 'sometimes|string' إذا ما كنت تتوقع تاريخ
        ];
    }
}
