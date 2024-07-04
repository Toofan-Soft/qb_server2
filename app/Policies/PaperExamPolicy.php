<?php

namespace App\Policies;

use App\Enums\RoleEnum;
use App\Helpers\ValidateHelper;

class PaperExamPolicy
{
    public function addPaperExam(): bool
    {
        $validRoles = [
            RoleEnum::SYSTEM_ADMINISTRATOR,
            RoleEnum::LECTURER
        ];
        return ValidateHelper::validatePolicy($validRoles);
    }

    public function modifyPaperExam(): bool
    {
        $validRoles = [
            RoleEnum::SYSTEM_ADMINISTRATOR,
            RoleEnum::LECTURER
        ];
        return ValidateHelper::validatePolicy($validRoles);
    }

    public function deletePaperExam(): bool
    {
        $validRoles = [
            RoleEnum::SYSTEM_ADMINISTRATOR,
            RoleEnum::LECTURER
        ];
        return ValidateHelper::validatePolicy($validRoles);
    }

    public function retrievePaperExam(): bool
    {
        $validRoles = [
            RoleEnum::SYSTEM_ADMINISTRATOR,
            RoleEnum::LECTURER
        ];
        return ValidateHelper::validatePolicy($validRoles);
    }

    public function retrieveEditablePaperExam(): bool
    {
        $validRoles = [
            RoleEnum::SYSTEM_ADMINISTRATOR,
            RoleEnum::LECTURER
        ];
        return ValidateHelper::validatePolicy($validRoles);
    }
    
    public function retrievePaperExams(): bool
    {
        $validRoles = [
            RoleEnum::SYSTEM_ADMINISTRATOR,
            RoleEnum::LECTURER
        ];
        return ValidateHelper::validatePolicy($validRoles);
    }
    
    public function retrievePaperExamsAndroid(): bool
    {
        $validRoles = [
            RoleEnum::SYSTEM_ADMINISTRATOR,
            RoleEnum::LECTURER
        ];
        return ValidateHelper::validatePolicy($validRoles);
    }

    public function retrievePaperExamChapters(): bool
    {
        $validRoles = [
            RoleEnum::SYSTEM_ADMINISTRATOR,
            RoleEnum::LECTURER
        ];
        return ValidateHelper::validatePolicy($validRoles);
    }
    public function retrievePaperExamChapterTopics(): bool
    {
        $validRoles = [
            RoleEnum::SYSTEM_ADMINISTRATOR,
            RoleEnum::LECTURER
        ];
        return ValidateHelper::validatePolicy($validRoles);
    }
    public function retrievePaperExamForms(): bool
    {
        $validRoles = [
            RoleEnum::SYSTEM_ADMINISTRATOR,
            RoleEnum::LECTURER
        ];
        return ValidateHelper::validatePolicy($validRoles);
    }
    public function retrievePaperExamFormQuestions(): bool
    {
        $validRoles = [
            RoleEnum::SYSTEM_ADMINISTRATOR,
            RoleEnum::LECTURER
        ];
        return ValidateHelper::validatePolicy($validRoles);
    }
    public function exportPaperExamToPDF(): bool
    {
        $validRoles = [
            RoleEnum::SYSTEM_ADMINISTRATOR,
            RoleEnum::LECTURER
        ];
        return ValidateHelper::validatePolicy($validRoles);
    }
}
