<?php

namespace App\Helpers;


class DeleteHelper
{

    public static function deleteModel($model)
    {
        $model->delete();
        return response()->json([
            'error_message' => 'college deleted successfully!',
        ], 200);
    }
}
