<?php

namespace App\Policies;

use App\Enums\RoleEnum;
use App\Helpers\ValidateHelper;

class QuestionChoicePolicy
{
    public function addQuestionChoice(): bool
    {
        $validRoles = [
            RoleEnum::SYSTEM_ADMINISTRATOR,
            RoleEnum::QUESTION_ENTRY
        ];
        return ValidateHelper::validatePolicy($validRoles);
    }

    public function modifyQuestionChoice(): bool
    {
        $validRoles = [
            RoleEnum::SYSTEM_ADMINISTRATOR,
            RoleEnum::QUESTION_ENTRY
        ];
        return ValidateHelper::validatePolicy($validRoles);
    }

    public function deleteQuestionChoice(): bool
    {
        $validRoles = [
            RoleEnum::SYSTEM_ADMINISTRATOR,
            RoleEnum::QUESTION_ENTRY,
            RoleEnum::QUESTION_REVIEWER
        ];
        return ValidateHelper::validatePolicy($validRoles);
    }
    
    public function retrieveEditableQuestionChoice(): bool
    {
        $validRoles = [
            RoleEnum::SYSTEM_ADMINISTRATOR,
            RoleEnum::QUESTION_ENTRY
        ];
        return ValidateHelper::validatePolicy($validRoles);
    }
}
