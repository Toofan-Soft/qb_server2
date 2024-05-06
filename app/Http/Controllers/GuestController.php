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
        // يجب ضمان ان عملية اضافة زائر وانشاء حساب له تتم مع بعض
        // if (ValidateHelper::validateData($request, $this->rules($request))) {
        //     return  ResponseHelper::clientError(401);
        // }
        if ($x = ValidateHelper::validateData($request, $this->rules($request))) {
            return ResponseHelper::clientError1($x);
        }
        $guest =  Guest::create([
            'name' => $request->name,
            'gender' =>  $request->gender_id,
            'phone' => $request->phone ?? null,
            'image_url' => ImageHelper::uploadImage($request->image) ,
        ]);

         $response = UserHelper::addUser($request->email, OwnerTypeEnum::GUEST->value, $guest->id, $request->password);
         return ResponseHelper::successWithToken($response);

        }

    public function modifyGuest (Request $request)
    {

        if (ValidateHelper::validateData($request, $this->rules($request))) {
            return  ResponseHelper::clientError(401);
        }

        $guest = Guest::where('user_id', auth()->user()->id)->first();
        return Guest::all();
        $guest->update([
            'name' => $request->name ??  $guest->name ,
            'phone' => $request->phone ?? $guest->phone ,
            'gender' =>  $request->gender_id ??  $guest->gender ,
            'image_url' => ImageHelper::updateImage($request->image,  $guest->image_url )
        ]);
        return ResponseHelper::success();
    }

    public function retrieveEditableGuestProfile()
    {
        $attributes = ['name', 'gender as gender_id', 'phone', 'image_url'];
        $guest = Guest::where('user_id', '=', auth()->user()->id)->get($attributes);
        return ResponseHelper::successWithData($guest);
    }

    public function rules(Request $request): array
    {
        $rules = [
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'phone' => 'nullable|string|unique:guests,phone',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
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

