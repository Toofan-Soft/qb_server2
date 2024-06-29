<?php

namespace App\Policies;

use App\Enums\RoleEnum;
use App\Helpers\ValidateHelper;

class UniversityPolicy
{
    public function configure(): bool
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
    /////////////
    public function retrieve(): bool
    {
        $correctRoles = [];
        return ValidateHelper::validatePolicy($correctRoles);
    }
    ///////////////
    public function retrieveBasicInfo(): bool
    {
        $correctRoles = [];
        return ValidateHelper::validatePolicy($correctRoles);
    }
}
