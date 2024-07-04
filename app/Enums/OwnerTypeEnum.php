<?php

namespace App\Enums;
use App\Traits\EnumTraits;
use Kongulov\Traits\InteractWithEnum;
enum OwnerTypeEnum: INT {
    use InteractWithEnum;
    use EnumTraits;

    case GUEST = 0;
    case STUDENT = 1;
    case LECTURER = 2;
    case EMPLOYEE = 3 ;

    public function getValues(): array {
        return match ($this) {
            self::GUEST => [0, 'Guest', 'زائر'],
            self::STUDENT => [1, 'Student', 'طالب'],
            self::LECTURER => [2, 'Lecturer', 'محاضر'],
            self::EMPLOYEE => [3, 'Employee', 'موظف '],
        };
    }

    public static function getAvailableValues() {
        $types = EnumTraits::getEnum(OwnerTypeEnum::class);

        $availableTypes = [];

        foreach ($types as $type) {
            if ($type['id'] !== OwnerTypeEnum::GUEST->value) {
                $availableTypes[] = $type;
            }
        }

        return $availableTypes;
    }

}
