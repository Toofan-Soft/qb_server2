<?php

namespace App\Helpers;

use App\Models\UserRole;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ValidateHelper
{
    public static function validateData(Request $request, $rules)
    {
        try {
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return  $validator->errors()->first();
                // return false;
            }
            // else{
            //     return true;
            // }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public static function validatePolicy($roles = []): bool
    {
        try {
            $user = auth()->user();

            if (is_null($user)) {
                return false;
            }

            $userRoleIds = UserRole::where('user_id', $user->id)
                ->pluck('role_id')
                ->toArray();

            foreach ($roles as $role) {
                if (in_array($role, $userRoleIds)) {
                    return true;
                }
            }

            return false;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public static function validateUser(): bool
    {
        try {
            $user = auth()->user();

            if (is_null($user)) {
                return false;
            }

            return true;
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
