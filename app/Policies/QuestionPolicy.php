<?php

namespace App\Policies;

use App\Enums\RoleEnum;
use App\Helpers\ValidateHelper;

class QuestionPolicy
{
    public function addQuestion(): bool
    {
        return ValidateHelper::validatePolicy([
            RoleEnum::SYSTEM_ADMINISTRATOR->value,
            RoleEnum::QUESTION_ENTRY->value
        ]);
    }

    public function modifyQuestion(): bool
    {
        return ValidateHelper::validatePolicy([
            RoleEnum::SYSTEM_ADMINISTRATOR->value,
            RoleEnum::QUESTION_ENTRY->value
        ]);
    }

    public function submitQuestionReviewRequest(): bool
    {
        return ValidateHelper::validatePolicy([
            RoleEnum::SYSTEM_ADMINISTRATOR->value,
            RoleEnum::QUESTION_ENTRY->value
        ]);
    }
    
    public function withdrawSubmitQuestionReviewRequest(): bool
    {
        return ValidateHelper::validatePolicy([
            RoleEnum::SYSTEM_ADMINISTRATOR->value,
            RoleEnum::QUESTION_ENTRY->value
        ]);
    }

    public function acceptQuestion(): bool
    {
        return ValidateHelper::validatePolicy([
            RoleEnum::SYSTEM_ADMINISTRATOR->value,
            RoleEnum::QUESTION_REVIEWER->value
        ]);
    }

    public function rejectQuestion(): bool
    {
        return ValidateHelper::validatePolicy([
            RoleEnum::SYSTEM_ADMINISTRATOR->value,
            RoleEnum::QUESTION_REVIEWER->value
        ]);
    }

    public function deleteQuestion(): bool
    {
        return ValidateHelper::validatePolicy([
            RoleEnum::SYSTEM_ADMINISTRATOR->value,
            RoleEnum::QUESTION_ENTRY->value
        ]);
    }
    
    public function retrieveQuestion(): bool
    {
        return ValidateHelper::validatePolicy([
            RoleEnum::SYSTEM_ADMINISTRATOR->value,
            RoleEnum::QUESTION_ENTRY->value,
            RoleEnum::QUESTION_REVIEWER->value
        ]);
    }
    
    public function retrieveEditableQuestion(): bool
    {
        return ValidateHelper::validatePolicy([
            RoleEnum::SYSTEM_ADMINISTRATOR->value,
            RoleEnum::QUESTION_ENTRY->value
        ]);
    }

    public function retrieveQuestions(): bool
    {
        return ValidateHelper::validatePolicy([
            RoleEnum::SYSTEM_ADMINISTRATOR->value,
            RoleEnum::QUESTION_ENTRY->value,
            RoleEnum::QUESTION_REVIEWER->value
        ]);
    }
}
