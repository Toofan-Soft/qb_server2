<?php

namespace App\Enums;

use App\Helpers\LanguageHelper;
use App\Traits\EnumTraits;
use Kongulov\Traits\InteractWithEnum;

enum RoleEnum: int
{
    use InteractWithEnum;
    use EnumTraits;

    case GUEST = 0;
    case STUDENT = 1;
    case LECTURER = 2;
    case QUESTION_ENTRY = 3;
    case QUESTION_REVIEWER = 4;
    case SYSTEM_ADMINISTRATOR = 5;
    case DATA_ENTRY = 6; ////
    case PROCTOR = 7; ////

    public function getValues(): array
    {
        return match ($this) {
            self::GUEST => [0, 'Guest', 'زائر'],
            self::STUDENT => [1, 'Student', 'طالب'],
            self::LECTURER => [2, 'Lecture', 'محاضر'],
            self::QUESTION_ENTRY => [3, 'Question Entry', 'مدخل سؤال'],
            self::QUESTION_REVIEWER => [4, 'Question Reviewer', 'مراجع سؤال'],
            self::SYSTEM_ADMINISTRATOR => [5, 'System Administrator', 'مدير النظام'],
            self::DATA_ENTRY => [6, 'Data Entry', 'مدخل بيانات'],
            self::PROCTOR => [7, 'Proctor', 'مراقب '],
        };
    }

    public static function getOwnerRoles(int $ownerTypeId): array
    {
        $rolesIds = match ($ownerTypeId) {
            OwnerTypeEnum::GUEST->value => [RoleEnum::GUEST->value],
            OwnerTypeEnum::STUDENT->value => [RoleEnum::STUDENT->value],
            OwnerTypeEnum::EMPLOYEE->value => [
                RoleEnum::LECTURER->value,
                RoleEnum::QUESTION_ENTRY->value,
                RoleEnum::QUESTION_REVIEWER->value,
                RoleEnum::SYSTEM_ADMINISTRATOR->value,
                RoleEnum::DATA_ENTRY->value,
                RoleEnum::PROCTOR->value
            ]
        };

        $roles = collect(EnumTraits::getEnum(RoleEnum::class, LanguageHelper::getEnumLanguageName()));

        $filteredRoles = $roles->filter(function ($role) use ($rolesIds) {
            return in_array($role['id'], $rolesIds);
        })->values()->toArray();

        return $filteredRoles;
    }
    public static function getOwnerRolesWithMandatory(int $ownerTypeId, int $jobTypeId = null): array
    {
        $roles = match ($ownerTypeId) {
            OwnerTypeEnum::GUEST->value => [[
                'id' => RoleEnum::GUEST->value,
                'name' => EnumTraits::getNameByNumber(RoleEnum::GUEST->value, RoleEnum::class, LanguageHelper::getEnumLanguageName(), LanguageHelper::getEnumLanguageName()),
                'is_mandatory' => true
            ]],
            OwnerTypeEnum::STUDENT->value => [[
                'id' => RoleEnum::STUDENT->value,
                'name' => EnumTraits::getNameByNumber(RoleEnum::STUDENT->value, RoleEnum::class, LanguageHelper::getEnumLanguageName()),
                'is_mandatory' => true
            ]],
            OwnerTypeEnum::EMPLOYEE->value => match ($jobTypeId) {
                JobTypeEnum::LECTURER->value => [
                    [
                        'id' => RoleEnum::LECTURER->value,
                        'name' => EnumTraits::getNameByNumber(RoleEnum::LECTURER->value, RoleEnum::class, LanguageHelper::getEnumLanguageName()),
                        'is_mandatory' => true
                    ],
                    [
                        'id' => RoleEnum::QUESTION_ENTRY->value,
                        'name' => EnumTraits::getNameByNumber(RoleEnum::QUESTION_ENTRY->value, RoleEnum::class, LanguageHelper::getEnumLanguageName()),
                        'is_mandatory' => false
                    ],
                    [
                        'id' => RoleEnum::PROCTOR->value,
                        'name' => EnumTraits::getNameByNumber(RoleEnum::PROCTOR->value, RoleEnum::class, LanguageHelper::getEnumLanguageName()),
                        'is_mandatory' => false
                    ]
                ],
                JobTypeEnum::EMPLOYEE_LECTURE->value => [
                    [
                        'id' => RoleEnum::LECTURER->value,
                        'name' => EnumTraits::getNameByNumber(RoleEnum::LECTURER->value, RoleEnum::class, LanguageHelper::getEnumLanguageName()),
                        'is_mandatory' => true
                    ],
                    [
                        'id' => RoleEnum::QUESTION_REVIEWER->value,
                        'name' => EnumTraits::getNameByNumber(RoleEnum::QUESTION_REVIEWER->value, RoleEnum::class, LanguageHelper::getEnumLanguageName()),
                        'is_mandatory' => false
                    ],
                    [
                        'id' => RoleEnum::QUESTION_ENTRY->value,
                        'name' => EnumTraits::getNameByNumber(RoleEnum::QUESTION_ENTRY->value, RoleEnum::class, LanguageHelper::getEnumLanguageName()),
                        'is_mandatory' => false
                    ],
                    [
                        'id' => RoleEnum::PROCTOR->value,
                        'name' => EnumTraits::getNameByNumber(RoleEnum::PROCTOR->value, RoleEnum::class, LanguageHelper::getEnumLanguageName()),
                        'is_mandatory' => true
                    ],
                    [
                        'id' => RoleEnum::SYSTEM_ADMINISTRATOR->value,
                        'name' => EnumTraits::getNameByNumber(RoleEnum::SYSTEM_ADMINISTRATOR->value, RoleEnum::class, LanguageHelper::getEnumLanguageName()),
                        'is_mandatory' => false
                    ],
                    [
                        'id' => RoleEnum::DATA_ENTRY->value,
                        'name' => EnumTraits::getNameByNumber(RoleEnum::DATA_ENTRY->value, RoleEnum::class, LanguageHelper::getEnumLanguageName()),
                        'is_mandatory' => false
                    ]
                ],
                JobTypeEnum::EMPLOYEE->value => [
                    [
                        'id' => RoleEnum::QUESTION_REVIEWER->value,
                        'name' => EnumTraits::getNameByNumber(RoleEnum::QUESTION_REVIEWER->value, RoleEnum::class, LanguageHelper::getEnumLanguageName()),
                        'is_mandatory' => false
                    ],
                    [
                        'id' => RoleEnum::QUESTION_ENTRY->value,
                        'name' => EnumTraits::getNameByNumber(RoleEnum::QUESTION_ENTRY->value, RoleEnum::class, LanguageHelper::getEnumLanguageName()),
                        'is_mandatory' => false
                    ],
                    [
                        'id' => RoleEnum::PROCTOR->value,
                        'name' => EnumTraits::getNameByNumber(RoleEnum::PROCTOR->value, RoleEnum::class, LanguageHelper::getEnumLanguageName()),
                        'is_mandatory' => true
                    ],
                    [
                        'id' => RoleEnum::SYSTEM_ADMINISTRATOR->value,
                        'name' => EnumTraits::getNameByNumber(RoleEnum::SYSTEM_ADMINISTRATOR->value, RoleEnum::class, LanguageHelper::getEnumLanguageName()),
                        'is_mandatory' => false
                    ],
                    [
                        'id' => RoleEnum::DATA_ENTRY->value,
                        'name' => EnumTraits::getNameByNumber(RoleEnum::DATA_ENTRY->value, RoleEnum::class, LanguageHelper::getEnumLanguageName()),
                        'is_mandatory' => false
                    ]
                ]
            }
        };
        return $roles;
    }
}
