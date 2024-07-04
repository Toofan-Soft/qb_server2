<?php

namespace App\Policies;

use App\Enums\RoleEnum;
use App\Helpers\ValidateHelper;

class FavoriteQuestionPolicy
{
    private static $validRoles = [
        RoleEnum::GUEST,
        RoleEnum::STUDENT,
        RoleEnum::LECTURER,
        RoleEnum::QUESTION_ENTRY,
        RoleEnum::QUESTION_REVIEWER,
        RoleEnum::DATA_ENTRY,
        RoleEnum::SYSTEM_ADMINISTRATOR,
        RoleEnum::PROCTOR

    ];

    public function addPracticeExam(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }
}
