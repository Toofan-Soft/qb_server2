<?php

namespace App\Helpers;

use App\Models\Course;
use App\Enums\GenderEnum;
use App\Helpers\ImageHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProcessDataHelper
{

    // public static function enumsConvertIdToName( $data, $enumReplacements )
    // {
    //     foreach ($data as $item) {
    //         foreach ($enumReplacements as $enumReplacement) {
    //             if (isset($item[$enumReplacement->columnName]) && is_numeric($item[$enumReplacement->columnName])) {
    //                 // Proceed with conversion (assuming numeric value)
    //                 $item->{$enumReplacement->columnName} = $enumReplacement->enumClass::getNameByNumber($item->{$enumReplacement->columnName});
    //               }
    //         }
    //     }
    //     return $data;
    // }


    public static function enumsConvertIdToName($data, $enumReplacements)
{
    foreach ($data as $key => $item) {
        foreach ($enumReplacements as $enumReplacement) {
            if (is_array($item)) {
                // Handle array
                if (isset($item[$enumReplacement->columnName]) && is_numeric($item[$enumReplacement->columnName])) {
                    $item[$enumReplacement->columnName] = $enumReplacement->enumClass::getNameByNumber($item[$enumReplacement->columnName]);
                }
            } elseif (is_object($item)) {
                // Handle object
                // if (property_exists($item, $enumReplacement->columnName) && is_numeric($item->{$enumReplacement->columnName})) {
                if (isset($item, $enumReplacement->columnName) && is_numeric($item->{$enumReplacement->columnName})) {
                    $item->{$enumReplacement->columnName} = $enumReplacement->enumClass::getNameByNumber($item->{$enumReplacement->columnName});
                }
            }
        }
        $data[$key] = $item;
    }
    return $data;
}



public static function columnConvertIdToName($data, $columnReplacements)
{
    foreach ($data as $item) {
        foreach ($columnReplacements as $columnReplacement) {
            $row = $columnReplacement->model::find($item->{$columnReplacement->columnName}, [$columnReplacement->modelColumnName]);
            if (isset($row[$columnReplacement->modelColumnName])) {
                $item->{$columnReplacement->columnName} = $row[$columnReplacement->modelColumnName];
            }
        }
    }
    return $data;
}


}
