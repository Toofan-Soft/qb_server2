<?php

namespace App\Enums;
use Kongulov\Traits\InteractWithEnum;
enum StudentOnlineExamStatusEnum: int {
    use InteractWithEnum;
    case ACTIVE = 0;
    case SUSPENDED = 1;
    case CANCELED = 2;
    case COMPLETE =  3;

    public function getValues(): array {
        return match ($this) {
            self::ACTIVE => [0, 'Active', '  نشط'],
            self::SUSPENDED => [1, 'Suspend', 'معلق'],
            self::CANCELED => [2, 'Canceled', 'ملغي'],
            self::COMPLETE => [3, 'Complete', 'مكتمل'],
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
