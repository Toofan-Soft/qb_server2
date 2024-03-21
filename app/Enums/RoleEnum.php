<?php

namespace App\Enums;

use Kongulov\Traits\InteractWithEnum;

enum RoleEnum: int {
    use InteractWithEnum;

    case GUEST = 0;
    case STUDENT = 1;
    case LECTURER = 2;
    case QUESTION_ENTRY = 3;
    case QUESTION_REVIEWER = 4;
    case SYSTEM_ADMINISTRATOR = 5;

    public function getValues(): array {
        return match ($this) {
            self::GUEST => [0, 'Guest', 'زائر'],
            self::STUDENT => [1, 'Student', 'طالب'],
            self::LECTURER => [2, 'Lecture', 'محاضر'],
            self::QUESTION_ENTRY => [3, 'Question Entry', 'مدخل سؤال'],
            self::QUESTION_REVIEWER => [4, 'Question Reviewer', 'مراجع سؤال'],
            self::SYSTEM_ADMINISTRATOR => [5, 'System Administrator', 'مدير النظام'],
        };
    }

    public function getEnglishName(): string {
        return $this->getValues()[1] ?? '';
    }

    public function getArabicName(): string {
        return $this->getValues()[2] ?? '';
    }

    public static function getArabicNames(): array {
        return array_map(fn($enumValue) => $enumValue->getArabicName(), self::cases());
    }

    public static function getEnglishNames(): array {
        return array_map(fn($enumValue) => $enumValue->getEnglishName(), self::cases());
    }

    public static function getNameByNumber(int $number, string $language = 'ar'): ?string
    {
        $roles = self::cases();
        foreach ($roles as $role) {
            if ($role->getValues()[0] === $number) {
                return $language === 'ar' ? $role->getArabicName() : $role->getEnglishName();
            }
        }
        return null;
    }
    public static function getEnum(string $language = 'ar'): array {
        $roles = self::cases();
        $result = [];
        foreach ($roles as $role) {
            $number = $role->getValues()[0];
            $name = $language === 'ar' ? $role->getArabicName() : $role->getEnglishName();
            $result[] = [$number, $name];
        }
        return $result;
    }
}

