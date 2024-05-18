<?php

namespace App\Enums;
use App\Traits\EnumTraits;
use Kongulov\Traits\InteractWithEnum;
enum TrueFalseAnswerEnum: int {
    use InteractWithEnum;
    use EnumTraits;
    case  FALSE= 0;
    case  TRUE = 1;

    public function getValues(): array {
        return match ($this) {
            self::FALSE => [0, 'False', '  خطا'],
            self::TRUE => [1, 'True', 'صح'],
        };
    }


}
