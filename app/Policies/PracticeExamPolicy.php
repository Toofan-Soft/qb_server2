<?php

namespace App\Policies;

use App\Enums\RoleEnum;
use App\Helpers\ValidateHelper;

class PracticeExamPolicy
{
    public function addPracticeExam(): bool
    {
        return ValidateHelper::validateUser();
    }

    public function modifyPracticeExam(): bool
    {
        return ValidateHelper::validateUser();
    }

    public function startPracticeExam(): bool
    {
        return ValidateHelper::validateUser();
    }
    public function continuePracticeExam(): bool
    {
        return ValidateHelper::validateUser();
    }
    public function suspendPracticeExam(): bool
    {
        return ValidateHelper::validateUser();
    }
    
    public function finishPracticeExam(): bool
    {
        return ValidateHelper::validateUser();
    }
    public function savePracticeExamQuestionAnswer(): bool
    {
        return ValidateHelper::validateUser();
    }
    public function deletePracticeExam(): bool
    {
        return ValidateHelper::validateUser();
    }
    public function retrievePracticeExam(): bool
    {
        return ValidateHelper::validateUser();
    }
    public function retrieveEditablePracticeExam(): bool
    {
        return ValidateHelper::validateUser();
    }
    public function retrievePracticeExamsResult(): bool
    {
        return ValidateHelper::validateUser();
    }
    public function retrievePracticeExams(): bool
    {
        return ValidateHelper::validateUser();
    }
    public function retrievePracticeExamsAndroid(): bool
    {
        return ValidateHelper::validateUser();
    }
    public function retrievePracticeExamQuestions(): bool
    {
        return ValidateHelper::validateUser();
    }
}
