<?php

namespace App\Enums;
use App\Traits\EnumTraits;
use Kongulov\Traits\InteractWithEnum;
enum LevelsCountEnum: int {
    use InteractWithEnum;
    use EnumTraits;

    case TOW = 2;
    case THREE = 3;
    case FOUR =  4;
    case FIVE =  5;
    case SIX =  6;
    case SEVEN =  7;

    public function getValues(): array {
        return match ($this) {
            self::TOW => [2, 'TOW', 'اثنين'],
            self::THREE => [3, 'Three', 'ثلاثة'],
            self::FOUR => [4, 'Four', 'أربعه'],
            self::FIVE => [5, 'FIVE', 'خمسة '],
            self::SIX => [6, 'SIX', ' ستة'],
            self::SEVEN => [7, 'SEVEN', ' سبعة'],
        };
    }


}
