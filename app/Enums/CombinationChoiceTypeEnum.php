<?php

namespace App\Enums;
use App\Traits\EnumTraits;
use Kongulov\Traits\InteractWithEnum;
enum CombinationChoiceTypeEnum: int {
    use InteractWithEnum;
    use EnumTraits;

    case ALL  = -1;
    case NOTHING  =  -2;
    case MIX  =  -3;

    // يتم تحديد الجمل التي سيتم عرضها باللغة العربية والانجليزية 
    public function getValues(): array {
        return match ($this) {
            self::ALL => [-1, '', ''],
            self::NOTHING => [-2, '', ''],
            self::MIX => [-3, '', ''],
        };
    }


}