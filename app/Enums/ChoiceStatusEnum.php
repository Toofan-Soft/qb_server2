<?php

namespace App\Enums;
use App\Traits\EnumTraits;
use Kongulov\Traits\InteractWithEnum;
enum ChoiceStatusEnum: INT {
    use InteractWithEnum;
    use EnumTraits;

    case INCORRECT_ANSWER  = 0;
    case CORRECT_ANSWER  = 1;

    public function getValues(): array {
        return match ($this) {
            self::INCORRECT_ANSWER => [0, 'Incorrect Answer', 'اجابة غير صحيحه '],
            self::CORRECT_ANSWER => [1, 'Correct Answer',  'اجابة صحيحه '],
        };
    }


}
