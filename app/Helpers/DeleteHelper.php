<?php

namespace App\Helpers;


class DeleteHelper
{

    public static function deleteModel($model)
    {
        $model->delete();
        return response()->json([
            'message' => 'Department deleted successfully!',
        ], 200);
    }
}
