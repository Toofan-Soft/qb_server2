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
        return $value;
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
        $imageFields = ['image_url', 'attachment', 'logo_url'];
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

            foreach ($imageFields as $field) {
                if ($key === $field) {
                    $filteredArray[$key] = asset($filteredValue);
                }
            }
        }

        return $filteredArray;
    }

    private static function filterObject($object)
    {
        // $array = (array) $object;
        // $array = $object->toArray();
        // $filteredArray = self::filterArray($array);
        // return (object) $filteredArray;

        $array = [];

        if (method_exists($object, 'toArray')) {
            // Handle Eloquent models
            $array = $object->toArray();
        } else {
            // Handle stdClass objects and other objects
            $array = (array) $object;
        }

        $filteredArray = self::filterArray($array);
        return (object) $filteredArray;
    }


    public static function filter1($value)
    {
        return $value;
        if (is_array($value)) {
            // return 5;
            return self::filterArray1($value);
        } elseif (is_object($value)) {
            return self::filterObject1($value);
        } else {
            throw new \InvalidArgumentException("Value must be an array or object");
        }
    }

    private static function filterArray1(array $array)
    {
        $filteredArray = [];

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $filteredValue = self::filterArray1($value);
            } elseif (is_object($value)) {
                $filteredValue = self::filterObject1($value);
            } else {
                $filteredValue = $value;
            }

            if (!is_null($filteredValue)) {
                $filteredArray[$key] = $filteredValue;
            }
        }

        return $filteredArray;
    }

    private static function filterObject1($object)
    {
        // $array = (array) $object;
        // $array = $object->toArray();
        // $filteredArray = self::filterArray($array);
        // return (object) $filteredArray;

        $array = [];

        if (method_exists($object, 'toArray')) {
            // Handle Eloquent models
            $array = $object->toArray();
        } else {
            // Handle stdClass objects and other objects
            $array = (array) $object;
        }

        $filteredArray = self::filterArray($array);
        return (object) $filteredArray;
    }


    public static function is_null($parent, $children)
    {
        foreach ($children as $child) {
            if (!isset($parent[$child]) || is_null($parent[$child]) || $parent[$child] === 'null') {
                return true;
            }
        }
        return false;
    }
}
