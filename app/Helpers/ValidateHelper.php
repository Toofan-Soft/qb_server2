<?php

namespace App\Helpers;

use App\Models\User;
use App\Enums\RoleEnum;
use App\Enums\GenderEnum;
use App\Enums\JobTypeEnum;
use Illuminate\Support\Str;
use App\Enums\OwnerTypeEnum;
use App\Helpers\ImageHelper;
use Illuminate\Http\Request;
use App\Enums\UserStatusEnum;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Notifications\EmaiVerificationNotification;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ValidateHelper
{
    public static function validateData(Request $request, $rules)
    {
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return  $validator->errors()->first();
            // return false;
        } 
        // if ($request->method() === 'PUT' || $request->method() === 'PATCH') {
        //     if (count($rules) > 0) {
        //         return true;
        //     } else {
        //         return false;
        //     }
        // }
    }

    public static function validatePolicy($roles = []) : bool {

        return true;        
    }
}
