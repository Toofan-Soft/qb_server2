<?php

namespace App\Policies;

use App\Enums\RoleEnum;
use App\Helpers\ValidateHelper;

class EnumsPolicy
{
    public function retrieveCourseStatus(): bool
    {
        // return ValidateHelper::validatePolicy($validRoles);
        return ValidateHelper::validateUser();
    }
    public function retrieveCourseParts(): bool
    {
        // return ValidateHelper::validatePolicy($validRoles);
        return ValidateHelper::validateUser();
    }
    public function retrieveLanguages(): bool
    {
        // return ValidateHelper::validatePolicy($validRoles);
        return ValidateHelper::validateUser();
    }
    public function retrieveDifficultyLevels(): bool
    {
        // return ValidateHelper::validatePolicy($validRoles);
        return ValidateHelper::validateUser();
    }
    public function retrieveQuestionTypes(): bool
    {
        // return ValidateHelper::validatePolicy($validRoles);
        return ValidateHelper::validateUser();
    }
    public function retrieveQuestionStatus(): bool
    {
        // return ValidateHelper::validatePolicy($validRoles);
        return ValidateHelper::validateUser();
    }
   
    public function retrieveAccessibilityStatus(): bool
    {
        // return ValidateHelper::validatePolicy($validRoles);
        return ValidateHelper::validateUser();
    }
    public function retrieveSemesters(): bool
    {
        // return ValidateHelper::validatePolicy($validRoles);
        return ValidateHelper::validateUser();
    }
    public function retrieveLevelsCounts(): bool
    {
        // return ValidateHelper::validatePolicy($validRoles);
        return ValidateHelper::validateUser();
    }
    public function retrieveJobTypes(): bool
    {
        // return ValidateHelper::validatePolicy($validRoles);
        return ValidateHelper::validateUser();
    }
    public function retrieveQualifications(): bool
    {
        // return ValidateHelper::validatePolicy($validRoles);
        return ValidateHelper::validateUser();
    }
    public function retrieveGenders(): bool
    {
        // return ValidateHelper::validatePolicy($validRoles);
        return ValidateHelper::validateUser();
    }
    public function retrieveCourseStudentStatus(): bool
    {
        // return ValidateHelper::validatePolicy($validRoles);
        return ValidateHelper::validateUser();
    }
    public function retrieveOwnerTypes(): bool
    {
        // return ValidateHelper::validatePolicy($validRoles);
        return ValidateHelper::validateUser();
    }
    public function retrieveUserStatus(): bool
    {
        // return ValidateHelper::validatePolicy($validRoles);
        return ValidateHelper::validateUser();
    }
    public function retrieveConductMethods(): bool
    {
        // return ValidateHelper::validatePolicy($validRoles);
        return ValidateHelper::validateUser();
    }
    public function retrieveExamTypes(): bool
    {
        // return ValidateHelper::validatePolicy($validRoles);
        return ValidateHelper::validateUser();
    }
    public function retrieveformConfigurationMethods(): bool
    {
        // return ValidateHelper::validatePolicy($validRoles);
        return ValidateHelper::validateUser();
    }
    public function retrieveformNameMethods(): bool
    {
        // return ValidateHelper::validatePolicy($validRoles);
        return ValidateHelper::validateUser();
    }
    public function retrieveOnlineExamStatus(): bool
    {
        // return ValidateHelper::validatePolicy($validRoles);
        return ValidateHelper::validateUser();
    }
    public function retrievePracticeExamStatus(): bool
    {
        // return ValidateHelper::validatePolicy($validRoles);
        return ValidateHelper::validateUser();
    }
    public function retrieveStudentOnlineExamStatus(): bool
    {
        // return ValidateHelper::validatePolicy($validRoles);
        return ValidateHelper::validateUser();
    }
    public function retrieveOnlineExamTakingStatus(): bool
    {
        // return ValidateHelper::validatePolicy($validRoles);
        return ValidateHelper::validateUser();
    }

}
