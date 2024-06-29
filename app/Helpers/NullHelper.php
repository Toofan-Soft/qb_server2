<?php

namespace App\Helpers;

use App\Models\User;
use App\Enums\RoleEnum;
use App\Enums\GenderEnum;
use App\Enums\JobTypeEnum;
use Illuminate\Support\Str;
use App\Enums\OwnerTypeEnum;
use App\Helpers\ImageHelper;
use Illuminate\Http\Request;
use App\Enums\UserStatusEnum;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Notifications\EmaiVerificationNotification;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class NullHelper
{
    // public static function filter($value)
    // {
    //     return $value->map(function ($item) {
    //         $itemArray = $item->toArray();
            
    //         foreach ($itemArray as $key => $value) {
    //             if (is_null($value)) {
    //                 unset($itemArray[$key]);
    //             }
    //         }
        
    //         return $itemArray;
    //     });
    // }

    // public static function filter($value)
    // {
    //     if (is_array($value)) {
    //         // return $value;
    //         // $item = $value[0];
    //         // if (is_array($item)) {
    //         //     // return $item;
    //         //     return self::filterArray($item);
    //         // } elseif (is_object($item)) {
    //         //     return 4;
    //         //     // return self::filterObject($item);
    //         // } else {
    //         //     return 2;
    //         //     // return !is_null($item);
    //         // }

    //         // return $value->map(function ($item) {
    //         //     $itemArray = $item->toArray();
    //         //     return itemArray;

    //         //     foreach ($itemArray as $value) {
    //         //         if (is_null($value)) {
    //         //             unset($itemArray[$key]);
    //         //         }
    //         //     }
            
    //         //     return $itemArray;
    //         // });
             
    //         return array_filter($value, function ($item) {
    //             if (is_array($item)) {
    //                 // return 5;
    //                 return self::filterArray($item);
    //             } elseif (is_object($item)) {
    //                 // return 4;
    //                 return self::filterObject($item);
    //             } else {
    //                 // return 2;
    //                 return !is_null($item);
    //             }
    //         });
    //     } elseif (is_object($value)) {
    //         return 3;

    //         return self::filterObject($value);
    //     } else {
    //         throw new \InvalidArgumentException("Value must be an array or object");
    //     }
    // }

    // private static function filterArray(array $array)
    // {
    //     return array_filter($array, function ($value) {
    //         if (is_array($value)) {
    //             return self::filterArray($value);
    //         } elseif (is_object($value)) {
    //             return self::filterObject($value);
    //         } else {
    //             return !is_null($value);
    //         }
    //     });
    // }

    public static function filter($value)
    {
        if (is_array($value)) {
            return self::filterArray($value);
        } elseif (is_object($value)) {
            return self::filterObject($value);
        } else {
            throw new \InvalidArgumentException("Value must be an array or object");
        }
    }
    
    private static function filterArray(array $array)
    {
        $filteredArray = [];

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $filteredValue = self::filterArray($value);
            } elseif (is_object($value)) {
                $filteredValue = self::filterObject($value);
            } else {
                $filteredValue = $value;
            }

            if (!is_null($filteredValue)) {
                $filteredArray[$key] = $filteredValue;
            }
        }

        return $filteredArray;
    }

    private static function filterObject($object)
    {
        $array = (array) $object;
        $filteredArray = self::filterArray($array);
        return (object) $filteredArray;
    }
}
