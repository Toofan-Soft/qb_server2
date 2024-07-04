<?php

namespace App\Policies;

use App\Enums\RoleEnum;
use App\Helpers\ValidateHelper;

class StudentOnlineExamPolicy
{
    public function saveOnlineExamQuestionAnswer(): bool
    {
        $validRoles = [
            RoleEnum::STUDENT
        ];
        return ValidateHelper::validatePolicy($validRoles);
    }

    public function finishOnlineExam(): bool
    {
        $validRoles = [
            RoleEnum::STUDENT
        ];
        return ValidateHelper::validatePolicy($validRoles);
    }

    public function retrieveOnlineExam(): bool
    {
        $validRoles = [
            RoleEnum::STUDENT
        ];
        return ValidateHelper::validatePolicy($validRoles);
    }

    public function retrieveOnlineExams(): bool
    {
        $validRoles = [
            RoleEnum::STUDENT
        ];
        return ValidateHelper::validatePolicy($validRoles);
    }

    public function retrieveOnlineExamQuestions(): bool
    {
        $validRoles = [
            RoleEnum::STUDENT
        ];
        return ValidateHelper::validatePolicy($validRoles);
    }

}
