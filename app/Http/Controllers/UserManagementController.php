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
use App\Helpers\NullHelper;
use App\Helpers\ProcessDataHelper;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rules\Enum;

class UserManagementController extends Controller
{
    public function addUser(Request $request)
    {
        if (ValidateHelper::validateData($request, $this->rules($request))) {
            return  ResponseHelper::clientError(401);
        }
        try {
            if (UserHelper::addUser($request->email, $request->owner_type_id,  $request->owner_id, null, $request->roles_ids)) {
                return ResponseHelper::success();
            } else {
                return ResponseHelper::serverError();
                // return ResponseHelper::serverError('لم يتم اضافة حساب لهذا المستخدم');
            }
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function modifyUserRoles(Request $request)
    {
        if (ValidateHelper::validateData($request, $this->rules($request))) {
            return  ResponseHelper::clientError(401);
        }
        DB::beginTransaction();
        try {
            $user = User::findOrFail($request->id);

            $userRoles = $user->user_roles()->pluck('role_id')->toArray();
            foreach ($request->roles_ids as $role_id) {
                if (in_array($role_id, $userRoles)) {
                    $user->user_roles()->where('role_id', '=', $role_id)->delete();
                } else {
                    UserHelper::addUserRoles($user, [$role_id]);
                    // يقترح ان يتم تجميع الصلاحيات المراد حذفها والمراد اضافتها الي مصفوفتين، ومن ثم اجراء استعلام واحد على قاعدة البيانات
                }
            }
            DB::commit();
            return ResponseHelper::success();
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseHelper::serverError();
        }
    }

    public function changeUserStatus(Request $request)
    {
        try {
            $user = User::findOrFail($request->id);
            if (intval($user->status) === UserStatusEnum::ACTIVATED->value) {
                $user->update([
                    'status' =>  UserStatusEnum::INACTIVE->value,
                ]);
            } else {
                $user->update([
                    'status' =>  UserStatusEnum::ACTIVATED->value,
                ]);
            }
            return ResponseHelper::success();
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function deleteUser(Request $request)
    {
        DB::beginTransaction();
        try {
            $user = User::findOrFail($request->id);
            $user->user_roles()->delete();
            // $userRoles = $user->user_roles()->get(['role_id'])->toArray();
            // UserHelper::deleteUserRoles($user->id, $userRoles);
            $user->delete();
            DB::commit();
            return ResponseHelper::success();
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseHelper::serverError();
        }
    }

    public function retrieveUsers(Request $request)
    {
        $users = [];
        $ownerTable = '';
        $nameColumn = 'arabic_name';
        if (intval($request->owner_type_id) === OwnerTypeEnum::GUEST->value) {
            $ownerTable = 'guests';
            $nameColumn = 'name';
        } elseif (intval($request->owner_type_id) === OwnerTypeEnum::STUDENT->value) {
            $ownerTable = 'students';
        } else {
            $ownerTable = 'employees';
        }
        try {
            $users = DB::table('users')
                ->join('user_roles', 'users.id', '=', 'user_roles.user_id')
                ->join($ownerTable, 'users.id', '=', $ownerTable . '.user_id')
                ->select(
                    'users.id',
                    'users.status as status_name',
                    'users.email',
                    $ownerTable . '.' . $nameColumn . ' as owner_name',
                    $ownerTable . '.image_url'
                )
                ->Where('users.owner_type', '=', $request->owner_type_id)
                ->Where('user_roles.role_id', '=', $request->role_id)
                ->get();
            $users = ProcessDataHelper::enumsConvertIdToName(
                $users,
                [
                    new EnumReplacement('status_name', UserStatusEnum::class)
                ]
            );
            $users = NullHelper::filter($users);
            return ResponseHelper::successWithData($users);
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function retrieveUser(Request $request)
    {
        try {
            return ResponseHelper::success();
            $userData = User::findOrFail(
                $request->id,
                ['id', 'email', 'status as status_name', 'owner_type as owner_type_name']
            );

            $ownerTable = '';
            $nameColumn = 'arabic_name as name';
            if (intval($userData->owner_type_name) === OwnerTypeEnum::GUEST->value) {
                $ownerTable = 'guests';
                $nameColumn = 'name';
            } elseif (intval($userData->owner_type_name) === OwnerTypeEnum::STUDENT->value) {
                $ownerTable = 'student';
            } else {
                $ownerTable = 'employee';
            }

            $ownerData = $userData->$ownerTable()->get([$nameColumn, 'image_url']);
            $ownerData = NullHelper::filter($ownerData);
            $currentUserRoles = $userData->user_roles()->pluck('role_id')->toArray();
            $userRoles = UserHelper::retrieveOwnerRoles($userData->owner_type_name);
            $resultRoles = [];
            foreach ($userRoles as $userRole) {
                if (in_array($userRole['id'], $currentUserRoles)) {
                    $userRole['is_selected'] = true;
                } else {
                    $userRole['is_selected'] = false;
                }
                array_push($resultRoles, $userRole);
            }

            $userData['is_active'] = ($userData->status_name === UserStatusEnum::ACTIVATED->value) ? true : false;
            $userData = ProcessDataHelper::enumsConvertIdToName($userData, [
                new EnumReplacement('owner_type_name', OwnerTypeEnum::class),
                new EnumReplacement('status_name', UserStatusEnum::class)
            ]);
            unset($userData['id']);
            $userData = $userData->toArray() + $ownerData->toArray();
            // array_merge($userData->toArray(), $ownerData->toArray());
            $userData['roles'] = $resultRoles;
            return ResponseHelper::successWithData($userData);
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function retrieveOwnerRoles(Request $request)
    {
        try {
            $userRoles = UserHelper::retrieveOwnerRoles($request->owner_type_id);
            return ResponseHelper::successWithData($userRoles);
            // $attributes = ['id, name, is_mandatory'];
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
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
