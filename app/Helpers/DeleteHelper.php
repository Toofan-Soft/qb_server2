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
            return ResponseHelper::serverError('something went wrong , not deleted');
        }
    }

    public static function deleteModels($model, $modelsIds)
    {
        try {
            $deleted = $model::whereIn('id', $modelsIds)->delete();

            if ($deleted == $modelsIds->count()) {
                return ResponseHelper::success();
            } else {
                return ResponseHelper::serverError('Failed to delete some models.');
            }
        } catch (\Exception $e) {
            return ResponseHelper::serverError('An error occurred while deleting models.');
        }
    }
}
