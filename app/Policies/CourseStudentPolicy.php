<?php

namespace App\Policies;

use App\Enums\RoleEnum;
use App\Helpers\ValidateHelper;

class CourseStudentPolicy
{
    public function addList(): bool
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
    public function pass(): bool
    {
        $correctRoles = [
            RoleEnum::DATA_ENTRY->value,
            RoleEnum::SYSTEM_ADMINISTRATOR->value
        ];
        return ValidateHelper::validatePolicy($correctRoles);
    }
    public function suspend(): bool
    {
        $correctRoles = [
            RoleEnum::DATA_ENTRY->value,
            RoleEnum::SYSTEM_ADMINISTRATOR->value
        ];
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
    ///////////////
    public function retrieveUnlinkList(): bool
    {
        $correctRoles = [];
        return ValidateHelper::validatePolicy($correctRoles);
    }
}
