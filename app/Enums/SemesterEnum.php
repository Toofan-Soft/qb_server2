<?php

namespace App\Enums;
use App\Traits\EnumTraits;
use Kongulov\Traits\InteractWithEnum;
enum SemesterEnum: INT   {
    use InteractWithEnum;
     use EnumTraits;

    case FIRST = 1;
    case SECOND = 2;

    public function getValues(): array {
        return match ($this) {
            self::FIRST => [1, 'First', 'الاول'],
            self::SECOND => [2, 'Second', 'الثاني'],
        };
    }

     
}
