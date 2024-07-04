<?php

namespace App\Policies;

use App\Enums\RoleEnum;
use App\Helpers\ValidateHelper;

class GuestPolicy
{
    private static $validRoles = [
        RoleEnum::GUEST
    ];

    // public function addGuest(): bool
    // {
    //     return ValidateHelper::validatePolicy(self::$validRoles);
    // }

    public function modifyGuest(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }
    
    public function retrieveEditableGuestProfile(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }
}
