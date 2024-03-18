<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Models\User;
use Ichtrojan\Otp\Otp;

class ResetPasswordController extends Controller
{
    private $otp ;
    public function __construct(){
        $this->otp=new Otp();
    }

   public function password_reset(ResetPasswordRequest $request){
    $otp2 = $this->otp->validate($request->email, $request->otp);
    if(! $otp2->status){
        return response()->json(['error' => $otp2],401);
    }


    $user=User::where('email',$request->email)->first();

    $user->update(['password' => bcrypt($request->password)]);

    $success['success'] = true;
    return response()->json($success,200);

   }
}
