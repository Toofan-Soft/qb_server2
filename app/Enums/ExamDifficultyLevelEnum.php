<?php
namespace App\Enums;
use Kongulov\Traits\InteractWithEnum;

enum ExamDifficultyLevelEnum: int {
    use InteractWithEnum;

    case VERY_DIFFICULT  = 0;
    case DIFFICULT  =  1;
    case MODERATE  =  2;
    case EASY  =  3;
    case VERYEASY  =  4;

    public function getValues(): array {
        return match ($this) {
            self::VERY_DIFFICULT => [0, 'Very_difficult', ' ضعب جدا'],
            self::DIFFICULT => [1, 'Difficult', ' صعب'],
            self::MODERATE => [2, 'Moderate', ' متوسط'],
            self::EASY => [3, 'Easy', ' سهل'],
            self::VERYEASY => [4, 'VeryEasy', ' سهل جدا'],
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
