<?php

namespace App\Http\Controllers;

use App\Models\Guest;
use App\Enums\GenderEnum;
use App\Helpers\UserHelper;
use App\Enums\OwnerTypeEnum;
use App\Helpers\ImageHelper;
use Illuminate\Http\Request;
use App\Helpers\ModifyHelper;
use App\Helpers\ResponseHelper;
use App\Helpers\ValidateHelper;
use Illuminate\Validation\Rules\Enum;

class GuestController extends Controller
{
    public function addGuest(Request $request)
    {
        if ($failed = ValidateHelper::validateData($request, $this->rules($request))) {
            return  ResponseHelper::clientError($failed);
        }
        $guest =  Guest::create([
            'name' => $request->name,
            'phone' => $request->phone ?? null,
            'gender' =>  $request->gender_id,
            'image_url' => ImageHelper::uploadImage($request->image),
        ]);
        if(! UserHelper::addUser($request->email, OwnerTypeEnum::GUEST->value, $guest->id, $request->pssword)) {
            return ResponseHelper::serverError('لم يتم اضافة حساب لهذا الزائر');

        return ResponseHelper::success();
       }
    }
    public function modifyGuest (Request $request)
    {

        if ($failed = ValidateHelper::validateData($request, $this->rules($request))) {
            return  ResponseHelper::clientError($failed);
        }

        $guest = Guest::findOrFail(auth()->user()->id);
        $guest->update([
            'name' => $request->name ??  $guest->name ,
            'phone' => $request->phone ?? $guest->phone ,
            'gender' =>  $request->gender_id ??  $guest->gender ,
            'image_url' => ImageHelper::updateImage($request->image,  $guest->image_url )
        ]);
        return ResponseHelper::success();
    }


    public function rules(Request $request): array
    {
        $rules = [
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'phone' => 'nullable|string|unique:guests,phone',
            'image' => 'nullable|string',
            'gender_id' => ['required',new Enum(GenderEnum::class)], // Assuming GenderEnum holds valid values
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

