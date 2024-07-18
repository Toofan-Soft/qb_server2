<?php

namespace App\Enums;
use App\Traits\EnumTraits;
use Kongulov\Traits\InteractWithEnum;
enum AccessibilityStatusEnum: int {
    use InteractWithEnum;
    use EnumTraits;

    case PRACTICE_EXAM  = 0;
    case REAL_EXAM  = 1;
    case PRACTICE_AND_REAL_EXAM  = 2;

    public function getValues(): array {
        return match ($this) {
            self::PRACTICE_EXAM => [0, 'Practice Exam', 'اختبار تجريبي'],
            self::REAL_EXAM => [1, 'Real Exam', 'اختبار فعلي'],
            self::PRACTICE_AND_REAL_EXAM => [2, 'Practice and Real Exam', 'اختبار فعلي وتجريبي'],
        };
    }


}
