<?php

namespace App\Enums;
use App\Traits\EnumTraits;
use Kongulov\Traits\InteractWithEnum;
enum LevelsEnum: int {
    use InteractWithEnum;
    use EnumTraits;

    case FIRST = 1;
    case SECOND = 2;
    case THIRD = 3;
    case FORTH =  4;
    case FIFTH =  5;
    case SIXTH =  6;
    case SEVENTH =  7;

    public function getValues(): array {
        return match ($this) {
            self::FIRST => [1, 'First', 'الاول'],
            self::SECOND => [2, 'Second', 'الثاني'],
            self::THIRD => [3, 'Third', 'الثالث'],
            self::FORTH => [4, 'Forth', 'الرابع '],
            self::FIFTH => [5, 'Fifth', ' الخامس'],
            self::SIXTH => [6, 'Sixth', ' السادس'],
            self::SEVENTH => [7, 'Seventh', ' السابع'],
        };
    }

    public static function getValuesByLevesCount($levelsCount): array {

       $levels = [
        [
           'id' => LevelsEnum::FIRST,
           'name' => 'الاول',
       ],
        [
           'id' => LevelsEnum::SECOND,
           'name' => 'الثاني',
       ],
        [
           'id' => LevelsEnum::THIRD,
           'name' => 'الثالث',
       ],
        [
           'id' => LevelsEnum::FORTH,
           'name' => 'الرابع',
       ],
        [
           'id' => LevelsEnum::FIFTH,
           'name' => 'الخامس',
       ],
        [
           'id' => LevelsEnum::SIXTH,
           'name' => 'السادس',
       ],
        [
           'id' => LevelsEnum::SEVENTH,
           'name' => 'السابع',
       ]
        ];
        $temp = [];
       for ($i=0; $i < $levelsCount; $i++) { 
        array_push($temp, $levels[$i]);
       }
       return $temp;
    }

}
