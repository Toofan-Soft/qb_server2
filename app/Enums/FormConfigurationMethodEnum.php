<?php

namespace App\Enums;
use App\Traits\EnumTraits;
use Kongulov\Traits\InteractWithEnum;
enum FormConfigurationMethodEnum: int {
    use InteractWithEnum;
    use EnumTraits;

    case DIFFERENT_FORMS  = 0;
    case SIMILAR_FORMS  =  1;
    case DIFFERENT_FORMS_IN_QUESTIONS_ORDER  =  2;

    public function getValues(): array {
        return match ($this) {
            self::DIFFERENT_FORMS => [0, 'Different Forms', ' نماذج مختلفه'],
            self::SIMILAR_FORMS => [1, 'Similar Forms', ' نماذج متشابهه'],
            self::DIFFERENT_FORMS_IN_QUESTIONS_ORDER => [2, ' Different Forms in Questions Order', ' نماذج مختلفة في ترتيب الاسئله'],
        };
    }


}
