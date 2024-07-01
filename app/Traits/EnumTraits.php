<?php
namespace App\Traits;


trait EnumTraits
{
    public function getValues(): array {
        return match ($this) {
            default => [],
        };
    }

    public function getEnglishName(): string {
        return $this->getValues()[1] ?? '';
    }

    public function getArabicName(): string {
        return $this->getValues()[2] ?? '';
    }

    public static function getArabicNames($enumClass): array {
        return array_map(fn($enumValue) => $enumValue->getArabicName(), $enumClass::cases());
    }

    public static function getEnglishNames($enumClass): array {
        return array_map(fn($enumValue) => $enumValue->getEnglishName(), $enumClass::cases());
    }

    public static function getNameByNumber(int $number, $enumClass, string $language = 'ar'): ?string {
        $cases = $enumClass::cases();
        foreach ($cases as $case) {
            if ($case->getValues()[0] === $number) {
                return $language === 'ar' ? $case->getArabicName() : $case->getEnglishName();
            }
        }
        return 'null';
        return null;
    }

    public static function getEnum( $enumClass, string $language = 'ar',): array {
        $cases = $enumClass::cases();
        $result = [];
        foreach ($cases as $case) {
            $number = $case->getValues()[0];
            $name = $language === 'ar' ? $case->getArabicName() : $case->getEnglishName();
            array_push(  $result, [
                'id'=> $number,
                'name'=> $name,
            ]);
        }
        return $result;
    }
}
