<?php

namespace App\Helpers;

use App\Enums\OwnerTypeEnum;
use App\Helpers\ImageHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ResponseHelper
{

    public static function serverError( $number = 500){
        return response()->json(null, $number);
    }
    public static function clientError($number = 400){
        return response()->json(null, $number);
    }
    public static function success($number = 200){
        return response()->json(null, $number);
    }
    public static function successWithData($data ,$number = 200){
        return response()->json(['data' => $data], $number);
    }

}
