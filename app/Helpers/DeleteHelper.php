<?php

namespace App\Helpers;


class DeleteHelper
{

    public static function deleteModel($model)
    {
        $deleteCount = $model->delete();
        if($deleteCount){
            return ResponseHelper::success();
        }else {
            return ResponseHelper::serverError('something went wrong , not deleted');
        }
    }

    public static function deleteModels($model, $modelsIds)
    {
        $deleteCount =  $model::where('id', $modelsIds)->delete();
        if($deleteCount == $modelsIds->count()){
            return ResponseHelper::success();
        }else {
            return ResponseHelper::serverError('something went wrong , not deleted');
        }
    }
}
