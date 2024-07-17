<?php

namespace App\Enums;
use App\Traits\EnumTraits;
use Kongulov\Traits\InteractWithEnum;

enum PracticeExamStatusEnum: int {
    use InteractWithEnum;
    use EnumTraits;

    case NEW = 0;
    case ACTIVE = 1;
    case COMPLETE = 2;
    case SUSPENDED = 3;

    public function getValues(): array {
        return match ($this) {
            self::NEW => [0, 'New', ' جديد'],
            self::ACTIVE => [1, 'Active', ' نشط'],
            self::COMPLETE => [2, 'Complete', ' مكتمل'],
            self::SUSPENDED => [3, 'Suspended', ' معلق'],
        };
    }


}
