<?php

namespace App\Policies;

use App\Enums\RoleEnum;
use App\Helpers\ValidateHelper;

class CoursePartPolicy
{
    public function addCoursePart(): bool
    {
        return ValidateHelper::validatePolicy([
            RoleEnum::SYSTEM_ADMINISTRATOR->value,
            RoleEnum::DATA_ENTRY->value
        ]);
    }

    public function modifyCoursePart(): bool
    {
        return ValidateHelper::validatePolicy([
            RoleEnum::SYSTEM_ADMINISTRATOR->value,
            RoleEnum::DATA_ENTRY->value
        ]);
    }

    public function deleteCoursePart(): bool
    {
        return ValidateHelper::validatePolicy([
            RoleEnum::SYSTEM_ADMINISTRATOR->value,
            RoleEnum::DATA_ENTRY->value
        ]);
    }

    public function retrieveEditableCoursePart(): bool
    {
        return ValidateHelper::validatePolicy([
            RoleEnum::SYSTEM_ADMINISTRATOR->value,
            RoleEnum::DATA_ENTRY->value
        ]);
    }

    public function retrieveCourseParts(): bool
    {
        return ValidateHelper::validatePolicy([
            RoleEnum::SYSTEM_ADMINISTRATOR->value,
            RoleEnum::DATA_ENTRY->value
        ]);
    }
}
