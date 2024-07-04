<?php

namespace App\Policies;

use App\Enums\RoleEnum;
use App\Helpers\ValidateHelper;

class LecturerOnlineExamPolicy
{
    public function addOnlineExam(): bool
    {
        $validRoles = [
            RoleEnum::SYSTEM_ADMINISTRATOR,
            RoleEnum::LECTURER
        ];
        return ValidateHelper::validatePolicy($validRoles);
    }

    public function modifyOnlineExam(): bool
    {
        $validRoles = [
            RoleEnum::SYSTEM_ADMINISTRATOR,
            RoleEnum::LECTURER
        ];
        return ValidateHelper::validatePolicy($validRoles);
    }

    public function changeOnlineExamStatus(): bool
    {
        $validRoles = [
            RoleEnum::SYSTEM_ADMINISTRATOR,
            RoleEnum::LECTURER
        ];
        return ValidateHelper::validatePolicy($validRoles);
    }

    public function deleteOnlineExam(): bool
    {
        $validRoles = [
            RoleEnum::SYSTEM_ADMINISTRATOR,
            RoleEnum::LECTURER
        ];
        return ValidateHelper::validatePolicy($validRoles);
    }

    public function retrieveOnlineExam(): bool
    {
        $validRoles = [
            RoleEnum::SYSTEM_ADMINISTRATOR,
            RoleEnum::LECTURER
        ];
        return ValidateHelper::validatePolicy($validRoles);
    }

    public function retrieveEditableOnlineExam(): bool
    {
        $validRoles = [
            RoleEnum::SYSTEM_ADMINISTRATOR,
            RoleEnum::LECTURER
        ];
        return ValidateHelper::validatePolicy($validRoles);
    }
    
    public function retrieveOnlineExams(): bool
    {
        $validRoles = [
            RoleEnum::SYSTEM_ADMINISTRATOR,
            RoleEnum::LECTURER
        ];
        return ValidateHelper::validatePolicy($validRoles);
    }
    
    public function retrieveOnlineExamsAndroid(): bool
    {
        $validRoles = [
            RoleEnum::SYSTEM_ADMINISTRATOR,
            RoleEnum::LECTURER
        ];
        return ValidateHelper::validatePolicy($validRoles);
    }

    public function retrieveOnlineExamChapters(): bool
    {
        $validRoles = [
            RoleEnum::SYSTEM_ADMINISTRATOR,
            RoleEnum::LECTURER
        ];
        return ValidateHelper::validatePolicy($validRoles);
    }
    public function retrieveOnlineExamChapterTopics(): bool
    {
        $validRoles = [
            RoleEnum::SYSTEM_ADMINISTRATOR,
            RoleEnum::LECTURER
        ];
        return ValidateHelper::validatePolicy($validRoles);
    }
    public function retrieveOnlineExamForms(): bool
    {
        $validRoles = [
            RoleEnum::SYSTEM_ADMINISTRATOR,
            RoleEnum::LECTURER
        ];
        return ValidateHelper::validatePolicy($validRoles);
    }
    public function retrieveOnlineExamFormQuestions(): bool
    {
        $validRoles = [
            RoleEnum::SYSTEM_ADMINISTRATOR,
            RoleEnum::LECTURER
        ];
        return ValidateHelper::validatePolicy($validRoles);
    }
}
