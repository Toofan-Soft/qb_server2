<?php

namespace App\Enums;
use Kongulov\Traits\InteractWithEnum;
enum LevelsEnum: int {
    use InteractWithEnum;
    case FIRST = 1;
    case SECOND = 2;
    case THIRD = 3;
    case FORTH =  4;
    case FIFTH =  5;
    case SIXTH =  6;
    case SEVENTH =  7;

    public function getValues(): array {
        return match ($this) {
            self::FIRST => [1, 'First', 'الاول'],
            self::SECOND => [2, 'Second', 'الثاني'],
            self::THIRD => [3, 'Third', 'الثالث'],
            self::FORTH => [4, 'Forth', 'الرابع '],
            self::FIFTH => [5, 'Fifth', ' الخامس'],
            self::SIXTH => [6, 'Sixth', ' السادس'],
            self::SEVENTH => [7, 'Seventh', ' السابع'],
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
