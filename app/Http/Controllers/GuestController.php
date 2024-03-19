<?php

namespace App\Http\Controllers;

use App\Models\Guest;
use App\Enums\GenderEnum;
use App\Enums\OwnerTypeEnum;
use App\Helpers\ImageHelper;
use Illuminate\Http\Request;
use App\Helpers\ModifyHelper;

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

}

