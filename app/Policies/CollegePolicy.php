<?php

namespace App\Policies;

use App\Enums\RoleEnum;
use App\Helpers\ValidateHelper;

class CollegePolicy
{
    private static $validRoles = [
        RoleEnum::SYSTEM_ADMINISTRATOR,
        RoleEnum::DATA_ENTRY
    ];

    public function addCollege(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }

    public function modifyCollege(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }

    public function deleteCollege(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }

    public function retrieveCollege(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }
    public function retrieveEditableCollege(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }

    public function retrieveColleges(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }

    public function retrieveBasicCollegesInfo(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }
}
