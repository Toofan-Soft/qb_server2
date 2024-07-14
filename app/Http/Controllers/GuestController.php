<?php

namespace App\Http\Controllers;

use App\Models\Guest;
use App\Enums\GenderEnum;
use App\Helpers\NullHelper;
use App\Helpers\UserHelper;
use App\Enums\OwnerTypeEnum;
use App\Helpers\ImageHelper;
use Illuminate\Http\Request;
use App\Helpers\ModifyHelper;
use App\Helpers\ResponseHelper;
use App\Helpers\ValidateHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\Enum;

class GuestController extends Controller
{
    public function addGuest(Request $request)
    {
        // يجب ضمان ان عملية اضافة زائر وانشاء حساب له تتم مع بعض
        // if (ValidateHelper::validateData($request, $this->rules($request))) {
        //     return  ResponseHelper::clientError(401);
        // }
        // return ResponseHelper::successWithData(ValidateHelper::validateData($request, $this->rules($request)));
        // return 5;

        Gate::authorize('addGuest', GuestController::class);

        if (ValidateHelper::validateData($request, $this->rules($request))) {
            return ResponseHelper::clientError();
        }
        DB::beginTransaction();
        try {

            $guest =  Guest::create([
                'name' => $request->name,
                'gender' =>  $request->gender_id,
                'phone' => $request->phone ?? null,
                'image_url' => ImageHelper::uploadImage($request->image),
            ]);

            //  $response = UserHelper::addUser($request->email, OwnerTypeEnum::GUEST->value, $guest->id, $request->password);
            //  return ResponseHelper::successWithToken($response);

            // return ResponseHelper::successWithData(UserHelper::addUser($request->email, OwnerTypeEnum::GUEST->value, $guest->id, $request->password));

            if (!UserHelper::addUser($request->email, OwnerTypeEnum::GUEST->value, $guest->id, $request->password)) {
                return ResponseHelper::serverError();
                // return ResponseHelper::serverError1("hellow");
                // return ResponseHelper::serverError('لم يتم اضافة حساب لهذا الموظف');
            }
            DB::commit();
            return ResponseHelper::success();
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseHelper::serverError();
        }
    }

    public function modifyGuest(Request $request)
    {
        Gate::authorize('modifyGuest', GuestController::class);

        if (ValidateHelper::validateData($request, $this->rules($request))) {
            return  ResponseHelper::clientError();
        }
        try {
            $guest = Guest::where('user_id', auth()->user()->id)->first();
            $guest->update([
                'name' => $request->name ??  $guest->name,
                'phone' => $request->phone ?? $guest->phone,
                'gender' =>  $request->gender_id ??  $guest->gender,
                'image_url' => ImageHelper::updateImage($request->image,  $guest->image_url)
            ]);
            return ResponseHelper::success();
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function retrieveEditableGuestProfile()
    {
        Gate::authorize('retrieveEditableGuestProfile', GuestController::class);

        $attributes = ['name', 'gender as gender_id', 'phone', 'image_url'];
        try {
            $guest = Guest::where('user_id', '=', auth()->user()->id)->get($attributes);
            $guest = NullHelper::filter($guest);

            return ResponseHelper::successWithData($guest);
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function rules(Request $request): array
    {
        $rules = [
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'phone' => 'nullable|integer',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'gender_id' => ['required', new Enum(GenderEnum::class)], // Assuming GenderEnum holds valid values
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
