<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Guest;
use Ichtrojan\Otp\Otp;
use App\Models\Student;
use App\Models\Employee;
use App\Enums\GenderEnum;
use App\Enums\JobTypeEnum;
use App\Helpers\UserHelper;
use App\Enums\OwnerTypeEnum;
use Illuminate\Http\Request;
use App\Helpers\ResponseHelper;
use App\Enums\QualificationEnum;
use App\Helpers\EnumReplacement;
use App\Helpers\ProcessDataHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\Console\Helper\ProcessHelper;
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
            return ResponseHelper::clientError(401);
        }
        $user = User::where('email',$request->email)->first();
        $user->update(['email_verified_at'=> now()] );
        $success['success'] = true;
        return ResponseHelper::success();
    }

    public function login(Request $request)
    {
        $input = $request->only('email', 'password');
        $validation = Validator::make($input, [
            'email' => 'required|email',
            'password' => 'required|min:8'
        ]);

        if ($validation->fails()) {
            return ResponseHelper::clientError(401); 
          }

        if (auth()->attempt($input)) {
            $user = Auth::user();

            $token =  $user->createToken('quesionbanklaravelapi')->accessToken;
           return ResponseHelper::successWithToken($token);

        } else {
            return ResponseHelper::clientError(401);
          }
    }

    public function logout(Request $request)
    {
        Auth::logout();
        if (Auth::check()) {
            // User is still logged in
            return ResponseHelper::serverError();
            // return ResponseHelper::serverError('User not logged out');
        } else {
            // User is logged out
            return ResponseHelper::success();
        }
    }
    public function retrieveProfile()
    {
        $user = auth()->user();

        $owner = null;
        $enumReplacements = [];

        if($user->owner_type === OwnerTypeEnum::GUEST->value){
            $attributes = ['name', 'phone', 'gender as gender_name','image_url'];
            $owner = Guest::where('user_id', $user->id)->first($attributes);
            array_push($enumReplacements, new EnumReplacement('gender_name', GenderEnum::class));

        }elseif($user->owner_type === OwnerTypeEnum::STUDENT->value){
            $attributes = ['arabic_name', 'english_name' , 'phone', 'birthdate', 'gender as gender_name','image_url'];
            $owner = Student::where('user_id', $user->id)->first($attributes);
            array_push($enumReplacements, new EnumReplacement('gender_name', GenderEnum::class));

        }else{
            $attributes = ['arabic_name', 'english_name' , 'phone', 'image_url', 'specialization',
            'qualification as qualification_name', 'job_type as job_type_name'
        ];
            $owner = Employee::where('user_id', $user->id)->first($attributes);
            array_push($enumReplacements, new EnumReplacement('qualification_name', QualificationEnum::class));
            array_push($enumReplacements, new EnumReplacement('job_type_name', JobTypeEnum::class));
        }

        $owner = ProcessDataHelper::enumsConvertIdToName($owner, $enumReplacements);
        $owner['email'] = $user->email;

        return ResponseHelper::successWithData($owner);
    }


    public function changePassword(Request $request)
    {
        $user = User::where('email',auth()->user()->email)->first();
        if (Hash::check($request->old_password, $user->password)) {
            $validator = Validator::make($request->all(), ['new_password' => 'required|min:8']);
            if ($validator->fails()) {
                return ResponseHelper::clientError(401);
            }
            $user->update([
                'password' => bcrypt($request->new_password),
            ]);
        }else {
            return ResponseHelper::clientError(401);
        }
        return ResponseHelper::success();
    }

    // reset password by email
    public function requestAccountReovery(Request $request)
    {
        $validator = Validator::make($request->all(), ['email' => 'required|email']);
            if ($validator->fails()) {
                return ResponseHelper::clientError(401);
            }
        $input= $request->only('email');
        $user = User::where('email',$input)->first();
        if($user){
            $user->notify(new ResetPasswordNotificationVerification());
            $success['success'] = true;
            return ResponseHelper::success();
        }else {
            return ResponseHelper::clientError(401);
        }

    }
    public function changePasswordAfterAccountReovery(Request $request)
    {
        $otp2 = $this->otp->validate(auth()->user()->email, $request->code);
        if(! $otp2->status){
            return ResponseHelper::clientError(401);
        }
        $user = User::where('email',$request->email)->first();
        $user->update(['password' => bcrypt($request->new_password)]);
        return ResponseHelper::success();
    }

}
