<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Helpers\UserHelper;
use App\Enums\OwnerTypeEnum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{

    public function verifyAccount(Request $request)
    {

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
        // Find user by email (assuming unique email)
        $user = User::where('email', $input['email'])->first();

        if (!$user) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }
        $token = $user->createToken('quesionbanklaravelapi')->accessToken;
        return response()->json(['token' => $token], 200); 

    }
    public function logout(Request $request)
    {

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

    }

    // reset password by email
    public function requestAccountReovery(Request $request)
    {

    }
    public function changePasswordAfterAccountReovery(Request $request)
    {

    }

}
