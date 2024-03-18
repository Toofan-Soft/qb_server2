<?php

namespace App\Helpers;

use PhpParser\Node\Stmt\Foreach_;

class GetHelper
{
    ////////FOR GET SPESEFIC ATTRIBUTES OF ANY MODEL ?
    //    public static function getAttributes($model, $attributes = [])
    //    {
    //        $result = [];
    //        if (empty($attributes)) {
    //            $result = $model->toArray(); // If no specific attributes are provided, retrieve all attributes of the model
    //        } else {
    //            foreach ($attributes as $attribute) {
    //                $result[$attribute] = $model->$attribute;
    //            }
    //        }
    //        return $result;
    //    }

    // public static function getAttributes($models, $attributes = [])
    // {
    //     $result = [];

    //     $helper = new GetHelper();
    //     // Handle single model instance
    //     if (!is_iterable($models)) {
    //         $result = $helper->extractModelAttributes($models, $attributes);
    //     } else {
    //         // Handle collection of models
    //         foreach ($models as $model) {
    //             // Extract attributes for each model in the collection
    //             $result[] = $helper->extractModelAttributes($model, $attributes);
    //         }
    //     }
    //     return $result;
    // }


    // function extractModelAttributes($modelInstance, $attributes): array
    // {
    //     $data = [];
    //     foreach ($attributes as $attribute) {
    //         if (property_exists($modelInstance, $attribute)) {
    //             $data[$attribute] = $modelInstance->$attribute;
    //         }
    //     }
    //     return $data;
    // }

    //WORKED
    // public static function retrieveBasicInfo($model, $attributes=[]){
    //     $rows = $model::all($attributes);
    //     foreach ($rows as $row) {
    //         if (isset($row->logo_url)) {
    //             $row->logo_url = asset($row->logo_url);
    //         } elseif (isset($row->image_url)) {
    //             $row->image_url = asset($row->image_url);
    //         }
    //     }
    //     return $rows;
    // }

    // public static function retrieveInfo($model, $attributes = [], $temp = [],$singleRow = false )
    // {
    //     if (empty($attributes)) {
    //         $rows = $model::all();
    //     } else {
    //         $rows = $model::all($attributes);
    //     }
    //     if ($singleRow) {
    //         $row = $model; ///can update here if get by id
    //         if ($row) {
    //             if (isset($row->logo_url)) {
    //                 $row->logo_url = asset($row->logo_url);
    //             } elseif (isset($row->image_url)) {
    //                 $row->image_url = asset($row->image_url);
    //             }
    //         }
    //         return $row;
    //     } else {
    //         // $rows = $model::all($attributes);
    //         foreach ($rows as $row) {
    //             if (isset($row->logo_url)) {
    //                 $row->logo_url = asset($row->logo_url);
    //             } elseif (isset($row->image_url)) {
    //                 $row->image_url = asset($row->image_url);
    //             }
    //         }

    //         return $rows;
    //     }
    // }

    public static function retrieveModels($model, $attributes = null , $conditionAttribute = [] )
    {
        if (empty($conditionAttribute)) {
            if (empty($attributes)) {
                $rows = $model::all();
            }else {
                $rows = $model::all($attributes);
            }
        } else {
            if (empty($attributes)) {
                $rows =$model::where($conditionAttribute[0], $conditionAttribute[1])->get() ;
            }else {
               $rows = $model::where($conditionAttribute[0], $conditionAttribute[1])->get( $attributes);
            }
        }
        foreach ($rows as $row) {
            if (isset($row->logo_url)) {
                $row->logo_url = asset($row->logo_url);
            } elseif (isset($row->image_url)) {
                $row->image_url = asset($row->image_url);
            }
        }

        return response()->json([
            'message' => 'Departments retrieved successfully!', 'data' => $rows,
        ], 200);
    }

    // public static function retrieveModelsWithEnum($model, $attributes = null , $conditionAttribute = [] , $enumAttributes =[] , $enumClasses =[]) {
    public static function retrieveModelsWithEnum($model, $attributes = null , $conditionAttribute = [] , $enumReplacements) {
        if (empty($conditionAttribute)) {
            if (empty($attributes)) {
                $rows = $model::all();
            }else {
                $rows = $model::all($attributes);
            }
        } else {
            if (empty($attributes)) {
                $rows =$model::where($conditionAttribute[0], $conditionAttribute[1])->get() ;
            }else {
               $rows = $model::where($conditionAttribute[0], $conditionAttribute[1])->get( $attributes);
            }
        }
        foreach ($rows as $row) {
            if (isset($row->logo_url)) {
                $row->logo_url = asset($row->logo_url);
            } elseif (isset($row->image_url)) {
                $row->image_url = asset($row->image_url);
            }

            // foreach($enumAttributes as $enumAttribute => $value){
            //    $row[$enumAttribute] = $enumClasses
            //   $part_name = $enum->
            // }

            foreach ($enumReplacements as $replacement) {
                $dbColumnName = $replacement->dbColumnName;
                $newColumnName = $replacement->newColumnName;
                $enumClass = $replacement->enumClass;

                if (isset($row->$dbColumnName)) {
                  $enumValue = $row->$dbColumnName;
                  $enumName = $enumClass::getNameByNumber($enumValue);
                  $row->{$newColumnName} = $enumName;  // add a new column to row and assign the enum name to it
                  unset($row->{$dbColumnName}); // Unset the old column
                }
            }
        }

        return response()->json([
            'message' => 'Departments retrieved successfully!', 'data' => $rows,
        ], 200);
    }

}
