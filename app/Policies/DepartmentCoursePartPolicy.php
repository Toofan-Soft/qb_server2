<?php

namespace App\Policies;

use App\Enums\RoleEnum;
use App\Helpers\ValidateHelper;

class DepartmentCoursePartPolicy
{
    private static $validRoles = [
        RoleEnum::SYSTEM_ADMINISTRATOR,
        RoleEnum::DATA_ENTRY
    ];

    public function addDepartmentCoursePart(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }

    public function modifyDepartmentCoursePart(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }

    public function deleteDepartmentCoursePart(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }
    
    public function retrieveEditableDepartmentCoursePart(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }
}
