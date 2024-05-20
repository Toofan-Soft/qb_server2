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
        /**
         * using: retrieve data from sent model
         * parameters: $
         *      model :
         *      attributes :
         *      conditionAttribute :
         *      enumReplacements :
         *      columnReplacements :
         * return:
         */

    public static function retrieveModels($model, $attributes = null , $conditionAttribute = [] , $enumReplacements = null, $columnReplacements =null) {
        $query = $model::query();
        $rows = null;
        if (empty($conditionAttribute)) {
            if (empty($attributes)) {
                $rows = $model::all();
            }else {
                $rows = $model::all($attributes);
            }
        } else {
            if (empty($attributes)) {

                $query = $query->where(function ($query) use ($conditionAttribute) {
                    foreach ($conditionAttribute as $column => $value) {
                        $query->where($column, '=', $value);
                    }
                });
                $rows = $query->get();
            }else {

            $query = $query->where(function ($query) use ($conditionAttribute) {
                foreach ($conditionAttribute as $column => $value) {
                    $query->where($column, '=', $value);
                }
            });

            $rows = $query->get($attributes);
            }
        }

        foreach ($rows as $row) {
            if (isset($row->logo_url)) {
                $row->logo_url = asset($row->logo_url);
            } elseif (isset($row->image_url)) {
                $row->image_url = asset($row->image_url);
            }
        }

        if($enumReplacements){
            $rows = ProcessDataHelper::enumsConvertIdToName($rows, $enumReplacements);
        }

        if($columnReplacements){
            $rows = ProcessDataHelper::columnConvertIdToName($rows, $columnReplacements);
        }

        if (count($rows) === 1) {
            return ResponseHelper::successWithData($rows->first());
          }
         return ResponseHelper::successWithData($rows);
    }

}
