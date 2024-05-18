<?php

namespace App\Enums;
use App\Traits\EnumTraits;
use Kongulov\Traits\InteractWithEnum;
enum QuestionStatusEnum: int {
    use InteractWithEnum;
    use EnumTraits;

    case NEW = 0;
    case REQUESTED = 1;
    case ACCEPTED = 2;
    case REJECTED = 3;

    public function getValues(): array {
        return match ($this) {
            self::NEW => [0, 'New', 'جديد'],
            self::REQUESTED => [1, 'Requested', 'تحت المراجعه'],
            self::ACCEPTED => [2, 'Accepted', 'معتمد'],
            self::REJECTED => [3, 'Rejected', 'مرفوض'],
        };
    }

}
