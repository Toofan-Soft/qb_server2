<?php

namespace App\Enums;

use App\Helpers\LanguageHelper;
use App\Traits\EnumTraits;
use Kongulov\Traits\InteractWithEnum;

enum AppreciationEnum: int
{
    use InteractWithEnum;
    use EnumTraits;

    case EXCELLENT = 0;
    case VERY_GOOD = 1;
    case GOOD = 2;
    case ACCEPTABLE = 3; // SATISFACTORY
    case POOR = 4; // WEAK
    // Excellent EXCELLENT
    // Very Good VERY_GOOD
    // Good GOOD
    // Satisfactory SATISFACTORY
    // acceptable ACCEPTABLE
    // weak WEAK
    // Poor POOR
    
    public function getValues(): array
    {
        return match ($this) {
            self::EXCELLENT => [0, 'Excellent', 'ممتاز'],
            self::VERY_GOOD => [1, 'Very Good', 'جيد جدا'],
            self::GOOD => [2, 'Good', 'جيد'],
            self::ACCEPTABLE => [3, 'Acceptable', 'مقبول'],
            self::POOR => [4, 'Poor', 'ضعيف'],
        };
    }

    public static function getScoreRateAppreciation(float $scoreRate, string $language): string
    {
        if($scoreRate >= 90){
            $appreciation = EnumTraits::getNameByNumber(AppreciationEnum::EXCELLENT->value, AppreciationEnum::cases(), $language);
        }elseif(($scoreRate >= 80) && ($scoreRate < 90)){
            $appreciation = EnumTraits::getNameByNumber(AppreciationEnum::VERY_GOOD->value, AppreciationEnum::cases(), $language);
        }elseif(($scoreRate >= 70) && ($scoreRate < 80)){
            $appreciation = EnumTraits::getNameByNumber(AppreciationEnum::GOOD->value, AppreciationEnum::cases(), $language);
        }elseif(($scoreRate >= 60) && ($scoreRate < 70)){
            $appreciation = EnumTraits::getNameByNumber(AppreciationEnum::ACCEPTABLE->value, AppreciationEnum::cases(), $language);
        }else{
            $appreciation = EnumTraits::getNameByNumber(AppreciationEnum::POOR->value, AppreciationEnum::cases(), $language);
        }
        
        return $appreciation;
    }
}
