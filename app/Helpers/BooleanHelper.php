<?php

namespace App\Helpers;

class BooleanHelper
{
    
    public static function toBoolean($value): bool
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    // public static function isTure($value): bool
    // {
    //     return ($value === true || $value === 'true' || $value == 1);
    // }
    
}
