<?php

namespace App\Enums;
use App\Traits\EnumTraits;
use Kongulov\Traits\InteractWithEnum;
enum QualificationEnum: INT {
    use InteractWithEnum;
    use EnumTraits;

    case DIPLOMA = 0;
    case BACHALOR_DEGREE = 1;
    case MASTER_DEGREE = 2;
    case ASSISTANT_PROFESSOR = 3;
    case ASSOCIATE_PROFESSOR = 4;
    case PROFESSOR = 5;

    public function getValues(): array {
        return match ($this) {
            self::DIPLOMA => [0, 'Diploma', 'دبلوم'],
            self::BACHALOR_DEGREE => [1, 'Bachalor Degree', 'بكلاريوس'],
            self::MASTER_DEGREE => [2, 'Master Degree', 'ماجستير'],
            self::ASSISTANT_PROFESSOR => [3, 'Assistant Professor', 'استاذ مشارك '],
            self::ASSOCIATE_PROFESSOR => [4, 'Associate Professor', ' استاذ مساعد '],
            self::PROFESSOR => [5, 'Professor', 'بروفسور '],
        };
    }



}
