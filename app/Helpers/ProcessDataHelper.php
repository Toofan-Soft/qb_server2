<?php

namespace App\Helpers;

use stdClass;
use Traversable;
use App\Models\Course;
use App\Enums\GenderEnum;
use App\Traits\EnumTraits;
use App\Helpers\ImageHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
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
    //                 $item->{$enumReplacement->columnName} = $enumReplacement->enumClass::getNameByNumber($item->{$enumReplacement->columnName});
    //               }
    //         }
    //     }
    //     return $data;
    // }


//     public static function enumsConvertIdToName($data, $enumReplacements)
// {
//     foreach ($data as $key => $item) {
//         foreach ($enumReplacements as $enumReplacement) {
//             if (is_array($item)) {
//                 // Handle array
//                 if (isset($item[$enumReplacement->columnName]) && is_numeric($item[$enumReplacement->columnName])) {
//                     $item[$enumReplacement->columnName] = $enumReplacement->enumClass::getNameByNumber($item[$enumReplacement->columnName]);
//                 }
//             } elseif (is_object($item)) {
//                 // Handle object
//                 // if (property_exists($item, $enumReplacement->columnName) && is_numeric($item->{$enumReplacement->columnName})) {
//                 if (isset($item, $enumReplacement->columnName) && is_numeric($item->{$enumReplacement->columnName})) {
//                     $item->{$enumReplacement->columnName} = $enumReplacement->enumClass::getNameByNumber($item->{$enumReplacement->columnName});
//                 }
//             }

//         }
//         $data[$key] = $item;
//     }

//     return $data;
// }


//handle single object or array of objects:
public static function enumsConvertIdToName($data, $enumReplacements)
{
    // Check if $data is an array or a single object
    $isArray = is_array($data) || $data instanceof Traversable;

    $dataToProcess = $isArray ? $data : [$data];
    foreach ($dataToProcess as $item) {
        foreach ($enumReplacements as $enumReplacement) {
            // if (isset($item[$enumReplacement->columnName]) && is_numeric($item[$enumReplacement->columnName])) {
                // if (property_exists($item, $enumReplacement->columnName) && is_numeric($item->{$enumReplacement->columnName})) {
                // $item->{$enumReplacement->columnName} = $enumReplacement->enumClass::getNameByNumber($item->{$enumReplacement->columnName});
                $item->{$enumReplacement->columnName} =  EnumTraits::getNameByNumber(intval($item->{$enumReplacement->columnName}), $enumReplacement->enumClass);
            // }
        }
    }
    // If $data was a single object, return the modified object
    return $isArray ? $dataToProcess : $dataToProcess[0];
}

// public static function columnConvertIdToName($data, $columnReplacements)
// {
//     foreach ($data as $item) {
//         foreach ($columnReplacements as $columnReplacement) {
//             $row = $columnReplacement->model::find($item->{$columnReplacement->columnName}, [$columnReplacement->modelColumnName]);
//             if (isset($row[$columnReplacement->modelColumnName])) {
//                 $item->{$columnReplacement->columnName} = $row[$columnReplacement->modelColumnName];
//             }
//         }
//     }
//     return $data;
// }

// public static function columnConvertIdToName($data, $columnReplacements)
// {
//     // Check if $data is a collection, array, or a single object
//     $isCollection = $data instanceof  Collection;
//     $isArray = is_array($data) || $data instanceof Traversable;
//     $isSingleObject = is_object($data) && !$isCollection;
//     // Convert $data to an array if it's a single object or collection
//     $dataToProcess = $isSingleObject ? [$data] : ($isCollection ? $data->toArray() : $data);
//     $processedData = [];
//     foreach ($dataToProcess as $item) {
//         if (is_object($item) || is_array($item)) {
//             foreach ($columnReplacements as $columnReplacement) {
//                 $row = $columnReplacement->model::find($item[$columnReplacement->columnName], [$columnReplacement->modelColumnName]);
//                 if ($row && isset($row[$columnReplacement->modelColumnName])) {
//                     $item[$columnReplacement->columnName] = $row[$columnReplacement->modelColumnName];
//                 }
//             }
//             $processedData[] = $item;
//         }
//     }

//     // Convert the processed data back to its original format
//     return $isSingleObject ? $processedData[0] : ($isCollection ? new Collection($processedData) : $processedData);
// }

public static function columnConvertIdToName($data, $columnReplacements) {
    // Check if $data is a collection, array, or a single object
    $isCollection = $data instanceof Collection;
    $isArray = is_array($data) || $data instanceof Traversable;
    $isSingleObject = is_object($data) && !$isCollection;

    // Convert $data to an array if it's a single object or collection
    $dataToProcess = $isSingleObject ? [$data] : ($isCollection ? $data->toArray() : $data);
    $processedData = [];

    foreach ($dataToProcess as $item) {
        if (is_object($item) || is_array($item)) {
            foreach ($columnReplacements as $columnReplacement) {
                $identifier = is_array($item) ? $item[$columnReplacement->columnName] : $item->{$columnReplacement->columnName};
                if($identifier != null){
                    $model = $columnReplacement->model;
                    $row = $model::findOrFail($identifier, [$columnReplacement->modelColumnName]);
    
                    if ($row && isset($row->{$columnReplacement->modelColumnName})) {
                        if (is_array($item)) {
                            $item[$columnReplacement->columnName] = $row->{$columnReplacement->modelColumnName};
                        } else {
                            $item->{$columnReplacement->columnName} = $row->{$columnReplacement->modelColumnName};
                        }
                    }
                }
            }
            $processedData[] = $item;
        }
    }

    // Convert the processed data back to its original format
    return $isSingleObject ? $processedData[0] : ($isCollection ? new Collection($processedData) : $processedData);
}
}
