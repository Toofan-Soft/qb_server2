<?php

namespace App\Enums;
use App\Traits\EnumTraits;
use Kongulov\Traits\InteractWithEnum;
enum ExamTypeEnum: int {
    use InteractWithEnum;
    use EnumTraits;

    case MIDTERM  = 0;
    case FINAL  =  1;
    case MONTHLY  =  2;

    public function getValues(): array {
        return match ($this) {
            self::MIDTERM => [0, 'Midterm', ' نصفي'],
            self::FINAL => [1, 'Final', ' نهائي'],
            self::MONTHLY => [2, 'Monthly', ' شهري'],
        };
    }


}
