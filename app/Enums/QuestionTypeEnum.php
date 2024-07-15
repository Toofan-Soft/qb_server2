<?php

namespace App\Enums;
use App\Traits\EnumTraits;
use Kongulov\Traits\InteractWithEnum;
enum QuestionTypeEnum: int {
    use InteractWithEnum;
    use EnumTraits;

    case TRUE_FALSE = 0;
    case MULTIPLE_CHOICE = 1;

    public function getValues(): array {
        return match ($this) {
            self::TRUE_FALSE => [0, 'True False', 'صح وخطأ'],
            self::MULTIPLE_CHOICE => [1, 'Multiple Choice', 'اختيار متعدد'],
        };
    }


}

