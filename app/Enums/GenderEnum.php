<?php

namespace App\Enums;
use App\Traits\EnumTraits;
use Kongulov\Traits\InteractWithEnum;
enum GenderEnum: int {
    use InteractWithEnum;
    use EnumTraits;
    case MALE = 0;
    case FEMALE = 1;

    public function getValues(): array {
        return match ($this) {
            self::MALE => [0, 'Male', 'ذكر '],
            self::FEMALE => [1, 'Female', 'انثى'],
        };
    }

}
