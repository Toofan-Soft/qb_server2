<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Guest;
use App\Helpers\UserHelper;
use App\Enums\OwnerTypeEnum;
use App\Enums\RoleEnum;
use App\Helpers\ImageHelper;
use Illuminate\Http\Request;
use App\Enums\UserStatusEnum;
use App\Helpers\DeleteHelper;
use App\Helpers\ResponseHelper;
use App\Helpers\ValidateHelper;
use App\Helpers\EnumReplacement;
use App\Helpers\EnumReplacement1;
use App\Helpers\ProcessDataHelper;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rules\Enum;

class UserManagmentController extends Controller
{
    public function addUser(Request $request)
    {
        if (ValidateHelper::validateData($request, $this->rules($request))) {
            return  ResponseHelper::clientError(401);
        }
        if (UserHelper::addUser($request->email, $request->owner_type_id,  $request->owner_id, null, $request->roles_ids)) {
            return ResponseHelper::success();
        }
        return ResponseHelper::serverError();
        // return ResponseHelper::serverError('لم يتم اضافة حساب لهذا المستخدم');
    }

    public function modifyUserRoles(Request $request)
    {

        if (ValidateHelper::validateData($request, $this->rules($request))) {
            return  ResponseHelper::clientError(401);
        }
        $user = User::findOrFail($request->id);
        $userRoles = $user->user_roles()->get(['role_id']);
        foreach ($request->roles_ids as $role_id) {
            if (in_array($userRoles['role_id'], $role_id)) {
                $user->user_roles()->where('role_id', '=', $role_id)->delete();

            } else {
                UserHelper::addUserRoles($user->id, [$role_id]);
            }
        }
        return ResponseHelper::success();
    }

    public function changeUserStatus(Request $request)
    {
        $user = User::findOrFail($request->id);
        if ($user->status === UserStatusEnum::ACTIVATED->value) {
            $user->update([
                'status' =>  UserStatusEnum::INACTIVE->value,
            ]);
        } else {
            $user->update([
                'status' =>  UserStatusEnum::ACTIVATED->value,
            ]);
        }
        return ResponseHelper::success();
    }

    public function deleteUser(Request $request)
    {
        $user = User::findOrFail($request->id);
        $user->user_roles()->delete();
        // $userRoles = $user->user_roles()->get(['role_id'])->toArray();
        // UserHelper::deleteUserRoles($user->id, $userRoles);
        return DeleteHelper::deleteModel($user);
    }

    public function retrieveUsers(Request $request)
    {
        $users = [];
        $ownerTable = '';
        if ($request->owner_type_id === OwnerTypeEnum::GUEST->value) {
            $ownerTable = 'guests';
        } elseif ($request->owner_type_id === OwnerTypeEnum::STUDENT->value) {
            $ownerTable = 'students';
        } else {
            $ownerTable = 'employees';
        }
        $users = DB::table('users')
            ->join('user_roles', 'users.id', '=', 'user_roles.user_id')
            ->join($ownerTable, 'users.id', '=', $ownerTable . '.user_id')
            ->select(
                'users.id',
                'users.status as status_name',
                'users.email',
                $ownerTable . '.name as owner_name',
                $ownerTable . '.image_url'
            )
            ->Where('users.owner_type', '=', $request->owner_type_id)
            ->Where('user_roles.role_id', '=', $request->role_id)
            ->get();
        $users = ProcessDataHelper::enumsConvertIdToName($users, new EnumReplacement('status_name', UserStatusEnum::class));

        return ResponseHelper::successWithData($users);
    }

    public function retrieveUser(Request $request)
    {
        $userData = User::findOrFail($request->id, ['email, status as status_name, owner_type as owner_type_name']);

        $ownerTable = '';
        if ($userData->owner_type_name === OwnerTypeEnum::GUEST->value) {
            $ownerTable = 'guests';
        } elseif ($userData->owner_type_name === OwnerTypeEnum::STUDENT->value) {
            $ownerTable = 'student';
        } else {
            $ownerTable = 'employee';
        }

        $ownerData = $userData->$ownerTable()->get(['arabic_name as name, image_url']);

        $currentUserRoles = $userData->user_roles()->get(['role_id']);
        $userRoles = UserHelper::retrieveOwnerRoles($userData->owner_type_name);
        foreach ($userRoles as $userRole) {
            if (in_array($userRole['id'], $currentUserRoles['role_id'])) {
                $userRole['is_selected'] = true;
            } else {
                $userRole['is_selected'] = false;
            }
        }

        $userData['is_active'] = ($userData->status_name === UserStatusEnum::ACTIVATED->value)? true : false;
        $userData = ProcessDataHelper::enumsConvertIdToName($userData, new EnumReplacement('owner_type_name', OwnerTypeEnum::class));

        array_merge($userData, $ownerData); // conncat userData + ownerData + userRoles
        $userData['roles'] = $userRoles;
        return ResponseHelper::successWithData($userData);
    }

    public function retrieveOwnerRoles(Request $request)
    {
        $userRoles = UserHelper::retrieveOwnerRoles($request->owner_type_id);
        return ResponseHelper::successWithData($userRoles);
        // $attributes = ['id, name, is_mandatory'];
    }


    public function rules(Request $request): array
    {
        $rules = [
            'email' => 'required|email|unique:users,email',
            'owner_type_id' => ['required', new Enum(OwnerTypeEnum::class)],
            'owner_id' => ['required'],
            'id' => ['nullable'],    // it is the same of owner_id  but from another process
            'roles_ids' => ['nullable'],
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
