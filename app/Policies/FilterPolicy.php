<?php

namespace App\Policies;

use App\Enums\RoleEnum;
use App\Helpers\ValidateHelper;

class FilterPolicy
{
    private static $validRoles = [
        RoleEnum::SYSTEM_ADMINISTRATOR,
        RoleEnum::DATA_ENTRY,
        RoleEnum::QUESTION_ENTRY
    ];

    public function retrieveCourses(): bool
    {
        return true;
        // return ValidateHelper::validatePolicy(self::$validRoles);
    }
    public function retrieveCourseParts(): bool
    {
        return true;
        // return ValidateHelper::validatePolicy(self::$validRoles);
    }
    public function retrieveChapters(): bool
    {
        return true;
        // return ValidateHelper::validatePolicy(self::$validRoles);
    }
    public function retrieveTopics(): bool
    {
        return true;
        // return ValidateHelper::validatePolicy(self::$validRoles);
    }
    public function retrieveColleges(): bool
    {
        return true;
        // return ValidateHelper::validatePolicy(self::$validRoles);
    }
    public function retrieveLecturerColleges(): bool
    {
        return true;
        // return ValidateHelper::validatePolicy(self::$validRoles);
    }
    public function retrieveLecturerCurrentColleges(): bool
    {
        return true;
        // return ValidateHelper::validatePolicy(self::$validRoles);
    }
    public function retrieveDepartments(): bool
    {
        return true;
        // return ValidateHelper::validatePolicy(self::$validRoles);
    }
    public function retrieveLecturerDepartments(): bool
    {
        return true;
        // return ValidateHelper::validatePolicy(self::$validRoles);
    }
    public function retrieveLecturerCurrentDepartments(): bool
    {
        return true;
        // return ValidateHelper::validatePolicy(self::$validRoles);
    }
    public function retrieveDepartmentLevels(): bool
    {
        return true;
        // return ValidateHelper::validatePolicy(self::$validRoles);
    }
    public function retrieveDepartmentCourses(): bool
    {
        return true;
        // return ValidateHelper::validatePolicy(self::$validRoles);
    }
    public function retrieveDepartmentLevelCourses(): bool
    {
        return true;
        // return ValidateHelper::validatePolicy(self::$validRoles);
    }
    public function retrieveDepartmentLevelSemesterCourses(): bool
    {
        return true;
        // return ValidateHelper::validatePolicy(self::$validRoles);
    }
    public function retrieveDepartmentCourseParts(): bool
    {
        return true;
        // return ValidateHelper::validatePolicy(self::$validRoles);
    }
    public function retrieveDepartmentLecturerCourses(): bool
    {
        return true;
        // return ValidateHelper::validatePolicy(self::$validRoles);
    }
    public function retrieveDepartmentLecturerCurrentCourses(): bool
    {
        return true;
        // return ValidateHelper::validatePolicy(self::$validRoles);
    }
    public function retrieveDepartmentLecturerCourseParts(): bool
    {
        return true;
        // return ValidateHelper::validatePolicy(self::$validRoles);
    }
    public function retrieveDepartmentLecturerCurrentCourseParts(): bool
    {
        return true;
        // return ValidateHelper::validatePolicy(self::$validRoles);
    }
    public function retrieveEmployees(): bool
    {
        return true;
        // return ValidateHelper::validatePolicy(self::$validRoles);
    }
    public function retrieveLecturers(): bool
    {
        return true;
        // return ValidateHelper::validatePolicy(self::$validRoles);
    }
    public function retrieveEmployeesOfJob(): bool
    {
        return true;
        // return ValidateHelper::validatePolicy(self::$validRoles);
    }
    public function retrieveAcademicYears(): bool
    {
        return true;
        // return ValidateHelper::validatePolicy(self::$validRoles);
    }
    public function retrieveOwners(): bool
    {
        return true;
        // return ValidateHelper::validatePolicy(self::$validRoles);
    }
    public function retrieveRoles(): bool
    {
        return true;
        // return ValidateHelper::validatePolicy(self::$validRoles);
    }
    public function retrieveProctors(): bool
    {
        return true;
        // return ValidateHelper::validatePolicy(self::$validRoles);
    }

}
