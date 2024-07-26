<?php

namespace App\Helpers\Roles;

use Illuminate\Contracts\Validation\Rule;
// use Illuminate\Contracts\Validation\ValidationRule;

class PasswordValidationRule implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        // Check if the password length is 8
        if (strlen($value) !== 8) {
            return false;
        }

        // Check if the password contains at least one number
        if (!preg_match('/\d/', $value)) {
            return false;
        }

        // Check if the password contains at least one letter
        if (!preg_match('/[a-zA-Z]/', $value)) {
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
        return 'The :attribute must be exactly 8 characters long and contain at least one number and one letter.';
    }
}