<?php

namespace App\Http\Controllers;

use App\Helpers\UserHelper;
use App\Enums\OwnerTypeEnum;
use Illuminate\Http\Request;

class UserController extends Controller
{
<<<<<<<<< Temporary merge branch 1
    public function verifyAccount(Request $request)
    {

    }

    public function login(Request $request)
    {

    }
    public function logout(Request $request)
    {

    }
    public function retrieveProfile()
    {
        $user = 0;
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

    public function requestAccountReovery(Request $request)
    {

    }
    public function changePasswordAfterAccountReovery(Request $request)
    {

    }
=========

>>>>>>>>> Temporary merge branch 2
}
