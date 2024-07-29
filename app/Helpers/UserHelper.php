<?php

namespace App\Helpers;

use App\Enums\JobTypeEnum;
use App\Models\User;
use App\Models\Guest;
use App\Enums\RoleEnum;
use App\Models\Student;
use App\Models\Employee;
use App\Enums\OwnerTypeEnum;
use App\Enums\UserStatusEnum;
use Illuminate\Support\Facades\DB;
use App\Notifications\EmaiVerificationNotification;

class UserHelper
{

    public static function addUser($email, $ownerTypeId, $ownerId, $password = null, $roles = [])
    {
        // make the function recieve كائن من المالك (طالب، موظف، زائر) بدل رقم المالك
        // add user status by default

        $roles = $roles ?? [];
        // لضمان ان هذا المتغير يبقى من نوع مصفوفة في حاله لم يتم تمريره او تمرير قيمته بفارغ

        DB::beginTransaction();
        try {
            $generatedToken = self::generateAlphanumericToken();

            $user = User::create([
                'email' => $email,
                'password' => ($password) ? bcrypt($password) : $generatedToken,
                'status' => UserStatusEnum::ACTIVATED->value,
                'owner_type' => $ownerTypeId,
                'email_verified_at' => ($ownerTypeId === OwnerTypeEnum::GUEST->value) ? null : now(),
            ]);

            $owner = null;
            if ($ownerTypeId === OwnerTypeEnum::GUEST->value) {
                $owner = Guest::findOrFail($ownerId)->update(['user_id' => $user->id]);
                array_push($roles, RoleEnum::GUEST->value);
            } elseif ($ownerTypeId === OwnerTypeEnum::STUDENT->value) {
                $owner = Student::findOrFail($ownerId)->update(['user_id' => $user->id]);
                array_push($roles, RoleEnum::STUDENT->value);
            } elseif ($ownerTypeId === OwnerTypeEnum::EMPLOYEE->value) {
                $owner = Employee::findOrFail($ownerId);
                if (intval($owner->job_type) === JobTypeEnum::LECTURER->value) {
                    array_push($roles, RoleEnum::LECTURER->value);
                } elseif (intval($owner->job_type) === JobTypeEnum::EMPLOYEE->value) {
                    array_push($roles, RoleEnum::PROCTOR->value);
                } elseif (intval($owner->job_type) === JobTypeEnum::EMPLOYEE_LECTURE->value) {
                    array_push($roles, RoleEnum::PROCTOR->value);
                    array_push($roles, RoleEnum::LECTURER->value);
                } else {
                    DB::rollBack();
                    return false;
                }
                $owner->update(['user_id' => $user->id]);
            } else {
                DB::rollBack();
                return false;
            }

            foreach ($roles as $role) {
                $user->user_roles()->create([
                    'role_id' => $role,
                ]);
            }
            // return [$user, $generatedToken];
            $user->notify(new EmaiVerificationNotification($generatedToken));

            // if ($ownerTypeId === OwnerTypeEnum::GUEST->value) {
            //     $token = $user->createToken('quesionbanklaravelapi')->accessToken;
            //     return $token;
            // } else {
            DB::commit();
            return true;
            // }
        } catch (\Exception $e) {
            DB::rollBack();
            return false;
            // throw $e;
        }
    }

    public static function addUserRoles(User $user, $roles = [])
    {
        try {
            if (is_array($roles)) {
                $user->user_roles()->createMany(array_map(function ($r) {
                    return ['role_id' => $r];
                }, $roles));
            } else {
                $user->user_roles()->create([
                    'role_id' => $roles,
                ]);
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public static function generateAlphanumericToken(int $length = 8): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $numbers = '0123456789';
        $letters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        $randomNumber = $numbers[random_int(0, strlen($numbers) - 1)];
        $randomLetter = $letters[random_int(0, strlen($letters) - 1)];

        $randomChars = substr(str_shuffle($characters), 0, $length - 2);

        $token = str_shuffle($randomNumber . $randomLetter . $randomChars);

        return $token;
    }
}
