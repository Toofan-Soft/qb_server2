<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Requests\Auth\EmailVerificationRequest;
use App\Notifications\EmaiVerificationNotification;
use Ichtrojan\Otp\Otp;
class EmailVerificationController extends Controller
{
    private $otp ;
    public function __construct(){
        $this->otp=new Otp();
    }


    // public function sendEmailVerification(Request $request){
    //     $request->user()->notify(new EmaiVerificationNotification());
    //     $success['success'] = true;
    //     return response()->json($success,200);
    // }


    public function email_verification(EmailVerificationRequest $request){
        $otp2 = $this->otp->validate($request->email, $request->otp);   // when real work  i will change the otp into code or any name
        if(!$otp2->status){
            return response()->json(['error' => $otp2],401);
        }
        $user=User::where('email',$request->email)->first();
        $user->update(['email_verified_at'=> now()] );
        $success['success'] = true;
        return response()->json($success,200);
    }
}
