<?php

namespace App\Enums;
use App\Traits\EnumTraits;
use Kongulov\Traits\InteractWithEnum;
enum FormNameMethodEnum: int {
    use InteractWithEnum;
    use EnumTraits;

    case DECIMAL_NUMBERING   = 0;
    case ROMAN_NUMBERING   =  1;
    case ALPHANUMERIC_NUMBERING   =  2;


    public function getValues(): array {
        return match ($this) {
            self::DECIMAL_NUMBERING => [0, 'Decimal numbering', ' ترقيم عشري'],
            self::ROMAN_NUMBERING => [1, 'Roman numbering', ' ترقيم روماني'],
            self::ALPHANUMERIC_NUMBERING => [2, 'Alphanumeric numbering', ' ترقيم أبجدي'],
        };
    }


}
