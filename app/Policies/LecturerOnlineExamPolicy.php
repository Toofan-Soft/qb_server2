<?php

namespace App\Policies;

use App\Enums\RoleEnum;
use App\Helpers\ValidateHelper;

class LecturerOnlineExamPolicy
{
    public function addOnlineExam(): bool
    {
        return ValidateHelper::validatePolicy([
            RoleEnum::LECTURER->value
        ]);
    }

    public function modifyOnlineExam(): bool
    {
        return ValidateHelper::validatePolicy([
            RoleEnum::LECTURER->value
        ]);
    }

    public function changeOnlineExamStatus(): bool
    {
        return ValidateHelper::validatePolicy([
            RoleEnum::LECTURER->value
        ]);
    }

    public function deleteOnlineExam(): bool
    {
        return ValidateHelper::validatePolicy([
            RoleEnum::LECTURER->value
        ]);
    }

    public function retrieveOnlineExam(): bool
    {
        return ValidateHelper::validatePolicy([
            RoleEnum::LECTURER->value
        ]);
    }

    public function retrieveEditableOnlineExam(): bool
    {
        return ValidateHelper::validatePolicy([
            RoleEnum::LECTURER->value
        ]);
    }
    
    public function retrieveOnlineExams(): bool
    {
        return ValidateHelper::validatePolicy([
            RoleEnum::LECTURER->value
        ]);
    }
    
    public function retrieveOnlineExamsAndroid(): bool
    {
        return ValidateHelper::validatePolicy([
            RoleEnum::LECTURER->value
        ]);
    }

    public function retrieveOnlineExamChapters(): bool
    {
        return ValidateHelper::validatePolicy([
            RoleEnum::LECTURER->value
        ]);
    }
    public function retrieveOnlineExamChapterTopics(): bool
    {
        return ValidateHelper::validatePolicy([
            RoleEnum::LECTURER->value
        ]);
    }
    public function retrieveOnlineExamForms(): bool
    {
        return ValidateHelper::validatePolicy([
            RoleEnum::LECTURER->value
        ]);
    }
    public function retrieveOnlineExamFormQuestions(): bool
    {
        return ValidateHelper::validatePolicy([
            RoleEnum::LECTURER->value
        ]);
    }
}
