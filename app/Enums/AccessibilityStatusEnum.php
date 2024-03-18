<?php

namespace App\Enums;
use Kongulov\Traits\InteractWithEnum;
enum AccessibilityStatusEnum: int {
    use InteractWithEnum;
    case PRACTICEEXAM  = 0;
    case REALEXAM  = 1;
    case PRACTICE_REALEXAM  = 2;

    public function getValues(): array {
        return match ($this) {
            self::PRACTICEEXAM => [0, 'Practiceexam', 'اختبار تجريبي'],
            self::REALEXAM => [1, 'Realexam', 'اختبار فعلي'],
            self::PRACTICE_REALEXAM => [2, 'Practice_realexam', 'اختبار فعلي وتجريبي'],
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
}
