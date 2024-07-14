<?php

namespace App\Policies;

use App\Enums\RoleEnum;
use App\Helpers\ValidateHelper;

class CourseStudentPolicy
{
    private static $validRoles = [
        RoleEnum::SYSTEM_ADMINISTRATOR,
        RoleEnum::DATA_ENTRY
    ];
    
    public function addCourseStudents(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }
    
    public function passCourseStudent(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }
    
    public function suspendCourseStudent(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }
    
    public function unsuspendCourseStudent(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }

    public function deleteCourseStudent(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }
    
    // public function retrieveEditableCourseStudent(): bool
    // {
    //     return ValidateHelper::validatePolicy(self::$validRoles);
    // }

    public function retrieveCourseStudents(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }

    public function retrieveUnlinkCourceStudents(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }
}
