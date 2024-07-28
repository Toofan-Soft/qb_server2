<?php

namespace App\Policies;

use App\Enums\RoleEnum;
use App\Helpers\ValidateHelper;

class ProctorOnlineExamPolicy
{
    public function startStudentOnlineExam(): bool
    {
        return ValidateHelper::validatePolicy([RoleEnum::PROCTOR->value]);
    }

    public function suspendStudentOnlineExam(): bool
    {
        return ValidateHelper::validatePolicy([RoleEnum::PROCTOR->value]);
    }

    public function continueStudentOnlineExam(): bool
    {
        return ValidateHelper::validatePolicy([RoleEnum::PROCTOR->value]);
    }

    public function finishStudentOnlineExam(): bool
    {
        return ValidateHelper::validatePolicy([RoleEnum::PROCTOR->value]);
    }

    public function retrieveOnlineExam(): bool
    {
        return ValidateHelper::validatePolicy([RoleEnum::PROCTOR->value]);
    }

    public function retrieveOnlineExams(): bool
    {
        return ValidateHelper::validatePolicy([RoleEnum::PROCTOR->value]);
    }
    
    public function retrieveOnlineExamStudents(): bool
    {
        return ValidateHelper::validatePolicy([RoleEnum::PROCTOR->value]);
    }
    
    public function finishOnlineExam(): bool
    {
        return ValidateHelper::validatePolicy([RoleEnum::PROCTOR->value]);
    }

}
