<?php

namespace App\Policies;

use App\Enums\RoleEnum;
use App\Helpers\ValidateHelper;

class UserPolicy
{
    private static $validRoles = [];
    public function verifyAccount(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }

    public function login(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }

    public function logou(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }
    
    public function changePassword(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }
    
    public function requestAccountReovery(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }
    
    public function changePasswordAfterAccountReovery(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }
    
    public function retrieveProfile(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }
}
