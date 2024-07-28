<?php

namespace App\Policies;

use App\Enums\RoleEnum;
use App\Helpers\ValidateHelper;

class UserPolicy
{
    // public function verifyAccount(): bool
    // {
    //     return ValidateHelper::validateUser();
    // }

    // public function login(): bool
    // {
    //     return ValidateHelper::validateUser();
    // }

    // public function resendCode(): bool
    // {
    //     return ValidateHelper::validateUser();
    // }
    
    public function logout(): bool
    {
        return ValidateHelper::validateUser();
    }
    
    public function changePassword(): bool
    {
        return ValidateHelper::validateUser();
    }
    
    public function changeLanguage(): bool
    {
        return ValidateHelper::validateUser();
    }
    
    // public function requestAccountReovery(): bool
    // {
    //     return ValidateHelper::validateUser();
    // }

    // public function changePasswordAfterAccountReovery(): bool
    // {
    //     return ValidateHelper::validateUser();
    // }

    // public function verifyAccountAfterRecvery(): bool
    // {
    //     return ValidateHelper::validateUser();
    // }

    public function retrieveProfile(): bool
    {
        return ValidateHelper::validateUser();
    }
}
