<?php

namespace App\Enums;
use App\Traits\EnumTraits;
use App\Helpers\LanguageHelper;
use Kongulov\Traits\InteractWithEnum;
enum OwnerTypeEnum: INT {
    use InteractWithEnum;
    use EnumTraits;

    case GUEST = 0;
    case STUDENT = 1;
    case EMPLOYEE = 2;

    public function getValues(): array {
        return match ($this) {
            self::GUEST => [0, 'Guest', 'زائر'],
            self::STUDENT => [1, 'Student', 'طالب'],
            self::EMPLOYEE => [2, 'Employee', 'موظف '],
        };
    }

    public static function getAvailableValues() {
        $types = EnumTraits::getEnum(OwnerTypeEnum::class, LanguageHelper::getEnumLanguageName());

        $availableTypes = [];

        foreach ($types as $type) {
            if ($type['id'] !== OwnerTypeEnum::GUEST->value) {
                $availableTypes[] = $type;
            }
        }

        return $availableTypes;
    }

}
