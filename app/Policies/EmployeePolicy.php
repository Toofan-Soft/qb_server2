<?php

namespace App\Policies;

use App\Enums\RoleEnum;
use App\Helpers\ValidateHelper;

class EmployeePolicy
{
    private static $validRoles = [
        RoleEnum::SYSTEM_ADMINISTRATOR,
        RoleEnum::DATA_ENTRY
    ];

    public function addEmployee(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }

    public function modifyEmployee(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }

    public function deleteEmployee(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }
    
    public function retrieveEmployee(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }
    
    public function retrieveEditableEmployee(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }
    
    public function retrieveEmployees(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }
}
