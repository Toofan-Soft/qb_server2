<?php

namespace App\Policies;

use App\Enums\RoleEnum;
use App\Helpers\ValidateHelper;

class DepartmentCoursePartChapterTopicPolicy
{
    private static $validRoles = [
        RoleEnum::SYSTEM_ADMINISTRATOR,
        RoleEnum::DATA_ENTRY
    ];

    public function modifyDepartmentCoursePartTopics(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }

    // public function deleteDepartmentCoursePartTopics(): bool
    // {
    //     return ValidateHelper::validatePolicy(self::$validRoles);
    // }

    public function retrieveDepartmentCoursePartChapters(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }
    
    public function retrieveDepartmentCoursePartChapterTopics(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }
    
    public function retrieveEditableDepartmentCoursePartChapters(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }
    
    public function retrieveEditableDepartmentCoursePartTopics(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }
}
