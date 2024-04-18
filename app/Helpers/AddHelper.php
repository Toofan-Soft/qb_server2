<?php

namespace App\Helpers;

use App\Models\User;
use App\Enums\RoleEnum;
use App\Enums\GenderEnum;
use App\Enums\JobTypeEnum;
use Illuminate\Support\Str;
use App\Enums\OwnerTypeEnum;
use App\Helpers\ImageHelper;
use Illuminate\Http\Request;
use App\Enums\UserStatusEnum;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Notifications\EmaiVerificationNotification;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AddHelper
{
    public static $filePath = null;



    public static function addModel(Request $request, $model, $rules, $relationShip = null, $related_id = null )
    {
           $validator = Validator::make($request->all(), $rules);
           if ($validator->fails()) {
               return response()->json(['error_message' => $validator->errors()->first()], 400);
           }
           $updatedAttributes = $request->all();
           foreach (['image_url', 'logo_url', 'attachment'] as $fileKey) {
               if ($request->hasFile($fileKey)) {
                   $filePath = ImageHelper::uploadImage($request->file($fileKey));
                   $updatedAttributes[$fileKey] = $filePath; // Update attribute with file path
               }
           }
           if ($request->has($related_id)) {
               // Create model with relationship
               try {
                   $parentModel = $model::findOrFail($related_id);
                   $parentModel->$relationShip()->create($updatedAttributes);
               } catch (ModelNotFoundException $e) {
                   return response()->json([
                       'error_message' => "Parent model not found.",
                   ], 404);
               }
           } else {
               // Create model without relationship
               $data = $model::create($updatedAttributes);
           }
           return response()->json([ 'message' => 'college created successfully!', 'data' => $data,
           ], 201);
    }


    public static function createNewUser(Request $request, $model, $rules, $owner_type = null)
    {
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['error_message' => $validator->errors()->first()], 400);
        }
        foreach (['image', 'logo'] as $fileKey) {
            if ($request->hasFile($fileKey)) {
                self::$filePath = ImageHelper::uploadImage($request->file($fileKey));
            }
        }
        $user = $model::create([
            'email' => $request->email,
            'password' => ($owner_type) ? bcrypt(Str::random(8)) : bcrypt($request->password),
            'status' => UserStatusEnum::ACTIVATED->value,
            'owner_type' => ($owner_type) ? $request->owner_type : OwnerTypeEnum::GUEST->value,
        ]);

        //$roles = self::getUserRoles($user, $request) ;  // $request->owner_type  can be null
        self::assignRoleToUser($user, self::getUserRoles($user, $request));
        $token = $user->createToken('quesionbanklaravelapi')->accessToken;  // هنا يجب ان يكون ال salt قوي جدا
        $user->notify(new EmaiVerificationNotification());
        return response()->json(['token' => $token], 200);
    }


    public static function getUserRoles(User $user, Request $request){
        switch ($request->owner_type) {
            case OwnerTypeEnum::STUDENT->value:
                self::createStudent($user, $request);
                return RoleEnum::STUDENT->value;
            case OwnerTypeEnum::LECTURER->value:
                self::createEmployee($user, $request);
                return RoleEnum::LECTURER->value;
            case OwnerTypeEnum::EMPLOYEE->value:
                self::createEmployee($user, $request);
                return $request->owner_type;   // if it is as array
            default:
                self::createGuest($user, $request);
                return RoleEnum::GUEST->value;
    }
    // if ($owner_type === OwnerTypeEnum::STUDENT->value ) {
        //     return  RoleEnum::STUDENT->value;
        // }elseif ($owner_type === OwnerTypeEnum::LECTURER->value) {
        //    return RoleEnum::LECTURER->value;
        // }elseif ($owner_type === OwnerTypeEnum::EMPLOYEE->value) {
        //    return $owner_type;
        // }else {
        //    return RoleEnum::GUEST->value ;
        // }
    }

    public static function assignRoleToUser(User $user , $roles){
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
    public static function createGuest(User $user, Request $request){
        $user->guest()->create([
            'name' => $request->name,
            'phone' => $request->phone,
            'image_url' => self::$filePath,
            'gender' =>  $request->gender ?? GenderEnum::MALE->value,
        ]);
    }
    public static function createStudent(User $user, Request $request){
        $user->student()->create([
            'academic_id' => $request->academic_id,
            'arabic_name' =>  $request->arabic_name,
            'english_name' =>  $request->english_name,
            'phone' => $request->phone,
            'image_url' => self::$filePath,
            'birthdate' =>  $request->birthdate,
            'gender' =>  $request->gender ?? GenderEnum::MALE->value,
        ]);
    }
    public static function createEmployee(User $user, Request $request){
        $user->employee()->create([
            'arabic_name' =>  $request->arabic_name,
            'english_name' =>  $request->english_name,
            'phone' => $request->phone,
            'image_url' => self::$filePath,
            'job_type' => $request->job_type,
            'qualification' =>  $request->qualification,
            'specialization' =>  $request->specialization,
            'gender' =>  $request->gender ?? GenderEnum::MALE->value,
        ]);
    }
}
