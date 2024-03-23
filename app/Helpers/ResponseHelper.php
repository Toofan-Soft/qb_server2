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

    public static function serverError($message ,$number = 500){
        return response()->json(['error_message' => $message], $number);
    }
    public static function clientError($message ,$number = 400){
        return response()->json(['error_message' => $message], $number);
    }
    public static function success($message = 'successfull',$number = 200){
        return response()->json(['message' => $message], $number);
    }
    public static function successWithData($data ,$number = 200){
        return response()->json(['data' => $data], $number);
    }

}
