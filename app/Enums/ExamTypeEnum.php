<?php

namespace App\Enums;
use App\Traits\EnumTraits;
use Kongulov\Traits\InteractWithEnum;
enum ExamTypeEnum: int {
    use InteractWithEnum;
    use EnumTraits;

    case MONTHLY  =  0;
    case MIDTERM  = 1;
    case FINAL  =  2;

    public function getValues(): array {
        return match ($this) {
            self::MONTHLY => [0, 'Monthly', ' شهري'],
            self::MIDTERM => [1, 'Midterm', ' نصفي'],
            self::FINAL => [2, 'Final', ' نهائي'],
        };
    }


}
