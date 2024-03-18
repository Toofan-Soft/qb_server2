<?php

namespace App\Enums;
use Kongulov\Traits\InteractWithEnum;
enum QualificationEnum: INT {
    use InteractWithEnum;
    case DIPLOMA = 0;
    case BACHALOR = 1;
    case MASTER_DEGREE = 2;
    case ASSISTANT_PROFESSOR = 3;
    case ASSOCIATE_PROFESSOR = 4;
    case PROFESSOR = 5;

    public function getValues(): array {
        return match ($this) {
            self::DIPLOMA => [0, 'Guest', 'دبلوم'],
            self::BACHALOR => [1, 'Bachalor', 'بكلاريوس'],
            self::MASTER_DEGREE => [2, 'Master_degree', 'ماجستير'],
            self::ASSISTANT_PROFESSOR => [3, 'Assistant_professor', 'استاذ مشارك '],
            self::ASSOCIATE_PROFESSOR => [4, 'Associate_professor', ' استاذ مساعد '],
            self::PROFESSOR => [5, 'Professor', 'بروفسور '],
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
