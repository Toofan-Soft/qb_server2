<?php

namespace App\Enums;
use Kongulov\Traits\InteractWithEnum;
enum QuestionStatusEnum: int {
    use InteractWithEnum;
    case NEW = 0;
    case REQUESTED = 1;
    case ACCEPTED = 2;
    case REJECTED = 3;

    public function getValues(): array {
        return match ($this) {
            self::NEW => [0, 'New', 'جديد'],
            self::REQUESTED => [1, 'Requested', 'تحت المراجعه'],
            self::ACCEPTED => [2, 'Accepted', 'معتمد'],
            self::REJECTED => [3, 'Rejected', 'مرفوض'],
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
