<?php

namespace App\Enums;
use App\Traits\EnumTraits;
use Kongulov\Traits\InteractWithEnum;
enum StudentOnlineExamStatusEnum: int {
    use InteractWithEnum;
    use EnumTraits;
    case ACTIVE = 0;
    case SUSPENDED = 1;
    case CANCELED = 2;
    case COMPLETE =  3;

    public function getValues(): array {
        return match ($this) {
            self::ACTIVE => [0, 'Active', '  نشط'],
            self::SUSPENDED => [1, 'Suspend', 'معلق'],
            self::CANCELED => [2, 'Canceled', 'ملغي'],
            self::COMPLETE => [3, 'Complete', 'مكتمل'],
        };
    }


}
