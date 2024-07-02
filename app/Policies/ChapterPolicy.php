<?php

namespace App\Policies;

use App\Enums\RoleEnum;
use App\Helpers\ValidateHelper;

class ChapterPolicy
{
    private static $validRoles = [
        RoleEnum::SYSTEM_ADMINISTRATOR,
        RoleEnum::DATA_ENTRY,
        RoleEnum::QUESTION_ENTRY
    ];

    public function addChapter(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }

    public function modifyChapter(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }

    public function deleteChapter(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }
    
    public function retrieveChapter(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }
    
    public function retrieveEditableChapter(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }
    
    public function retrieveChapterDescription(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }
    
    public function retrieveChapters(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }
    
    public function retrieveAvailableChapters(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }
}
