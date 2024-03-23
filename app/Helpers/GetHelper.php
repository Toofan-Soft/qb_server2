<?php

namespace App\Helpers;

use App\Helpers\ResponseHelper;
use PhpParser\Node\Stmt\Foreach_;

class GetHelper
{
    ////////FOR make more cindition in one methode  ?
    // $query = $model::query();
    // foreach (array_chunk($conditionAttribute, 2) as $chunk) {
    //     $query->where($chunk[0], $chunk[1]);
    // }
    // $rows = $query->get($attributes);

    // then when call we will make condition as this shape ?
    // $conditions = [
    //     ['chapter_id', 1],
    //     ['status', 'active'],
    //     ['created_at', '>', now()->subDays(7)]
    // ];

    
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
