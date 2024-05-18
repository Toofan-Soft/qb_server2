<?php

namespace App\Enums;
use App\Traits\EnumTraits;
use Kongulov\Traits\InteractWithEnum;
enum AccessibilityStatusEnum: int {
    use InteractWithEnum;
    use EnumTraits;

    case PRACTICE_EXAM  = 0;
    case REALEXAM  = 1;
    case PRACTICE_REALEXAM  = 2;

    public function getValues(): array {
        return match ($this) {
            self::PRACTICE_EXAM => [0, 'Practice_exam', 'اختبار تجريبي'],
            self::REALEXAM => [1, 'Realexam', 'اختبار فعلي'],
            self::PRACTICE_REALEXAM => [2, 'Practice_realexam', 'اختبار فعلي وتجريبي'],
        };
    }


}
