<?php

namespace App\Policies;

use App\Enums\RoleEnum;
use App\Helpers\ValidateHelper;

class DepartmentCoursePolicy
{
    private static $validRoles = [
        RoleEnum::SYSTEM_ADMINISTRATOR,
        RoleEnum::DATA_ENTRY
    ];

    public function addDepartmentCourse(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }

    public function modifyDepartmentCourse(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }

    public function deleteDepartmentCourse(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }
    
    public function retrieveDepartmentCourse(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }
    
    public function retrieveEditableDepartmentCourse(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }
    
    public function retrieveDepartmentCourses(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }
    
    public function retrieveCourseDepartments(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }
    
    public function retrieveDepartmentLevelCourses(): bool
    {
        $roles = [
            RoleEnum::SYSTEM_ADMINISTRATOR,
            RoleEnum::DATA_ENTRY,
            RoleEnum::GUEST
        ];

        return ValidateHelper::validatePolicy($roles);
    }
}
