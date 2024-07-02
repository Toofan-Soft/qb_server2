<?php

namespace App\Policies;

use App\Enums\RoleEnum;
use App\Helpers\ValidateHelper;

class CoursePartPolicy
{
    private static $validRoles = [
        RoleEnum::SYSTEM_ADMINISTRATOR,
        RoleEnum::DATA_ENTRY
    ];

    public function addCoursePart(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }

    public function modifyCoursePart(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }

    public function deleteCoursePart(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }

    public function retrieveEditableCoursePart(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }

    public function retrieveCourseParts(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }
}
