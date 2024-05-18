<?php

namespace App\Enums;
use App\Traits\EnumTraits;
use Kongulov\Traits\InteractWithEnum;
enum OnlineExamTakingStatusEnum: int {
    use InteractWithEnum;
    use EnumTraits;

    case INCOMPLETE = 0;
    case COMPLETE = 1;

    public function getValues(): array {
        return match ($this) {
            self::INCOMPLETE => [0, 'Incomplete', '  غير مكتمل'],
            self::COMPLETE => [1, 'Complete', 'مكتمل'],
        };
    }



}
