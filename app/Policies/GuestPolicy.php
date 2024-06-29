<?php

namespace App\Policies;

use App\Enums\RoleEnum;
use App\Helpers\ValidateHelper;

class GuestPolicy
{
    public function add(): bool
    {
        $correctRoles = [
            RoleEnum::DATA_ENTRY->value,
            RoleEnum::SYSTEM_ADMINISTRATOR->value
        ];
        return ValidateHelper::validatePolicy($correctRoles);
    }

    public function modify(): bool
    {
        $correctRoles = [
            RoleEnum::DATA_ENTRY->value,
            RoleEnum::SYSTEM_ADMINISTRATOR->value
        ];
        return ValidateHelper::validatePolicy($correctRoles);
    }
    //////////////
    public function retrieveEditable(): bool
    {
        $correctRoles = [];
        return ValidateHelper::validatePolicy($correctRoles);
    }
}
