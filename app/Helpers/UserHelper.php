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
use App\Models\UserRole;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Notifications\EmaiVerificationNotification;

class UserHelper
{

    public static function addUser($email, $ownerTypeId, $ownerId, $password = null, $roles = [])
    {
        // make the function recieve كائن من المالك (طالب، موظف، زائر) بدل رقم المالك

        // add user status by default
        $generatedToken = self::generateAlphanumericToken(8);

        $parent = null;
        if ($ownerTypeId === OwnerTypeEnum::GUEST->value) {
            $parent = Guest::findOrFail($ownerId);
            array_push($roles, RoleEnum::GUEST->value);
        } elseif ($ownerTypeId === OwnerTypeEnum::STUDENT->value) {
            $parent = Student::findOrFail($ownerId);
            array_push($roles, RoleEnum::STUDENT->value);
        } elseif ($ownerTypeId === OwnerTypeEnum::LECTURER->value) {
            $parent = Employee::findOrFail($ownerId);
            array_push($roles, RoleEnum::LECTURER->value);
        } elseif ($ownerTypeId === OwnerTypeEnum::EMPLOYEE->value) {
            $parent = Employee::findOrFail($ownerId);
        } else {
            $parent = Employee::findOrFail($ownerId);
            array_push($roles, RoleEnum::LECTURER->value);
        }

        $user = $parent->user()->create([
            'email' => $email,
            'password' => ($password) ?  bcrypt($password) : $generatedToken,
            'status' => UserStatusEnum::ACTIVATED->value,
            'owner_type' => $ownerTypeId,
        ]);

<<<<<<< HEAD

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
            // return true;
            return   $token ;

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
            return true;

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
            return true;
        }else{

            $guest = Guest::findOrFail($ownerId);
            $guest->update([
                'user_id' => $user->id,
            ]);
=======
        foreach ($roles as $role) {
>>>>>>> 60b13af2688e3346577884998e9a8ffd3d1b2d65
            $user->user_roles()->create([
                'role_id' => $role,
            ]);
        }

        $user->notify(new EmaiVerificationNotification($generatedToken));

        if ($ownerTypeId === OwnerTypeEnum::GUEST->value) {
            $token = $user->createToken('quesionbanklaravelapi')->accessToken;
            return $token;
        } else {
            return true;
        }
    }

    public static function addUserRoles(User $user, $roles = [])
    {
        if (is_array($roles)) {
            $user->user_roles()->createMany(array_map(function ($r) {
                return ['role_id' => $r];
            }, $roles));
        } else {
            $user->user_roles()->create([
                'role_id' => $roles,
            ]);
        }
    }

    public static function retrieveOwnerRoles($ownerTypeId)
    {
        $userRoles = [];
        
        if ($ownerTypeId === OwnerTypeEnum::GUEST->value) {
            $userRoles = [
                [
                    'id' => RoleEnum::GUEST->value,
                    'name' =>RoleEnum::getNameByNumber(RoleEnum::GUEST->value),
                    'is_mandatory' => true
                ]
            ];
        } elseif ($ownerTypeId === OwnerTypeEnum::STUDENT->value) {
            $userRoles = [
                [
                    'id' => RoleEnum::STUDENT->value,
                    'name' =>RoleEnum::getNameByNumber(RoleEnum::STUDENT->value),
                    'is_mandatory' => true
                ]
            ];
        } elseif ($ownerTypeId === OwnerTypeEnum::LECTURER->value) {
            $userRoles = [
                [
                    'id' => RoleEnum::LECTURER->value,
                    'name' =>RoleEnum::getNameByNumber(RoleEnum::LECTURER->value),
                    'is_mandatory' => true
                ],
                [
                    'id' => RoleEnum::QUESTION_ENTRY->value,
                    'name' =>RoleEnum::getNameByNumber(RoleEnum::QUESTION_ENTRY->value),
                    'is_mandatory' => false
                ],
                [
                    'id' => RoleEnum::PROCTOR->value,
                    'name' =>RoleEnum::getNameByNumber(RoleEnum::PROCTOR->value),
                    'is_mandatory' => false
                ]
            ];
        } else {
            $userRoles = [
                [
                    'id' => RoleEnum::QUESTION_REVIEWER->value,
                    'name' =>RoleEnum::getNameByNumber(RoleEnum::QUESTION_REVIEWER->value),
                    'is_mandatory' => false
                ],
                [
                    'id' => RoleEnum::QUESTION_ENTRY->value,
                    'name' =>RoleEnum::getNameByNumber(RoleEnum::QUESTION_ENTRY->value),
                    'is_mandatory' => false
                ],
                [
                    'id' => RoleEnum::PROCTOR->value,
                    'name' =>RoleEnum::getNameByNumber(RoleEnum::PROCTOR->value),
                    'is_mandatory' => false
                ],
                [
                    'id' => RoleEnum::SYSTEM_ADMINISTRATOR->value,
                    'name' =>RoleEnum::getNameByNumber(RoleEnum::SYSTEM_ADMINISTRATOR->value),
                    'is_mandatory' => false
                ],
                [
                    'id' => RoleEnum::DATA_ENTRY->value,
                    'name' =>RoleEnum::getNameByNumber(RoleEnum::DATA_ENTRY->value),
                    'is_mandatory' => false
                ]
            ];
        }

        return $userRoles;
    }

    private static function generateAlphanumericToken(int $length = 8): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
        return substr(str_shuffle($characters), 0, $length);
    }

}
