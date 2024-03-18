<?php

namespace App\Enums;
use Kongulov\Traits\InteractWithEnum;
enum QuestionTypeEnum: int {
    use InteractWithEnum;
    case TRUE_FALSE = 0;
    case MULTIPLE_CHOICE = 1;

    public function getValues(): array {
        return match ($this) {
            self::TRUE_FALSE => [0, 'True_false', 'صح وخطأ'],
            self::MULTIPLE_CHOICE => [1, 'Multiple_choice', 'اختيار متعدد'],
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

