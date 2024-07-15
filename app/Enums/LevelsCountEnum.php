<?php

namespace App\Enums;
use App\Traits\EnumTraits;
use Kongulov\Traits\InteractWithEnum;
enum LevelsCountEnum: int {
    use InteractWithEnum;
    use EnumTraits;

    case TWO = 2;
    case THREE = 3;
    case FOUR =  4;
    case FIVE =  5;
    case SIX =  6;
    case SEVEN =  7;

    public function getValues(): array {
        return match ($this) {
            self::TWO => [2, 'Tow', 'اثنين'],
            self::THREE => [3, 'Three', 'ثلاثة'],
            self::FOUR => [4, 'Four', 'أربعه'],
            self::FIVE => [5, 'Five', 'خمسة '],
            self::SIX => [6, 'Six', ' ستة'],
            self::SEVEN => [7, 'Seve', ' سبعة'],
        };
    }


}
