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
        try {
            $deleteCount = $model->delete();
            // return ResponseHelper::success();
        } catch (\Exception $e) {
            // throw $e;
            return ResponseHelper::serverError();
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
