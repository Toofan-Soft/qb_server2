<?php

namespace App\Policies;

use App\Enums\RoleEnum;
use App\Helpers\ValidateHelper;

class GuestPolicy
{
    public function modifyGuest(): bool
    {
        return ValidateHelper::validatePolicy([
            RoleEnum::GUEST->value
        ]);
    }
    
    public function retrieveEditableGuestProfile(): bool
    {
        return ValidateHelper::validatePolicy([
            RoleEnum::GUEST->value
        ]);
    }
}
