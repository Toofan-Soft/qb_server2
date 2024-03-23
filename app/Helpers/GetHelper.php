<?php

namespace App\Helpers;

use App\Helpers\ResponseHelper;
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

    // public static function retrieveModels($model, $attributes = null , $conditionAttribute = [] )
    // {
    //     if (empty($conditionAttribute)) {
    //         if (empty($attributes)) {
    //             $rows = $model::all();
    //         }else {
    //             $rows = $model::all($attributes);
    //         }
    //     } else {
    //         if (empty($attributes)) {
    //             $rows =$model::where($conditionAttribute[0],  $conditionAttribute[1],  $conditionAttribute[2])->get();
    //         }else {
    //            $rows = $model::where($conditionAttribute[0], $conditionAttribute[1], $conditionAttribute[2] )->get( $attributes);
    //         }
    //     }
    //     foreach ($rows as $row) {
    //         if (isset($row->logo_url)) {
    //             $row->logo_url = asset($row->logo_url);
    //         } elseif (isset($row->image_url)) {
    //             $row->image_url = asset($row->image_url);
    //         }
    //     }

    //     return response()->json([
    //         'message' => 'Departments retrieved successfully!', 'data' => $rows,
    //     ], 200);
    // }

    // public static function retrieveModelsWithEnum($model, $attributes = null , $conditionAttribute = [] , $enumAttributes =[] , $enumClasses =[]) {
    public static function retrieveModels($model, $attributes = null , $conditionAttribute = [] , $enumReplacements = null, $columnReplacements =null) {
        if (empty($conditionAttribute)) {
            if (empty($attributes)) {
                $rows = $model::all();
            }else {
                $rows = $model::all($attributes);
            }
        } else {
            if (empty($attributes)) {
                $rows =$model::where($conditionAttribute[0],  $conditionAttribute[1])->get();
            }else {
               $rows = $model::where($conditionAttribute[0], $conditionAttribute[1])->get( $attributes);
            }
        }

        if($enumReplacements){
            $rows = ProcessDataHelper::enumsConvertIdToName($rows, $enumReplacements);
        }

        if($columnReplacements){
            $rows = ProcessDataHelper::columnConvertIdToName($rows, $columnReplacements);
        }
        foreach ($rows as $row) {
            if (isset($row->logo_url)) {
                $row->logo_url = asset($row->logo_url);
            } elseif (isset($row->image_url)) {
                $row->image_url = asset($row->image_url);
            }
        }
          return ResponseHelper::success();
    }

    public static function retrieveModels2($model, $attributes = null , $conditionAttribute = [] , $enumReplacements = null, $columnReplacements =null) {
        if (empty($conditionAttribute)) {
            if (empty($attributes)) {
                $rows = $model::all();
            }else {
                $rows = $model::all($attributes);
            }
        } else {
            if (empty($attributes)) {
                $rows = $model::where($conditionAttribute[0][0],  $conditionAttribute[0][1])
                ->where($conditionAttribute[1][0], $conditionAttribute[1][1] )->get();
            }else {
               $rows = $model::where($conditionAttribute[0][0],  $conditionAttribute[0][1])
               ->where($conditionAttribute[1][0], $conditionAttribute[1][1] )->get( $attributes);
            }
        }

        if($enumReplacements){
            $rows = ProcessDataHelper::enumsConvertIdToName($rows, $enumReplacements);
        }

        if($columnReplacements){
            $rows = ProcessDataHelper::columnConvertIdToName($rows, $columnReplacements);
        }
        foreach ($rows as $row) {
            if (isset($row->logo_url)) {
                $row->logo_url = asset($row->logo_url);
            } elseif (isset($row->image_url)) {
                $row->image_url = asset($row->image_url);
            }
        }
          return ResponseHelper::success();
    }
    public static function retrieveModels3($model, $attributes = null , $conditionAttribute = [] , $enumReplacements = null, $columnReplacements =null) {
        if (empty($conditionAttribute)) {
            if (empty($attributes)) {
                $rows = $model::all();
            }else {
                $rows = $model::all($attributes);
            }
        } else {
            if (empty($attributes)) {
                $rows = $model::where($conditionAttribute[0][0],  $conditionAttribute[0][1])
                ->where($conditionAttribute[1][0], $conditionAttribute[1][1] )
                ->where($conditionAttribute[2][0], $conditionAttribute[2][1] )
                ->get();
            }else {
               $rows = $model::where($conditionAttribute[0][0],  $conditionAttribute[0][1])
               ->where($conditionAttribute[1][0], $conditionAttribute[1][1] )
               ->where($conditionAttribute[2][0], $conditionAttribute[2][1] )
               ->get( $attributes);
            }
        }

        if($enumReplacements){
            $rows = ProcessDataHelper::enumsConvertIdToName($rows, $enumReplacements);
        }

        if($columnReplacements){
            $rows = ProcessDataHelper::columnConvertIdToName($rows, $columnReplacements);
        }
        foreach ($rows as $row) {
            if (isset($row->logo_url)) {
                $row->logo_url = asset($row->logo_url);
            } elseif (isset($row->image_url)) {
                $row->image_url = asset($row->image_url);
            }
        }
          return ResponseHelper::success();
    }
}
