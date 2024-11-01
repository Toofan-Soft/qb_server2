<?php

namespace App\Enums;
use App\Traits\EnumTraits;
use Kongulov\Traits\InteractWithEnum;
enum CoursePartStatusEnum: int {
    use InteractWithEnum;
    use EnumTraits;

    case UNAVAILABLE = 0;
    case AVAILABLE = 1;

    public function getValues(): array {
        return match ($this) {
            self::UNAVAILABLE => [0, 'Unavailable', 'غير متاح'],
            self::AVAILABLE => [1, 'Available', 'متاح'],
        };
    }


}
