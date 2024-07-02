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

    public function addDepartmentCoursePartTopics(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }

    public function deleteDepartmentCoursePartTopics(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }

    public function retrieveDepartmentCoursePartChapters(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }
    
    public function retrieveDepartmentCoursePartChapterTopics(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }
    
    public function retrieveAvailableDepartmentCoursePartChapters(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }
    
    public function retrieveAvailableDepartmentCoursePartTopics(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }
}
