<?php

namespace App\Enums;
use App\Traits\EnumTraits;
use Kongulov\Traits\InteractWithEnum;
enum UserStatusEnum: int {
    use InteractWithEnum;
    use EnumTraits;
    case INACTIVE = 0;
    case ACTIVATED = 1;

    public function getValues(): array {
        return match ($this) {
            self::INACTIVE => [0, 'INACTIVATED', ' غير نشط'],
            self::ACTIVATED => [1, 'ACTIVATED', 'نشط'],
        };
    }


}
