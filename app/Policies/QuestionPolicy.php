<?php

namespace App\Policies;

use App\Enums\RoleEnum;
use App\Helpers\ValidateHelper;

class QuestionPolicy
{
    public function addQuestion(): bool
    {
        $validRoles = [
            RoleEnum::SYSTEM_ADMINISTRATOR,
            RoleEnum::QUESTION_ENTRY
        ];
        return ValidateHelper::validatePolicy($validRoles);
    }

    public function modifyQuestion(): bool
    {
        $validRoles = [
            RoleEnum::SYSTEM_ADMINISTRATOR,
            RoleEnum::QUESTION_ENTRY
        ];
        return ValidateHelper::validatePolicy($validRoles);
    }

    public function submitQuestionReviewRequest(): bool
    {
        $validRoles = [
            RoleEnum::SYSTEM_ADMINISTRATOR,
            RoleEnum::QUESTION_ENTRY
        ];
        return ValidateHelper::validatePolicy($validRoles);
    }
    
    public function withdrawSubmitQuestionReviewRequest(): bool
    {
        $validRoles = [
            RoleEnum::SYSTEM_ADMINISTRATOR,
            RoleEnum::QUESTION_ENTRY
        ];
        return ValidateHelper::validatePolicy($validRoles);
    }

    public function acceptQuestion(): bool
    {
        $validRoles = [
            RoleEnum::SYSTEM_ADMINISTRATOR,
            RoleEnum::QUESTION_REVIEWER
        ];
        return ValidateHelper::validatePolicy($validRoles);
    }

    public function rejectQuestion(): bool
    {
        $validRoles = [
            RoleEnum::SYSTEM_ADMINISTRATOR,
            RoleEnum::QUESTION_REVIEWER
        ];
        return ValidateHelper::validatePolicy($validRoles);
    }

    public function deleteQuestion(): bool
    {
        $validRoles = [
            RoleEnum::SYSTEM_ADMINISTRATOR,
            RoleEnum::QUESTION_ENTRY,
            RoleEnum::QUESTION_REVIEWER
        ];
        return ValidateHelper::validatePolicy($validRoles);
    }
    
    public function retrieveQuestion(): bool
    {
        $validRoles = [
            RoleEnum::SYSTEM_ADMINISTRATOR,
            RoleEnum::QUESTION_ENTRY,
            RoleEnum::QUESTION_REVIEWER
        ];
        return ValidateHelper::validatePolicy($validRoles);
    }
    
    public function retrieveEditableQuestion(): bool
    {
        $validRoles = [
            RoleEnum::SYSTEM_ADMINISTRATOR,
            RoleEnum::QUESTION_ENTRY
        ];
        return ValidateHelper::validatePolicy($validRoles);
    }

    public function retrieveQuestions(): bool
    {
        $validRoles = [
            RoleEnum::SYSTEM_ADMINISTRATOR,
            RoleEnum::QUESTION_ENTRY,
            RoleEnum::QUESTION_REVIEWER
        ];
        return ValidateHelper::validatePolicy($validRoles);
    }
}
