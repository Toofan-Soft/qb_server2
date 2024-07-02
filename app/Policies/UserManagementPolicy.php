<?php

namespace App\Policies;

use App\Enums\RoleEnum;
use App\Helpers\ValidateHelper;

class UserManagementPolicy
{
    private static $validRoles = [
        RoleEnum::SYSTEM_ADMINISTRATOR
    ];

    public function addUser(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }

    public function modifyUserRoles(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }

    public function changeUserStatus(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }

    public function deleteUser(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }
    
    public function retrieveUser(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }
    
    public function retrieveUsers(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }
    
    public function retrieveOwnerRoles(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }
}
