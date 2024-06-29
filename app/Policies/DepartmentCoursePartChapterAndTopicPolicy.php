<?php

namespace App\Policies;

use App\Enums\RoleEnum;
use App\Helpers\ValidateHelper;

class DepartmentCoursePartChapterAndTopicPolicy
{
    public function addTopicList(): bool
    {
        $correctRoles = [
            RoleEnum::DATA_ENTRY->value,
            RoleEnum::SYSTEM_ADMINISTRATOR->value
        ];
        return ValidateHelper::validatePolicy($correctRoles);
    }

    public function deleteTopicList(): bool
    {
        $correctRoles = [
            RoleEnum::DATA_ENTRY->value,
            RoleEnum::SYSTEM_ADMINISTRATOR->value
        ];
        return ValidateHelper::validatePolicy($correctRoles);
    }

    public function retrieveChapterList(): bool
    {
        $correctRoles = [
            RoleEnum::DATA_ENTRY->value,
            RoleEnum::SYSTEM_ADMINISTRATOR->value
        ];
        return ValidateHelper::validatePolicy($correctRoles);
    }
    /////////////
    public function retrieveTopicList(): bool
    {
        $correctRoles = [];
        return ValidateHelper::validatePolicy($correctRoles);
    }
    //////////////
    public function retrieveAvailableChapterList(): bool
    {
        $correctRoles = [];
        return ValidateHelper::validatePolicy($correctRoles);
    }
    ///////////////
    public function retrieveAvailableTopicList(): bool
    {
        $correctRoles = [];
        return ValidateHelper::validatePolicy($correctRoles);
    }
}
