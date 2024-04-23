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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Notifications\EmaiVerificationNotification;

class LoginHelper
{
    public static function userLogin(Request $request, $model )
    {
        $input = $request->only('email', 'password');
        $validation = Validator::make($input, [
            'email' => 'required|email',
            'password' => 'required|min:8'
        ]);

        if ($validation->fails()) {
            return response()->json(['error_message' =>  $validation->errors()->first()], 422);
        }
        // Find user by email (assuming unique email)
        $user = User::where('email', $input['email'])->first();

        if (!$user) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }
        $token = $user->createToken('quesionbanklaravelapi')->accessToken;

        return response()->json(['token' => $token], 200);




    }

}
