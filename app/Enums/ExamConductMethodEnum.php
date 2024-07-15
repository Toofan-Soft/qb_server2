<?php

namespace App\Enums;
use App\Traits\EnumTraits;
use Kongulov\Traits\InteractWithEnum;

enum ExamConductMethodEnum: int {
    use InteractWithEnum;
    use EnumTraits;

    case MANDATORY  = 0;
    case OPTIONAL  =  1;

    public function getValues(): array {
        return match ($this) {
            self::MANDATORY => [0, 'Mandatory Question Sequence', 'تسلسل اسئلة اجباري'],
            self::OPTIONAL => [1, 'Optional Question Sequence', 'تسلسل اسئلة اختياري'],
        };
    }



}
