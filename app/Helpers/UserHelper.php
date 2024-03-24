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

    public static function addUser( $email, $ownerTypeId, $ownerId , $password = null, $roles = [] )
    {

        // $roles = $roles_ids;
        // add user status by default

        if($owner_type_id === OwnerTypeEnum::EMPLOYEE->value){


        }elseif ($owner_type_id === OwnerTypeEnum::LECTURER->value) {


        }elseif ($owner_type_id === OwnerTypeEnum::STUDENT->value) {

        }


        return true;

    }
    public static function addUserRoles( $user, $roles = [] )
    {


    }

    public static function deleteUserRoles( $user,  $roles = [] )
    {

    }

  public static function retrieveOwnerRoles( $ownerTypeId )
    {
        $userRoles = [
            'id' => 0,
            'is_mandatory' => true
        ];
        return $userRoles;
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
