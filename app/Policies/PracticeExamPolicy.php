<?php

namespace App\Policies;

use App\Enums\RoleEnum;
use App\Helpers\ValidateHelper;

class PracticeExamPolicy
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

    public function modifyPracticeExam(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }

    public function finishPractiseExam(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }
    public function savePractiseExamQuestionAnswer(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }
    public function suspendPractiseExam(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }
    public function deletePracticeExam(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }
    public function retrievePractiseExam(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }
    public function retrieveEditablePracticeExam(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }
    public function retrievePractiseExamsResult(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }
    public function retrievePracticeExams(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }
    public function retrievePractiseExamsAndroid(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }
    public function retrievePractiseExamsQuestions(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }
}
