<?php

namespace App\Enums;
use App\Traits\EnumTraits;
use Kongulov\Traits\InteractWithEnum;

enum JobTypeEnum: int {
    use InteractWithEnum;
    use EnumTraits;

    case EMPLOYEE = 0;
    case LECTURER = 1;
    case EMPLOYEE_LECTURE =  2;

    public function getValues(): array {
        return match ($this) {
            self::EMPLOYEE => [0, 'Employee', 'موظف '],
            self::LECTURER => [1, 'Lecturer', 'محاضر'],
            self::EMPLOYEE_LECTURE => [2, 'Employee and Lecture', 'موظف ومحاضر'],

        };
    }


}

