<?php

namespace App\Http\Requests\Account;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAccountRequest extends FormRequest
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
        $account_id = $this->input('account_id');

        return [

            'account_id' => 'required|integer|exists:accounts,id',
            'email' => [
                'sometimes',
                'email',
                Rule::unique('accounts', 'email')->ignore($account_id)
            ],
        ];
    }

}
