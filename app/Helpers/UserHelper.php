<?php

namespace App\Helpers;

use App\Models\User;
use App\Models\Guest;
use App\Enums\RoleEnum;
use App\Models\Student;
use App\Models\Employee;
use Illuminate\Support\Str;
use App\Enums\OwnerTypeEnum;
use App\Helpers\ImageHelper;
use Illuminate\Http\Request;
use App\Enums\UserStatusEnum;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Notifications\EmaiVerificationNotification;

class UserHelper
{

    public static function addUser( $email, $ownerTypeId, $ownerId , $password = null, $roles = [] )
    {

        // $roles = $roles_ids;
        // add user status by default
        $generatedToken = self::generateAlphanumericToken(8);
        $user = User::create([
            'email' => $email,
            'password' => ($password) ?  bcrypt($password) :  $generatedToken,
            'status' => UserStatusEnum::ACTIVATED->value,
            'owner_type' => $ownerTypeId,
        ]);


        if($ownerTypeId === OwnerTypeEnum::EMPLOYEE->value){

            $employee = Employee::findOrFail($ownerId);
            $employee->update([
                'user_id' => $user->id,
            ]);
            if($roles){
                self::addUserRoles( $user, $roles );
            }else {
                $user->user_roles()->create([
                    'role_id' => RoleEnum::LECTURER->value,
                ]);
            }
            $token = $user->createToken('quesionbanklaravelapi')->accessToken;
            $user->notify(new EmaiVerificationNotification ($generatedToken));
            return response()->json(['token' => $token], 200);

        }elseif ($ownerTypeId === OwnerTypeEnum::LECTURER->value) {

            $lecturer = Employee::findOrFail($ownerId);
            $lecturer->update([
                'user_id' => $user->id,
            ]);

            if($roles){
                self::addUserRoles( $user, $roles );
            }else {
                $user->user_roles()->create([
                    'role_id' => RoleEnum::LECTURER->value,
                ]);
            }
            $token = $user->createToken('quesionbanklaravelapi')->accessToken;
            $user->notify(new EmaiVerificationNotification ($generatedToken));
            return response()->json(['token' => $token], 200);

        }elseif ($ownerTypeId === OwnerTypeEnum::STUDENT->value) {

            $student = Student::findOrFail($ownerId);
            $student->update([
                'user_id' => $user->id,
            ]);

            if($roles){
                self::addUserRoles( $user, $roles );
            }else {
                $user->user_roles()->create([
                    'role_id' => RoleEnum::STUDENT->value,
                ]);
            }

            $token = $user->createToken('quesionbanklaravelapi')->accessToken;
            $user->notify(new EmaiVerificationNotification ($generatedToken));
            return response()->json(['token' => $token], 200);
        }else{

            $guest = Guest::findOrFail($ownerId);
            $guest->update([
                'user_id' => $user->id,
            ]);
            $user->user_roles()->create([
                'role_id' => RoleEnum::GUEST->value,
            ]);
            $token = $user->createToken('quesionbanklaravelapi')->accessToken;
            $user->notify(new EmaiVerificationNotification ($generatedToken));
            /////////////////
            // return response()->json(['token' => $token], 200);
            return   $token ;

        }
    }

    public static function addUserRoles( User $user, $roles = [] )
    {
        if (is_array($roles)) {
            $user->user_roles()->createMany(array_map(function($r) {
                return ['role_id' => $r];
            }, $roles));
        } else {
            $user->user_roles()->create([
                'role_id' => $roles,
            ]);
        }
    }

    public static function deleteUserRoles( $user,  $roles = [] )
    {

    }

  public static function retrieveOwnerRoles( $ownerTypeId )
    {
        if($ownerTypeId === OwnerTypeEnum::STUDENT->value){

            
            $userRole = [
                    'id' => 0,
                    'is_mandatory' => true
            ];
        }
        $userRoles = [$userRole];
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

    private static function generateAlphanumericToken(int $length = 8): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
        return substr(str_shuffle($characters), 0, $length);
    }
}
