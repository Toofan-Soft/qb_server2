<?php

namespace App\Policies;

use App\Enums\RoleEnum;
use App\Helpers\ValidateHelper;

class CollegePolicy
{
    public function addCollege(): bool
    {
        return ValidateHelper::validatePolicy([
            RoleEnum::SYSTEM_ADMINISTRATOR->value,
            RoleEnum::DATA_ENTRY->value
        ]);
    }

    public function modifyCollege(): bool
    {
        return ValidateHelper::validatePolicy([
            RoleEnum::SYSTEM_ADMINISTRATOR->value,
            RoleEnum::DATA_ENTRY->value
        ]);
    }

    public function deleteCollege(): bool
    {
        return ValidateHelper::validatePolicy([
            RoleEnum::SYSTEM_ADMINISTRATOR->value,
            RoleEnum::DATA_ENTRY->value
        ]);
    }

    public function retrieveCollege(): bool
    {
        return ValidateHelper::validateUser();
    }
    public function retrieveEditableCollege(): bool
    {
        return ValidateHelper::validatePolicy([
            RoleEnum::SYSTEM_ADMINISTRATOR->value,
            RoleEnum::DATA_ENTRY->value
        ]);
    }

    public function retrieveColleges(): bool
    {
        return ValidateHelper::validateUser();
    }

    public function retrieveBasicCollegesInfo(): bool
    {
        return ValidateHelper::validateUser();
    }
}
