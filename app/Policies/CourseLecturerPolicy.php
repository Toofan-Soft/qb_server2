<?php

namespace App\Policies;

use App\Enums\RoleEnum;
use App\Helpers\ValidateHelper;

class CourseLecturerPolicy
{
    private static $validRoles = [
        RoleEnum::SYSTEM_ADMINISTRATOR,
        RoleEnum::DATA_ENTRY
    ];
    
    public function addCourseLecturer(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }

    public function deleteCourseLecturer(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }
    
    public function retrieveCourseLecturer(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }
    
    public function retrieveCourseLecturers(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }
    
    public function retrieveLecturerCourses(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }
}
