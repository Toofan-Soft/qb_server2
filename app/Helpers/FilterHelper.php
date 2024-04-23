<?php

namespace App\Helpers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class FilterHelper{
    public static function getfilterData($model, $attributes, $conditionAttribute){
        $data = null;
        if (empty($conditionAttribute)) {

            $data = $model::all($attributes);
        }else {
            $data = $model::where($conditionAttribute[0], $conditionAttribute[1])->get();
        }
        if($data){
            return response()->json(['error_message' => 'empty cources'], 400);
        }
        return response()->json(['data' => $data], 400);
    }
}
