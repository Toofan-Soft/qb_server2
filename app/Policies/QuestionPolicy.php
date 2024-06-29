<?php

namespace App\Policies;

use App\Enums\RoleEnum;
use App\Helpers\ValidateHelper;

class QuestionPolicy
{
    public function add(): bool
    {
        $correctRoles = [
            RoleEnum::DATA_ENTRY->value,
            RoleEnum::SYSTEM_ADMINISTRATOR->value
        ];
        return ValidateHelper::validatePolicy($correctRoles);
    }

    public function modify(): bool
    {
        $correctRoles = [
            RoleEnum::DATA_ENTRY->value,
            RoleEnum::SYSTEM_ADMINISTRATOR->value
        ];
        return ValidateHelper::validatePolicy($correctRoles);
    }
    ///////////////
    public function submit(): bool
    {
        $correctRoles = [];
        return ValidateHelper::validatePolicy($correctRoles);
    }
    ///////////////
    public function accept(): bool
    {
        $correctRoles = [];
        return ValidateHelper::validatePolicy($correctRoles);
    }
    ///////////////
    public function reject(): bool
    {
        $correctRoles = [];
        return ValidateHelper::validatePolicy($correctRoles);
    }

    public function delete(): bool
    {
        $correctRoles = [
            RoleEnum::DATA_ENTRY->value,
            RoleEnum::SYSTEM_ADMINISTRATOR->value
        ];
        return ValidateHelper::validatePolicy($correctRoles);
    }
    /////////////
    public function retrieve(): bool
    {
        $correctRoles = [];
        return ValidateHelper::validatePolicy($correctRoles);
    }
    ////////////
    public function retrieveEditable(): bool
    {
        $correctRoles = [];
        return ValidateHelper::validatePolicy($correctRoles);
    }
    //////////////
    public function retrieveList(): bool
    {
        $correctRoles = [];
        return ValidateHelper::validatePolicy($correctRoles);
    }
}
