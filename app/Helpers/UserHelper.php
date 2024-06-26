<?php

namespace App\Helpers;

use App\Models\User;
use App\Models\Guest;
use App\Enums\RoleEnum;
use App\Models\Student;
use App\Models\Employee;
use App\Models\UserRole;
use App\Traits\EnumTraits;
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

    public static function addUser($email, $ownerTypeId, $ownerId, $password = null, $roles = [])
    {
        // make the function recieve كائن من المالك (طالب، موظف، زائر) بدل رقم المالك

        // add user status by default
        $generatedToken = self::generateAlphanumericToken(8);

        $user = User::create([
            'email' => $email,
            'password' => ($password) ? bcrypt($password) : $generatedToken,
            'status' => UserStatusEnum::ACTIVATED->value,
            'owner_type' => $ownerTypeId,
            // 'email_verified_at' =>($ownerTypeId === OwnerTypeEnum::GUEST->value) ? null: now(),
        ]);

        $owner = null;
        if ($ownerTypeId === OwnerTypeEnum::GUEST->value) {
            $owner = Guest::findOrFail($ownerId)->update(['user_id' => $user->id]);
            array_push($roles, RoleEnum::GUEST->value);
        } elseif ($ownerTypeId === OwnerTypeEnum::STUDENT->value) {
            $owner = Student::findOrFail($ownerId)->update(['user_id' => $user->id]);
            array_push($roles, RoleEnum::STUDENT->value);
        } elseif ($ownerTypeId === OwnerTypeEnum::LECTURER->value) {
            $owner = Employee::findOrFail($ownerId)->update(['user_id' => $user->id]);
            array_push($roles, RoleEnum::LECTURER->value);
        } elseif ($ownerTypeId === OwnerTypeEnum::EMPLOYEE->value) {
            $owner = Employee::findOrFail($ownerId)->update(['user_id' => $user->id]);
        } else {
            $owner = Employee::findOrFail($ownerId)->update(['user_id' => $user->id]);
            array_push($roles, RoleEnum::LECTURER->value);
        }
        
        foreach ($roles as $role) {
            $user->user_roles()->create([
                'role_id' => $role,
            ]);
        }

        $user->notify(new EmaiVerificationNotification($generatedToken));

        // if ($ownerTypeId === OwnerTypeEnum::GUEST->value) {
        //     $token = $user->createToken('quesionbanklaravelapi')->accessToken;
        //     return $token;
        // } else {
            return true;
        // }
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
                    // 'name' =>RoleEnum::getNameByNumber(RoleEnum::GUEST->value),
                    'name' =>EnumTraits::getNameByNumber(RoleEnum::GUEST->value, RoleEnum::class),
                    'is_mandatory' => true
                ]
            ];
        } elseif ($ownerTypeId === OwnerTypeEnum::STUDENT->value) {
            $userRoles = [
                [
                    'id' => RoleEnum::STUDENT->value,
                    // 'name' =>RoleEnum::getNameByNumber(RoleEnum::STUDENT->value),
                    'name' =>EnumTraits::getNameByNumber(RoleEnum::STUDENT->value, RoleEnum::class),
                    'is_mandatory' => true
                ]
            ];
        } elseif ($ownerTypeId === OwnerTypeEnum::LECTURER->value) {
            $userRoles = [
                [
                    'id' => RoleEnum::LECTURER->value,
                    // 'name' =>RoleEnum::getNameByNumber(RoleEnum::LECTURER->value),
                    'name' =>EnumTraits::getNameByNumber(RoleEnum::LECTURER->value, RoleEnum::class),
                    'is_mandatory' => true
                ],
                [
                    'id' => RoleEnum::QUESTION_ENTRY->value,
                    // 'name' =>RoleEnum::getNameByNumber(RoleEnum::QUESTION_ENTRY->value),
                    'name' =>EnumTraits::getNameByNumber(RoleEnum::QUESTION_ENTRY->value, RoleEnum::class),
                    'is_mandatory' => false
                ],
                [
                    'id' => RoleEnum::PROCTOR->value,
                    // 'name' =>RoleEnum::getNameByNumber(RoleEnum::PROCTOR->value),
                    'name' =>EnumTraits::getNameByNumber(RoleEnum::PROCTOR->value, RoleEnum::class),
                    'is_mandatory' => false
                ]
            ];
        } else {
            $userRoles = [
                [
                    'id' => RoleEnum::QUESTION_REVIEWER->value,
                    // 'name' =>RoleEnum::getNameByNumber(RoleEnum::QUESTION_REVIEWER->value),
                    'name' =>EnumTraits::getNameByNumber(RoleEnum::QUESTION_REVIEWER->value, RoleEnum::class),
                    'is_mandatory' => false
                ],
                [
                    'id' => RoleEnum::QUESTION_ENTRY->value,
                    // 'name' =>RoleEnum::getNameByNumber(RoleEnum::QUESTION_ENTRY->value),
                    'name' =>EnumTraits::getNameByNumber(RoleEnum::QUESTION_ENTRY->value, RoleEnum::class),
                    'is_mandatory' => false
                ],
                [
                    'id' => RoleEnum::PROCTOR->value,
                    // 'name' =>RoleEnum::getNameByNumber(RoleEnum::PROCTOR->value),
                    'name' =>EnumTraits::getNameByNumber(RoleEnum::PROCTOR->value, RoleEnum::class),
                    'is_mandatory' => false
                ],
                [
                    'id' => RoleEnum::SYSTEM_ADMINISTRATOR->value,
                    // 'name' =>RoleEnum::getNameByNumber(RoleEnum::SYSTEM_ADMINISTRATOR->value),
                    'name' =>EnumTraits::getNameByNumber(RoleEnum::SYSTEM_ADMINISTRATOR->value, RoleEnum::class),
                    'is_mandatory' => false
                ],
                [
                    'id' => RoleEnum::DATA_ENTRY->value,
                    // 'name' =>RoleEnum::getNameByNumber(RoleEnum::DATA_ENTRY->value),
                    'name' =>EnumTraits::getNameByNumber(RoleEnum::DATA_ENTRY->value, RoleEnum::class),
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
