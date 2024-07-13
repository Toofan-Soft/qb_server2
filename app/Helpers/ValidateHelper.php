<?php

namespace App\Helpers;

use App\Models\UserRole;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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
        $user = auth()->user();

        if (!$user) {
            return false;
        }

        $userRoleIds = UserRole::where('user_id', $user->id)
            ->pluck('role_id')
            ->toArray();

        foreach ($roles as $role) {
            if (in_array($role->value, $userRoleIds)) {
                return true;
            }
        }

        return false;
    }
}
