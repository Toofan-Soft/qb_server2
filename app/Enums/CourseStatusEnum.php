<?php

namespace App\Enums;
use Kongulov\Traits\InteractWithEnum;
enum CourseStatusEnum: int {
    use InteractWithEnum;
    case UNAVAILABLE = 0;
    case AVAILABLE = 1;

    public function getValues(): array {
        return match ($this) {
            self::UNAVAILABLE => [0, 'UNAVAILABLE', 'غير متاح'],
            self::AVAILABLE => [1, 'AVAILABLE', 'متاح'],
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
