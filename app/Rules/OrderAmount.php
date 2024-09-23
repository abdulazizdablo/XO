<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\Order;
use Illuminate\Support\Facades\Validator;


class OrderAmount implements Rule
{

    protected int $order_id;

    public function __construct(int $order_id)
    {
        $this->order_id = $order_id;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $amount
     * @return bool
     */
    public function passes($attribute, $value)
    {
		  $validator = Validator::make(['order_id' => $this->order_id, 'amount' => $value], [
            'order_id' => 'required|integer|exists:orders,id',
            'amount' => 'required|integer',
        ]);
		

// Assuming $validator is an instance of Validator
if ($validator->fails()) {
    // Get the error messages
    $errors = $validator->errors();

    // Format the errors as needed for your response
    // For example, you might want to return them as a JSON response
    return response()->error($errors, 422); // 422 is the HTTP status code for Unprocessable Entity, commonly used for validation errors
}

		
        $order = Order::findOrFail($this->order_id);
        return ($order->paid_by_user + $order->shipping_fee) == $value;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The Amount is not sufficient';
    }
}
