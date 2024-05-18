<?php

namespace App\Enums;
use App\Traits\EnumTraits;
use Kongulov\Traits\InteractWithEnum;
enum CoursePartsEnum: int {
    use InteractWithEnum;
    use EnumTraits;

    case THEORETICAL  = 0;
    case PRACTICAL = 1;
    case EXERCISES = 2;

    public function getValues(): array {
        return match ($this) {
            self::THEORETICAL => [0, 'Theoretical', 'نظري'],
            self::PRACTICAL => [1, 'Practical', 'عملي'],
            self::EXERCISES => [2, 'Exercises', 'تمارين'],
        };
    }



}

