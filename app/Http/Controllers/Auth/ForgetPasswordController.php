<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Notifications\ResetPasswordNotificationVerification;
use App\Http\Requests\Auth\ForgetPasswordRequest;
use App\Models\User;
class ForgetPasswordController extends Controller
{
    public function forget_password(ForgetPasswordRequest $request){

        $input= $request->only('email');
        $user = User::where('email',$input)->first();
        $user->notify(new ResetPasswordNotificationVerification());

        $success['success'] = true;
        return response()->json($success,200);
    }
}
