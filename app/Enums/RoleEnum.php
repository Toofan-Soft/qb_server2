<?php

namespace App\Enums;

use App\Traits\EnumTraits;
use Kongulov\Traits\InteractWithEnum;

enum RoleEnum: int {
    use InteractWithEnum;
    use EnumTraits;

    case GUEST = 0;
    case STUDENT = 1;
    case LECTURER = 2;
    case QUESTION_ENTRY = 3;
    case QUESTION_REVIEWER = 4;
    case SYSTEM_ADMINISTRATOR = 5;
    case DATA_ENTRY = 6; ////
    case PROCTOR = 7; ////

    public function getValues(): array {
        return match ($this) {
            self::GUEST => [0, 'Guest', 'زائر'],
            self::STUDENT => [1, 'Student', 'طالب'],
            self::LECTURER => [2, 'Lecture', 'محاضر'],
            self::QUESTION_ENTRY => [3, 'Question Entry', 'مدخل سؤال'],
            self::QUESTION_REVIEWER => [4, 'Question Reviewer', 'مراجع سؤال'],
            self::SYSTEM_ADMINISTRATOR => [5, 'System Administrator', 'مدير النظام'],
            self::DATA_ENTRY => [6, 'Data_entry', 'مدخل بيانات'],
            self::PROCTOR => [7, 'Proctor', 'مراقب '],
        };
    }


}

