<?php

namespace App\Http\Controllers;

use App\Models\User;
use Ichtrojan\Otp\Otp;
use App\Helpers\UserHelper;
use App\Enums\OwnerTypeEnum;
use Illuminate\Http\Request;
use App\Helpers\ResponseHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Notifications\ResetPasswordNotificationVerification;

class UserController extends Controller
{

    private $otp ;
    public function __construct(){
        $this->otp=new Otp();
    }

    public function verifyAccount(Request $request)
    {
        $otp2 = $this->otp->validate(auth()->user()->email, $request->code);
        if(!$otp2->status){
            return response()->json(['error_message' => $otp2],401);
        }
        $user=User::where('email',$request->email)->first();
        $user->update(['email_verified_at'=> now()] );
        $success['success'] = true;
        return response()->json($success,200);
    }

    public function login(Request $request)
    {
        $input = $request->only('email', 'password');
        $validation = Validator::make($input, [
            'email' => 'required|email',
            'password' => 'required|min:8'
        ]);

        if ($validation->fails()) {
            return response()->json(['error_message' =>  $validation->errors()->first()], 422);
        }

        if (auth()->attempt($input)) {
            $user = Auth::user();
            $token =  auth()->user()->createToken('quesionbanklaravelapi')->accessToken;
            return response()->json(['token' => $token], 200);
        } else {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

    }
    public function logout(Request $request)
    {
        Auth::logout();
        if (Auth::check()) {
            // User is still logged in
            return ResponseHelper::serverError('User not logged out');
        } else {
            // User is logged out
            return ResponseHelper::success();
        }
    }
    public function retrieveProfile()
    {
        $user = auth()->user()->id;
        // get type of current user,

        $profile = [];
        if($user->owner_type === OwnerTypeEnum::GUEST->value){
            $profile = UserHelper::retrieveGuestProfile($user);
        }elseif($user->owner_type === OwnerTypeEnum::STUDENT->value){
            $profile = UserHelper::retrieveStudentProfile($user);

        }else{
            $profile = UserHelper::retrieveEmployeeProfile($user);
        }
    }


    public function changePassword(Request $request)
    {
        $user = User::where('email',auth()->user()->email)->first();
        if (Hash::check($request->old_password, $user->password)) {
            $validator = Validator::make($request->all(), ['new_password' => 'required|min:8']);
            if ($validator->fails()) {
                return  $validator->errors()->first();
            }
            $user->update([
                'password' => bcrypt($request->new_password),
            ]);
        }else {
            return ResponseHelper::clientError('invalid old password');
        }
        return ResponseHelper::success();
    }

    // reset password by email
    public function requestAccountReovery(Request $request)
    {
        $validator = Validator::make($request->all(), ['email' => 'required|email']);
            if ($validator->fails()) {
                return  $validator->errors()->first();
            }
        $input= $request->only('email');
        $user = User::where('email',$input)->first();
        if($user){
            $user->notify(new ResetPasswordNotificationVerification());
            $success['success'] = true;
            return ResponseHelper::success();
        }else {
            return ResponseHelper::clientError('email not found ');
        }

    }
    public function changePasswordAfterAccountReovery(Request $request)
    {
        $otp2 = $this->otp->validate(auth()->user()->email, $request->code);
        if(! $otp2->status){
            return response()->json(['error' => $otp2],401);
        }
        $user = User::where('email',$request->email)->first();
        $user->update(['password' => bcrypt($request->new_password)]);
        return ResponseHelper::success();
    }

}
