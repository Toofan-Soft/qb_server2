<?php

namespace App\Policies;

use App\Enums\RoleEnum;
use App\Helpers\ValidateHelper;

class StudentOnlineExamPolicy
{
    public function saveOnlineExamQuestionAnswer(): bool
    {
        return ValidateHelper::validatePolicy([RoleEnum::STUDENT->value]);
    }

    public function finishOnlineExam(): bool
    {
        return ValidateHelper::validatePolicy([RoleEnum::STUDENT->value]);
    }

    public function retrieveOnlineExam(): bool
    {
        return ValidateHelper::validatePolicy([RoleEnum::STUDENT->value]);
    }

    public function retrieveOnlineExams(): bool
    {
        return ValidateHelper::validatePolicy([RoleEnum::STUDENT->value]);
    }

    public function retrieveOnlineExamQuestions(): bool
    {
        return ValidateHelper::validatePolicy([RoleEnum::STUDENT->value]);
    }

}
