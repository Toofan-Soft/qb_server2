<?php
namespace App\Enums;
use App\Traits\EnumTraits;
use Kongulov\Traits\InteractWithEnum;

enum ExamDifficultyLevelEnum: int {
    use InteractWithEnum;
    use EnumTraits;

    case VERY_DIFFICULT  = 0;
    case DIFFICULT  =  1;
    case MODERATE  =  2;
    case EASY  =  3;
    case VERYEASY  =  4;

    public function getValues(): array {
        return match ($this) {
            self::VERY_DIFFICULT => [0, 'Very difficult', ' ضعب جدا'],
            self::DIFFICULT => [1, 'Difficult', ' صعب'],
            self::MODERATE => [2, 'Moderate', ' متوسط'],
            self::EASY => [3, 'Easy', ' سهل'],
            self::VERYEASY => [4, 'Very Easy', ' سهل جدا'],
        };
    }


}
