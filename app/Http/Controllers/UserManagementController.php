<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Guest;
use App\Enums\RoleEnum;
use App\Helpers\NullHelper;
use App\Helpers\UserHelper;
use App\Enums\OwnerTypeEnum;
use App\Helpers\ImageHelper;
use Illuminate\Http\Request;
use App\Enums\UserStatusEnum;
use App\Helpers\DeleteHelper;
use App\Helpers\LanguageHelper;
use App\Helpers\ResponseHelper;
use App\Helpers\ValidateHelper;
use App\Helpers\EnumReplacement;
use App\Helpers\EnumReplacement1;
use App\Helpers\ProcessDataHelper;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\Enum;

class UserManagementController extends Controller
{
    public function addUser(Request $request)
    {
        // Gate::authorize('addUser', UserManagementController::class);

        if (ValidateHelper::validateData($request, $this->rules($request))) {
            return  ResponseHelper::clientError();
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
        Gate::authorize('modifyUserRoles', UserManagementController::class);

        if (ValidateHelper::validateData($request, $this->rules($request))) {
            return  ResponseHelper::clientError();
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
        Gate::authorize('changeUserStatus', UserManagementController::class);
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
        Gate::authorize('deleteUser', UserManagementController::class);

        try {
            $user = User::findOrFail($request->id);
            $user->delete();
            return ResponseHelper::success();
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function retrieveUsers(Request $request)
    {
        Gate::authorize('retrieveUsers', UserManagementController::class);

        $users = [];
        $ownerTable = '';
        $nameColumn = LanguageHelper::getNameColumnName(null, null);
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
        Gate::authorize('retrieveUser', UserManagementController::class);

        try {
            $userData = User::findOrFail(
                $request->id,
                ['id', 'email', 'status as status_name', 'owner_type as owner_type_name']
            );

            if (intval($userData->owner_type_name) === OwnerTypeEnum::GUEST->value) {
                $ownerData = $userData->guest()->get(['name', 'image_url']);
                $userRoles = RoleEnum::getOwnerRolesWithMandatory(intval($userData->owner_type_name));
            } elseif (intval($userData->owner_type_name) === OwnerTypeEnum::STUDENT->value) {
                $ownerData = $userData->student()->get([LanguageHelper::getNameColumnName(null, 'name'), 'image_url']);
                $userRoles = RoleEnum::getOwnerRolesWithMandatory(intval($userData->owner_type_name));
            } elseif (intval($userData->owner_type_name) === OwnerTypeEnum::EMPLOYEE->value) {
                $ownerData = $userData->employee()->get([LanguageHelper::getNameColumnName(null, 'name'), 'image_url', 'job_type']);
                $userRoles = RoleEnum::getOwnerRolesWithMandatory(intval($userData->owner_type_name), intval($ownerData->job_type));
                unset($ownerData['job_type']);
            }

            $currentUserRoles = $userData->user_roles()->pluck('role_id')->toArray();
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

            $userData = $userData->toArray() + $ownerData->first()->toArray();
            // $userData = $userData + $ownerData->toArray();
            // $userData = $userData->toArray() + $ownerData->toArray();
            // array_merge($userData->toArray(), $ownerData->toArray());
            $userData['roles'] = $resultRoles;

            $userData = NullHelper::filter($userData);

            return ResponseHelper::successWithData($userData);
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function retrieveOwnerRoles(Request $request)
    {
        Gate::authorize('retrieveOwnerRoles', UserManagementController::class);

        try {
            $ownerRoles = RoleEnum::getOwnerRolesWithMandatory($request->owner_type_id, $request->job_type_id);
            return ResponseHelper::successWithData($ownerRoles);
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
            'owner_id' => 'required',
            'id' => 'nullable',    // it is the same of owner_id  but from another process
            'roles_ids'                => 'nullable|array',
            'roles_ids.*'              => ['nullable', new Enum(RoleEnum::class)],
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
