<?php

namespace App\Policies;

use App\Enums\RoleEnum;
use App\Helpers\ValidateHelper;

class UniversityPolicy
{
    public function configureUniversityData(): bool
    {
        return ValidateHelper::validatePolicy([
            RoleEnum::SYSTEM_ADMINISTRATOR->value,
            RoleEnum::DATA_ENTRY->value
        ]);
    }

    public function modifyUniversityData(): bool
    {
        return ValidateHelper::validatePolicy([
            RoleEnum::SYSTEM_ADMINISTRATOR->value,
            RoleEnum::DATA_ENTRY->value
        ]);
    }

    public function retrieveUniversityInfo(): bool
    {
        return ValidateHelper::validateUser();
    }
    
    public function retrieveBasicUniversityInfo(): bool
    {
        return ValidateHelper::validateUser();
    }
}
