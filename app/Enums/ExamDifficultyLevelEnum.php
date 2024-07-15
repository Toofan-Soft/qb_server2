<?php
namespace App\Enums;
use App\Traits\EnumTraits;
use InvalidArgumentException;
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
            self::VERY_DIFFICULT => [0, 'Very Difficult', ' صعب جدا'],
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

    public static function fromFloat(float $value): float {
        if ($value >= 0.8 && $value <= 1.0) {
            return self::VERY_DIFFICULT->value;
        } elseif ($value >= 0.6 && $value < 0.8) {
            return self::DIFFICULT->value;
        } elseif ($value >= 0.4 && $value < 0.6) {
            return self::MODERATE->value;
        } elseif ($value >= 0.2 && $value < 0.4) {
            return self::EASY->value;
        } elseif ($value >= 0.0 && $value < 0.2) {
            return self::VERYEASY->value;
        } else {
            throw new InvalidArgumentException('Invalid enum value provided');
        }
    }

    public static function toFloat(int $value): float {
        if ($value == self::VERY_DIFFICULT->value) {
            return 0.9;
        } else if ($value == self::DIFFICULT->value) {
            return 0.7;
        } else if ($value == self::MODERATE->value) {
            return 0.5;
        } else if ($value == self::EASY->value) {
            return 0.3;
        } else if ($value == self::VERYEASY->value) {
            return 0.1;
        } else {
            throw new InvalidArgumentException('Invalid enum value provided');
        }
    }
}
