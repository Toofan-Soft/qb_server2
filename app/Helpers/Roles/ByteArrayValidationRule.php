<?php

namespace App\Helpers\Roles;

use Illuminate\Contracts\Validation\Rule;
// use Illuminate\Contracts\Validation\ValidationRule;

class ByteArrayValidationRule implements Rule
{
    public function passes($attribute, $value)
    {
        // Check if $value is a valid byte array
        // Assuming $value is an array of bytes (integers between -256 and 255)
        // Example validation logic:
        return is_array($value) && count($value) > 0 && min($value) >= -256 && max($value) <= 255;
    }

    public function message()
    {
        return 'The :attribute must be a valid byte array.';
    }
}