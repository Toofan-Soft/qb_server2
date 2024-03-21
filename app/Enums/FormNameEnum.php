<?php

namespace App\Enums;
use Kongulov\Traits\InteractWithEnum;
enum FormNameEnum: int {
    use InteractWithEnum;

    case DECIMAL_NUMBERING   = 0;
    case ROMAN_NUMBERING   =  1;
    case ALPHANUMERIC_NUMBERING   =  2;


    public function getValues(): array {
        return match ($this) {
            self::DECIMAL_NUMBERING => [0, 'Decimal_numbering', ' ترقيم عشري'],
            self::ROMAN_NUMBERING => [1, 'Roman_numbering', ' ترقيم روماني'],
            self::ALPHANUMERIC_NUMBERING => [2, 'Alphanumeric_numbering', ' ترقيم أبجدي'],
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
