<?php

namespace App\Policies;

use App\Enums\RoleEnum;
use App\Models\User;
use App\Models\Department;
use App\Enums\UserRoleEnum;
use Illuminate\Auth\Access\Response;

class DepartmentPolicy
{
    // public function add() : bool {
    //     $user = auth()->user();
    //     $userRoles = $user->user_roles;
    //     return true;
    // }
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        //
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Department $department): bool
    {
        //
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return ((int)auth()->user()->user_roles->first()->role_id === UserRoleEnum::SYSTEM_ADMINISTRATOR->value) ? true : false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Department $department): bool
    {
        return ((int)auth()->user()->user_roles->first()->role_id === UserRoleEnum::SYSTEM_ADMINISTRATOR->value) ? true : false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Department $department): bool
    {
        //
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Department $department): bool
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Department $department): bool
    {
        //
    }
}
