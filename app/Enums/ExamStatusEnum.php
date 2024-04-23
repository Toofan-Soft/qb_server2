<?php

namespace App\Enums;
use Kongulov\Traits\InteractWithEnum;

enum ExamStatusEnum: int {
    use InteractWithEnum;
    case ACTIVE = 0;
    case COMPLETE = 1;
    case SUSPENDED =  2;

    public function getValues(): array {
        return match ($this) {
            self::ACTIVE => [0, 'ACTIVE', ' نشط'],
            self::COMPLETE => [1, 'Complete', ' مكتمل'],
            self::SUSPENDED => [2, 'Suspended', ' معلق'],
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
