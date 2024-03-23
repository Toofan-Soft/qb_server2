<?php

namespace App\Helpers;

use App\Enums\OwnerTypeEnum;
use App\Helpers\ImageHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class UserHelper
{

    public static function addUser( $email, $owner_type_id, $owner_id = null, $password = null, $roles = [] )
    {

        // $roles = $roles_ids;
        // add user status by default

        if($owner_type_id === OwnerTypeEnum::EMPLOYEE->value){

        }elseif ($owner_type_id === OwnerTypeEnum::LECTURER->value) {


        }elseif ($owner_type_id === OwnerTypeEnum::STUDENT->value) {

        }

    }
    public static function addUserRoles( $user, $roles = [] )
    {


    }

    // public static function addUserRole( $user, $role_id )
    // {


    // }
    public static function deleteUserRoles( $user,  $roles = [] )
    {

    }

    public static function retrieveGuestProfile( $user )
    {

    }
    public static function retrieveStudentProfile( $user )
    {

    }
    public static function retrieveEmployeeProfile( $user )
    {

    }
}
