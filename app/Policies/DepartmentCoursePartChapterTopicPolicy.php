<?php

namespace App\Policies;

use App\Enums\RoleEnum;
use App\Helpers\ValidateHelper;

class DepartmentCoursePartChapterTopicPolicy
{
    public function modifyDepartmentCoursePartTopics(): bool
    {
        return ValidateHelper::validatePolicy([
            RoleEnum::SYSTEM_ADMINISTRATOR->value,
            RoleEnum::DATA_ENTRY->value
        ]);
    }

    public function retrieveDepartmentCoursePartChapters(): bool
    {
        return ValidateHelper::validateUser();
    }
    
    public function retrieveDepartmentCoursePartChapterTopics(): bool
    {
        return ValidateHelper::validateUser();
    }
    
    public function retrieveEditableDepartmentCoursePartChapters(): bool
    {
        return ValidateHelper::validatePolicy([
            RoleEnum::SYSTEM_ADMINISTRATOR->value,
            RoleEnum::DATA_ENTRY->value
        ]);
    }
    
    public function retrieveEditableDepartmentCoursePartTopics(): bool
    {
        return ValidateHelper::validatePolicy([
            RoleEnum::SYSTEM_ADMINISTRATOR->value,
            RoleEnum::DATA_ENTRY->value
        ]);
    }
}
