<?php

namespace App\Enums;
use App\Traits\EnumTraits;
use Kongulov\Traits\InteractWithEnum;
enum RealExamTypeEnum: int {
    use InteractWithEnum;
    use EnumTraits;

    case PAPER = 0;
    case ONLINE = 1;

    public function getValues(): array {
        return match ($this) {
            self::PAPER => [0, 'Paper', ' ورقي'],
            self::ONLINE => [1, 'Online', ' إلكتروني'],
        };
    }

}

