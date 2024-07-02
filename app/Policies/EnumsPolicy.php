<?php

namespace App\Policies;

use App\Enums\RoleEnum;
use App\Helpers\ValidateHelper;

class EnumsPolicy
{
    public function retrieveCourseStatus(): bool
    {
        $validRoles = [
            RoleEnum::SYSTEM_ADMINISTRATOR,
        ];
        // return ValidateHelper::validatePolicy($validRoles);
        return true;
    }
    public function retrieveCourseParts(): bool
    {
        $validRoles = [
            RoleEnum::SYSTEM_ADMINISTRATOR,
        ];
        // return ValidateHelper::validatePolicy($validRoles);
        return true;
    }
    public function retrieveLanguages(): bool
    {
        $validRoles = [
            RoleEnum::SYSTEM_ADMINISTRATOR,
        ];
        // return ValidateHelper::validatePolicy($validRoles);
        return true;
    }
    public function retrieveDifficultyLevels(): bool
    {
        $validRoles = [
            RoleEnum::SYSTEM_ADMINISTRATOR,
        ];
        // return ValidateHelper::validatePolicy($validRoles);
        return true;
    }
    public function retrieveQuestionTypes(): bool
    {
        $validRoles = [
            RoleEnum::SYSTEM_ADMINISTRATOR,
        ];
        // return ValidateHelper::validatePolicy($validRoles);
        return true;
    }
    public function retrieveQuestionStatus(): bool
    {
        $validRoles = [
            RoleEnum::SYSTEM_ADMINISTRATOR,
        ];
        // return ValidateHelper::validatePolicy($validRoles);
        return true;
    }
    public function retrieveAcceptanceStatus(): bool
    {
        $validRoles = [
            RoleEnum::SYSTEM_ADMINISTRATOR,
        ];
        // return ValidateHelper::validatePolicy($validRoles);
        return true;
    }
    public function retrieveAccessibilityStatus(): bool
    {
        $validRoles = [
            RoleEnum::SYSTEM_ADMINISTRATOR,
        ];
        // return ValidateHelper::validatePolicy($validRoles);
        return true;
    }
    public function retrieveSemesters(): bool
    {
        $validRoles = [
            RoleEnum::SYSTEM_ADMINISTRATOR,
        ];
        // return ValidateHelper::validatePolicy($validRoles);
        return true;
    }
    public function retrieveJobTypes(): bool
    {
        $validRoles = [
            RoleEnum::SYSTEM_ADMINISTRATOR,
        ];
        // return ValidateHelper::validatePolicy($validRoles);
        return true;
    }
    public function retrieveQualifications(): bool
    {
        $validRoles = [
            RoleEnum::SYSTEM_ADMINISTRATOR,
        ];
        // return ValidateHelper::validatePolicy($validRoles);
        return true;
    }
    public function retrieveGenders(): bool
    {
        $validRoles = [
            RoleEnum::SYSTEM_ADMINISTRATOR,
        ];
        // return ValidateHelper::validatePolicy($validRoles);
        return true;
    }
    public function retrieveCourseStudentStatus(): bool
    {
        $validRoles = [
            RoleEnum::SYSTEM_ADMINISTRATOR,
        ];
        // return ValidateHelper::validatePolicy($validRoles);
        return true;
    }
    public function retrieveOwnerTypes(): bool
    {
        $validRoles = [
            RoleEnum::SYSTEM_ADMINISTRATOR,
        ];
        // return ValidateHelper::validatePolicy($validRoles);
        return true;
    }
    public function retrieveUserStatus(): bool
    {
        $validRoles = [
            RoleEnum::SYSTEM_ADMINISTRATOR,
        ];
        // return ValidateHelper::validatePolicy($validRoles);
        return true;
    }
    public function retrieveConductMethods(): bool
    {
        $validRoles = [
            RoleEnum::SYSTEM_ADMINISTRATOR,
        ];
        // return ValidateHelper::validatePolicy($validRoles);
        return true;
    }
    public function retrieveExamTypes(): bool
    {
        $validRoles = [
            RoleEnum::SYSTEM_ADMINISTRATOR,
        ];
        // return ValidateHelper::validatePolicy($validRoles);
        return true;
    }
    public function retrieveformConfigurationMethods(): bool
    {
        $validRoles = [
            RoleEnum::SYSTEM_ADMINISTRATOR,
        ];
        // return ValidateHelper::validatePolicy($validRoles);
        return true;
    }
    public function retrieveformNameMethods(): bool
    {
        $validRoles = [
            RoleEnum::SYSTEM_ADMINISTRATOR,
        ];
        // return ValidateHelper::validatePolicy($validRoles);
        return true;
    }
    public function retrieveOnlineExamStatus(): bool
    {
        $validRoles = [
            RoleEnum::SYSTEM_ADMINISTRATOR,
        ];
        // return ValidateHelper::validatePolicy($validRoles);
        return true;
    }
    public function retrieveStudentOnlineExamStatus(): bool
    {
        $validRoles = [
            RoleEnum::SYSTEM_ADMINISTRATOR,
        ];
        // return ValidateHelper::validatePolicy($validRoles);
        return true;
    }
    public function retrieveOnlineExamTakingStatus(): bool
    {
        $validRoles = [
            RoleEnum::SYSTEM_ADMINISTRATOR,
        ];
        // return ValidateHelper::validatePolicy($validRoles);
        return true;
    }

}
