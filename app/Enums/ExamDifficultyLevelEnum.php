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
            self::VERY_DIFFICULT => [0, 'Very difficult', ' صعب جدا'],
            self::DIFFICULT => [1, 'Difficult', ' صعب'],
            self::MODERATE => [2, 'Moderate', ' متوسط'],
            self::EASY => [3, 'Easy', ' سهل'],
            self::VERYEASY => [4, 'Very Easy', ' سهل جدا'],
        };
    }

    // public static function of(int $value): self {
    //     return match(true) {
    //         $value == 0 => self::VERY_DIFFICULT,
    //         $value == 1 => self::DIFFICULT,
    //         $value == 2 => self::MODERATE,
    //         $value == 3 => self::EASY,
    //         $value == 4 => self::VERYEASY,
    //         default => throw new InvalidArgumentException('Invalid value for exam difficulty level'),
    //     };
    // }

    public static function toFloat(int $value): float {
        // if ($value == ExamDifficultyLevelEnum::VERY_DIFFICULT) {
        if ($value == 0) {
            return 0.9;
        } else if ($value == 1) {
            return 0.7;
        } else if ($value == 2) {
            return 0.5;
        } else if ($value == 3) {
            return 0.3;
        } else if ($value == 4) {
            return 0.1;
        } else {
            throw new InvalidArgumentException('Invalid enum value provided');
        }
        
        // return match ($value) {
        //     ExamDifficultyLevelEnum::VERY_DIFFICULT => 0.9,
        //     ExamDifficultyLevelEnum::DIFFICULT => 0.7,
        //     ExamDifficultyLevelEnum::MODERATE => 0.5,
        //     ExamDifficultyLevelEnum::EASY => 0.3,
        //     ExamDifficultyLevelEnum::VERYEASY => 0.1,
        //     default => throw new InvalidArgumentException('Invalid enum value provided')
        // };
    }
}
