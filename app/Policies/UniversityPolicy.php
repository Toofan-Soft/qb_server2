<?php

namespace App\Policies;

use App\Enums\RoleEnum;
use App\Helpers\ValidateHelper;

class UniversityPolicy
{
    private static $validRoles = [
        RoleEnum::SYSTEM_ADMINISTRATOR,
        RoleEnum::DATA_ENTRY
    ];

    public function configureUniversityData(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }

    public function modifyUniversityData(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }

    public function retrieveUniversityInfo(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }
    
    public function retrieveBasicUniversityInfo(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }
}
