<?php

namespace App\Policies;

use App\Enums\RoleEnum;
use App\Helpers\ValidateHelper;

class PaperExamPolicy
{
    public function addPaperExam(): bool
    {
        return ValidateHelper::validatePolicy([RoleEnum::LECTURER->value]);
    }

    public function modifyPaperExam(): bool
    {
        return ValidateHelper::validatePolicy([RoleEnum::LECTURER->value]);
    }

    public function deletePaperExam(): bool
    {
        return ValidateHelper::validatePolicy([RoleEnum::LECTURER->value]);
    }

    public function retrievePaperExam(): bool
    {
        return ValidateHelper::validatePolicy([RoleEnum::LECTURER->value]);
    }

    public function retrieveEditablePaperExam(): bool
    {
        return ValidateHelper::validatePolicy([RoleEnum::LECTURER->value]);
    }
    
    public function retrievePaperExams(): bool
    {
        return ValidateHelper::validatePolicy([RoleEnum::LECTURER->value]);
    }
    
    public function retrievePaperExamsAndroid(): bool
    {
        return ValidateHelper::validatePolicy([RoleEnum::LECTURER->value]);
    }

    public function retrievePaperExamChapters(): bool
    {
        return ValidateHelper::validatePolicy([RoleEnum::LECTURER->value]);
    }
    public function retrievePaperExamChapterTopics(): bool
    {
        return ValidateHelper::validatePolicy([RoleEnum::LECTURER->value]);
    }
    public function retrievePaperExamForms(): bool
    {
        return ValidateHelper::validatePolicy([RoleEnum::LECTURER->value]);
    }
    public function retrievePaperExamFormQuestions(): bool
    {
        return ValidateHelper::validatePolicy([RoleEnum::LECTURER->value]);
    }
    public function exportPaperExamToPDF(): bool
    {
        return ValidateHelper::validatePolicy([RoleEnum::LECTURER->value]);
    }
}
