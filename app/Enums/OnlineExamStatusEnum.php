<?php

namespace App\Enums;
use App\Traits\EnumTraits;
use Kongulov\Traits\InteractWithEnum;

enum OnlineExamStatusEnum: int {
    use InteractWithEnum;
    use EnumTraits;

    case ACTIVE = 0;
    case COMPLETE = 1;
    case SUSPENDED =  2;

    public function getValues(): array {
        return match ($this) {
            self::ACTIVE => [0, 'Active', ' نشط'],
            self::COMPLETE => [1, 'Complete', ' مكتمل'],
            self::SUSPENDED => [2, 'Suspended', ' معلق'],
        };
    }


}
