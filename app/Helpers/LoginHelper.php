<?php

namespace App\Helpers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LoginHelper
{
    public static function userLogin(Request $request, $model)
    {
        try {
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
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
