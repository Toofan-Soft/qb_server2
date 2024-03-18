<?php

namespace App\Enums;
use Kongulov\Traits\InteractWithEnum;
enum LevelsCountEnum: int {
    use InteractWithEnum;
    case TOW = 2;
    case THREE = 3;
    case FOUR =  4;
    case FIVE =  5;
    case SIX =  6;
    case SEVEN =  7;

    public function getValues(): array {
        return match ($this) {
            self::TOW => [2, 'TOW', 'اثنين'],
            self::THREE => [3, 'Three', 'ثلاثة'],
            self::FOUR => [4, 'Four', 'أربعه'],
            self::FIVE => [5, 'FIVE', 'خمسة '],
            self::SIX => [6, 'SIX', ' ستة'],
            self::SEVEN => [7, 'SEVEN', ' سبعة'],
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
