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
    public static function clientError1($m, $number = 400){
        return response()->json(['message'=> $m], $number);
    }
    public static function success($number = 200){
        return response()->json(null, $number);
    }
    public static function successWithData($data ,$number = 201){
        return response()->json(['data' => $data], $number);
    }

    public static function successWithToken($data ,$number = 202){
        return response()->json(['token' => $data], $number);
    }

}
