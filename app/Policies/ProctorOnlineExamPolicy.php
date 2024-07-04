<?php

namespace App\Policies;

use App\Enums\RoleEnum;
use App\Helpers\ValidateHelper;

class ProctorOnlineExamPolicy
{
    public function startStudentOnlineExam(): bool
    {
        $validRoles = [
            RoleEnum::SYSTEM_ADMINISTRATOR,
            RoleEnum::PROCTOR
        ];
        return ValidateHelper::validatePolicy($validRoles);
    }

    public function suspendStudentOnlineExam(): bool
    {
        $validRoles = [
            RoleEnum::SYSTEM_ADMINISTRATOR,
            RoleEnum::PROCTOR
        ];
        return ValidateHelper::validatePolicy($validRoles);
    }

    public function continueStudentOnlineExam(): bool
    {
        $validRoles = [
            RoleEnum::SYSTEM_ADMINISTRATOR,
            RoleEnum::PROCTOR
        ];
        return ValidateHelper::validatePolicy($validRoles);
    }

    public function finishStudentOnlineExam(): bool
    {
        $validRoles = [
            RoleEnum::SYSTEM_ADMINISTRATOR,
            RoleEnum::PROCTOR
        ];
        return ValidateHelper::validatePolicy($validRoles);
    }

    public function retrieveOnlineExam(): bool
    {
        $validRoles = [
            RoleEnum::SYSTEM_ADMINISTRATOR,
            RoleEnum::PROCTOR
        ];
        return ValidateHelper::validatePolicy($validRoles);
    }

    public function retrieveOnlineExams(): bool
    {
        $validRoles = [
            RoleEnum::SYSTEM_ADMINISTRATOR,
            RoleEnum::PROCTOR,
            RoleEnum::PROCTOR
        ];
        return ValidateHelper::validatePolicy($validRoles);
    }
    
    public function retrieveOnlineExamStudents(): bool
    {
        $validRoles = [
            RoleEnum::SYSTEM_ADMINISTRATOR,
            RoleEnum::PROCTOR,
            RoleEnum::PROCTOR
        ];
        return ValidateHelper::validatePolicy($validRoles);
    }
    
    public function refreshOnlineExamStudents(): bool
    {
        $validRoles = [
            RoleEnum::SYSTEM_ADMINISTRATOR,
            RoleEnum::PROCTOR
        ];
        return ValidateHelper::validatePolicy($validRoles);
    }

}
