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
        RoleEnum::SYSTEM_ADMINISTRATOR,
        RoleEnum::DATA_ENTRY,
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

    public function finishPracticeExam(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }
    public function savePracticeExamQuestionAnswer(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }
    public function suspendPracticeExam(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }
    public function deletePracticeExam(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }
    public function retrievePracticeExam(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }
    public function retrieveEditablePracticeExam(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }
    public function retrievePracticeExamsResult(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }
    public function retrievePracticeExams(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }
    public function retrievePracticeExamsAndroid(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }
    public function retrievePracticeExamQuestions(): bool
    {
        return ValidateHelper::validatePolicy(self::$validRoles);
    }
}
