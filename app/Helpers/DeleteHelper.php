<?php

namespace App\Helpers;


class DeleteHelper
{


  /**
   * $model object of model
   * delete one raw
   */
    public static function deleteModel($model)
    {
        $deleteCount = $model->delete();
        if($deleteCount){
            return ResponseHelper::success();
        }else {
            return ResponseHelper::serverError(500);
        }
    }

    public static function deleteModels($model, $modelsIds)
    {
        try {
            $deleted = $model::whereIn('id', $modelsIds)->delete();

            if ($deleted == $modelsIds->count()) {
                return ResponseHelper::success();
            } else {
                return ResponseHelper::serverError();
            }
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }
}
