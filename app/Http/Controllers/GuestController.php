<?php

namespace App\Http\Controllers;

use App\Models\Guest;
use App\Enums\GenderEnum;
use App\Enums\OwnerTypeEnum;
use App\Helpers\ImageHelper;
use Illuminate\Http\Request;
use App\Helpers\ModifyHelper;
use Illuminate\Validation\Rules\Enum;

class GuestController extends Controller
{
    public function addEmployee(Request $request)
    {

        $guest =  Guest::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'gender' =>  $request->gender ?? GenderEnum::MALE->value,
            'image_url' => ImageHelper::uploadImage($request->image),
        ]);
        if ($request->email) {
            //Add User, email,type,emp_id , password
        }
    }

    public function modifyGuest (Request $request, Guest $guest)
    {
        return ModifyHelper::modifyModel($request, $guest,  $this->rules($request));
    }

    public function addUser(Request $request)
    {
        $roles = $request->roles_ids;

        if($request->owner_type_id === OwnerTypeEnum::EMPLOYEE->value){

        }elseif ($request->owner_type_id === OwnerTypeEnum::LECTURER->value) {

        }elseif ($request->owner_type_id === OwnerTypeEnum::STUDENT->value) {

        }

    }

    public function rules(Request $request): array
    {
        $rules = [
            'name' => 'required|string',
            'phone' => 'nullable|string|unique:guests,phone',
            'image_url' => 'nullable|string',
            'gender' => new Enum(GenderEnum::class), // Assuming GenderEnum holds valid values
            //'user_id' => 'nullable|uuid|unique:users,id',
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

