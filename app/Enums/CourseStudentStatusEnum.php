<?php

namespace App\Enums;
use App\Traits\EnumTraits;
use Kongulov\Traits\InteractWithEnum;
enum CourseStudentStatusEnum: int {
    use InteractWithEnum;
    use EnumTraits;

    case ACTIVE  = 0;
    case SUSPENDED = 1;
    case PASSED = 2;

    public function getValues(): array {
        return match ($this) {
            self::ACTIVE => [0, 'Active', '  نشط'],
            self::SUSPENDED => [1, 'Suspend', 'معلق'],
            self::PASSED => [2, 'Pass', 'مجتاز'],
        };
    }


}


