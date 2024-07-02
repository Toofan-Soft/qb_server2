<?php

namespace App\Policies;

use App\Enums\RoleEnum;
use App\Helpers\ValidateHelper;

class StudentPolicy
{
    private static $validRoles = [
        RoleEnum::SYSTEM_ADMINISTRATOR,
        RoleEnum::DATA_ENTRY
    ];

    public function addStudent(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }

    public function modifyStudent(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }

    public function deleteStudent(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }
    
    public function retrieveStudent(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }
    
    public function retrieveEditableStudent(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }
    
    public function retrieveStudents(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }
}
