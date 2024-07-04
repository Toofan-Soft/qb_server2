<?php

namespace App\Policies;

use App\Enums\RoleEnum;
use App\Helpers\ValidateHelper;

class DepartmentPolicy
{
    private static $validRoles = [
        RoleEnum::SYSTEM_ADMINISTRATOR,
        RoleEnum::DATA_ENTRY
    ];

    public function addDepartment(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }

    public function modifyDepartment(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }

    public function deleteDepartment(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }

    public function retrieveDepartment(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }
    
    public function retrieveDepartments(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }
    
    public function retrieveBasicDepartmentsInfo(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }
}
