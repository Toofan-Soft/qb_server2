<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Helpers\UserHelper;
use App\Enums\OwnerTypeEnum;
use Illuminate\Http\Request;
use App\Enums\UserStatusEnum;
use App\Helpers\DeleteHelper;
use App\Helpers\EnumReplacement1;
use App\Helpers\ProcessDataHelper;
use Illuminate\Support\Facades\DB;

class UserManagmentController extends Controller
{
    public function addUser(Request $request)
    {
       // addUser($request->email, $request->owner_type_id, $request->roles_ids);
    }

    public function modifyUserRoles(Request $request)
    {
        $user = User::find($request->id);
        $userRoles = $user->user_roles()->get(['role_id']);
        foreach ($request->roles_ids as $role_id) {
            if(in_array($userRoles['role_id'], $role_id)){
                UserHelper::deleteUserRoles($user->id, [$role_id]);
            }else{
                UserHelper::addUserRoles($user->id, [$role_id]);
            }
        }
    }

    public function changeUserStatus(Request $request)
    {
        $user = User::find($request->id);
        if($user->status === UserStatusEnum::ACTIVATED->value){
            $user['status'] = UserStatusEnum::INACTIVE->value;
        }else{
            $user['status'] = UserStatusEnum::ACTIVATED->value;
        }
    }

    public function deleteUser (User $user)
    {
        return DeleteHelper::deleteModel($user);
    }

        public function retrieveUsers(Request $request)
        {
            $users = [];
            $ownerTable = "";
            if($request->owner_type_id === OwnerTypeEnum::GUEST->value){
                $ownerTable = 'cuests';
                $users = DB::table('users')
                ->join('user_roles', 'users.id', '=', 'user_roles.user_id')
                ->join('cuests', 'users.id', '=', 'cuests.user_id')
                ->select('users.id', 'users.status as status_name', 'users.email', 'cuests.name as owner_name', 'cuests.image_url')
                ->Where('users.owner_type', '=', $request->owner_type_id)
                ->Where('user_roles.role_id', '=', $request->role_id)
                ->get();
            }elseif ($request->owner_type_id === OwnerTypeEnum::STUDENT->value) {
                $ownerTable = 'students';
                $users = DB::table('users')
                ->join('user_roles', 'users.id', '=', 'user_roles.user_id')
                ->join('students', 'users.id', '=', 'students.user_id')
                ->select('users.id', 'users.status as status_name', 'users.email', 'students.arabic_name as owner_name', 'students.image_url')
                ->Where('users.owner_type', '=', $request->owner_type_id)
                ->Where('user_roles.role_id', '=', $request->role_id)
                ->get();
            }else {
                $ownerTable = 'employees';
                $users = DB::table('users')
                ->join('user_roles', 'users.id', '=', 'user_roles.user_id')
                ->join('employees', 'users.id', '=', 'employees.user_id')
                ->select('users.id', 'users.status as status_name', 'users.email', 'employees.arabic_name as owner_name', 'employees.image_url')
                ->Where('users.owner_type', '=', $request->owner_type_id)
                ->Where('user_roles.role_id', '=', $request->role_id)
                ->get();
            }
            // LECTURER, EMPLOYEE

            $users = ProcessDataHelper::enumsConvertIdToName($users, new EnumReplacement1( 'status_name', UserStatusEnum::class));

            return $users;
        }

        public function retrieveUser(Request $request)
        {
            $userData = User::find($request->id, ['email, status as status_id, owner_type as owner_type_name']);
            $userRoles = $userData->user_roles()->get(['role_id']);
            $ownerTable = '';
            if($userData->owner_type_name === OwnerTypeEnum::GUEST->value){
                $ownerTable = 'guest';
            }elseif ($userData->owner_type_name === OwnerTypeEnum::STUDENT->value) {
                $ownerTable = 'student';
            }else{
                $ownerTable = 'employee';
            }

            $ownerData = $userData->$ownerTable()->get(['arabic_name as name, image_url']);
            // modify userRoles list like [id, name, is_selected, is_mandatory]

            $userData = ProcessDataHelper::enumsConvertIdToName($userData, new EnumReplacement1( 'owner_type_name', OwnerTypeEnum::class));

            $user = [] ; // conncat userData + ownerData + userRoles
            return $user;
        }

        public function retrieveOwnerRoles(Request $request)
        {
            // $attributes = ['id, name, is_mandatory'];
        }


    public function rules(Request $request): array
    {
        $rules = [
            // 'arabic_name' => 'required|string|max:255',
            // 'english_name' => 'required|string|max:255',
            // 'logo_url' =>  'image|mimes:jpeg,png,jpg,gif|max:2048', // Adjust max size as needed
            // 'description' => 'nullable|string',
            // 'phone' => 'nullable|string|unique:colleges,phone',
            // 'email' => 'nullable|email|unique:colleges,email',
            // 'facebook' => 'nullable|string|max:255',
            // 'twitter' => 'nullable|string|max:255',
            // 'youtube' => 'nullable|string|max:255',
            // 'telegram' => 'nullable|string|max:255',
        ];
        if ($request->method() === 'PUT' || $request->method() === 'PATCH') {
            $rules = array_filter($rules, function ($attribute) use ($request) {
                // Ensure strict type comparison for security
                return $request->has($attribute);
            });
        }
        return $rules;
    }
}
