<?php

namespace App\Policies;

use App\Enums\RoleEnum;
use App\Helpers\ValidateHelper;

class CoursePolicy
{
    private static $validRoles = [
        RoleEnum::SYSTEM_ADMINISTRATOR,
        RoleEnum::DATA_ENTRY
    ];

    public function addCourse(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }

    public function modifyCourse(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }

    public function deleteCourse(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }
    
    public function retrieveEditableCourse(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }
    
    public function retrieveCourses(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }
}
