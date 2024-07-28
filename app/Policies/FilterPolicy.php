<?php

namespace App\Policies;

use App\Enums\RoleEnum;
use App\Helpers\ValidateHelper;

class FilterPolicy
{
    public function retrieveCourses(): bool
    {
        return ValidateHelper::validateUser();
        // return ValidateHelper::validatePolicy(self::$validRoles);
    }
    public function retrieveCourseParts(): bool
    {
        return ValidateHelper::validateUser();
        // return ValidateHelper::validatePolicy(self::$validRoles);
    }
    public function retrieveChapters(): bool
    {
        return ValidateHelper::validateUser();
        // return ValidateHelper::validatePolicy(self::$validRoles);
    }
    public function retrieveTopics(): bool
    {
        return ValidateHelper::validateUser();
        // return ValidateHelper::validatePolicy(self::$validRoles);
    }
    public function retrieveColleges(): bool
    {
        return ValidateHelper::validateUser();
        // return ValidateHelper::validatePolicy(self::$validRoles);
    }
    public function retrieveLecturerColleges(): bool
    {
        return ValidateHelper::validatePolicy([RoleEnum::LECTURER->value]);
        // return ValidateHelper::validatePolicy(self::$validRoles);
    }
    public function retrieveLecturerCurrentColleges(): bool
    {
        return ValidateHelper::validatePolicy([RoleEnum::LECTURER->value]);
        // return ValidateHelper::validatePolicy(self::$validRoles);
    }
    public function retrieveDepartments(): bool
    {
        return ValidateHelper::validateUser();
        // return ValidateHelper::validatePolicy(self::$validRoles);
    }
    public function retrieveLecturerDepartments(): bool
    {
        return ValidateHelper::validatePolicy([RoleEnum::LECTURER->value]);
        // return ValidateHelper::validatePolicy(self::$validRoles);
    }
    public function retrieveLecturerCurrentDepartments(): bool
    {
        return ValidateHelper::validatePolicy([RoleEnum::LECTURER->value]);
        // return ValidateHelper::validatePolicy(self::$validRoles);
    }
    public function retrieveDepartmentLevels(): bool
    {
        return ValidateHelper::validateUser();
        // return ValidateHelper::validatePolicy(self::$validRoles);
    }
    public function retrieveDepartmentCourses(): bool
    {
        return ValidateHelper::validateUser();
        // return ValidateHelper::validatePolicy(self::$validRoles);
    }
    public function retrieveDepartmentLevelCourses(): bool
    {
        return ValidateHelper::validateUser();
        // return ValidateHelper::validatePolicy(self::$validRoles);
    }
    public function retrieveDepartmentLevelSemesterCourses(): bool
    {
        return ValidateHelper::validateUser();
        // return ValidateHelper::validatePolicy(self::$validRoles);
    }
    public function retrieveDepartmentCourseParts(): bool
    {
        return ValidateHelper::validateUser();
        // return ValidateHelper::validatePolicy(self::$validRoles);
    }
    public function retrieveDepartmentLecturerCourses(): bool
    {
        return ValidateHelper::validatePolicy([RoleEnum::LECTURER->value]);
        // return ValidateHelper::validatePolicy(self::$validRoles);
    }
    public function retrieveDepartmentLecturerCurrentCourses(): bool
    {
        return ValidateHelper::validatePolicy([RoleEnum::LECTURER->value]);
        // return ValidateHelper::validatePolicy(self::$validRoles);
    }
    public function retrieveDepartmentLecturerCourseParts(): bool
    {
        return ValidateHelper::validatePolicy([RoleEnum::LECTURER->value]);
        // return ValidateHelper::validatePolicy(self::$validRoles);
    }
    public function retrieveDepartmentLecturerCurrentCourseParts(): bool
    {
        return ValidateHelper::validatePolicy([RoleEnum::LECTURER->value]);
        // return ValidateHelper::validatePolicy(self::$validRoles);
    }
    public function retrieveEmployees(): bool
    {
        return ValidateHelper::validateUser();
        // return ValidateHelper::validatePolicy(self::$validRoles);
    }
    public function retrieveLecturers(): bool
    {
        return ValidateHelper::validateUser();
        // return ValidateHelper::validatePolicy(self::$validRoles);
    }
    public function retrieveEmployeesOfJob(): bool
    {
        return ValidateHelper::validateUser();
        // return ValidateHelper::validatePolicy(self::$validRoles);
    }
    // public function retrieveAcademicYears(): bool
    // {
    //     return ValidateHelper::validateUser();
    //     // return ValidateHelper::validatePolicy(self::$validRoles);
    // }
    public function retrieveNonOwnerEmployees(): bool
    {
        return ValidateHelper::validatePolicy([RoleEnum::SYSTEM_ADMINISTRATOR->value]);
        // return ValidateHelper::validatePolicy(self::$validRoles);
    }
    public function retrieveNonOwnerStudents(): bool
    {
        return ValidateHelper::validatePolicy([RoleEnum::SYSTEM_ADMINISTRATOR->value]);
        // return ValidateHelper::validatePolicy(self::$validRoles);
    }
    public function retrieveRoles(): bool
    {
        return ValidateHelper::validateUser();
        // return ValidateHelper::validatePolicy(self::$validRoles);
    }
    public function retrieveProctors(): bool
    {
        return ValidateHelper::validateUser();
        // return ValidateHelper::validatePolicy(self::$validRoles);
    }

}
