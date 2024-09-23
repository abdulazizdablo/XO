<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Validator;

class IsKadmousRule implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute The name of the attribute being validated.
     * @param mixed $value The value of the attribute.
     * @return bool
     */
    public function passes($attribute, $value)
    {
        // Assuming the request has been validated up to this point
        $isKadmous = request()->input('isKadmous');

        // Check if isKadmous is false and branch_id is not null
        if (!$isKadmous && !is_null($value)) {
            return false;
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Something went wrong!';
    }
}
