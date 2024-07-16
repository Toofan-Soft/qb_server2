<?php

namespace App\Enums;
use App\Traits\EnumTraits;
use Kongulov\Traits\InteractWithEnum;
enum LanguageEnum: int {
    use InteractWithEnum;
    use EnumTraits;

    case ARABIC = 0;
    case ENGLISH = 1;

    public function getValues(): array {
        return match ($this) {
            self::ARABIC => [0, 'Arabic', 'العربيه '],
            self::ENGLISH => [1, 'English', ' الانجليزية'],
        };
    }

    public static function symbolOf(int $value) {
        if ($value === LanguageEnum::ARABIC->value) {
            return 'ar';
        } elseif ($value === LanguageEnum::ENGLISH->value) {
            return 'en';
        } else {
            return null;
        }
    }
}
