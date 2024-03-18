<?php

namespace App\Http\Controllers;

use App\Helpers\AddHelper;
use App\Helpers\DeleteHelper;
use App\Models\College;
use App\Helpers\GetHelper;
use App\Helpers\ImageHelper;
use Illuminate\Http\Request;
use App\Helpers\ModifyHelper;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\CollegeRequest;
use App\Http\Resources\CollegeResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class CollegeController extends Controller
{

    public function addCollege(Request $request)
    {
      return AddHelper::addModel($request, College::class,  $this->rules($request));
    }

    public function modifyCollege (Request $request, College $college)
    {
        return ModifyHelper::modifyModel($request, $college,  $this->rules($request));
    }


    public function deleteCollege (College $college)
    {
        return DeleteHelper::deleteModel($college);
    }

    public function retrieveColleges ()
    {
        $attributes = ['id', 'arabic_name', 'english_name', 'phone', 'email', 'logo_url'];
        return GetHelper::retrieveModels(College::class, $attributes, null);
    }
    public function retrieveBasicCollegesInfo ()
    {
        $attributes = ['id', 'arabic_name','logo_url'];
        return GetHelper::retrieveModels(College::class, $attributes, null);
    }


    public function retrieveCollege( $id)
    {
        // $attributes = [ 'arabic_name', 'english_name', 'phone', 'email', 'description', 'youtube', 'x_platform', 'facebook', 'telegram', 'logo_url'];
        // $conditionAttribute = ['id' => $request->id];
        // return GetHelper::retrieveModels(College::class, $attributes, $conditionAttribute);

        $college = College::find($id);

        return response()->json(['data' => $college  ], 200);
    }


    public function rules(Request $request): array
    {
        $rules = [
            'arabic_name' => 'required|string|max:255',
            'english_name' => 'required|string|max:255',
            'logo_url' =>  'image|mimes:jpeg,png,jpg,gif|max:2048', // Adjust max size as needed
            'description' => 'nullable|string',
            'phone' => 'nullable|string|unique:colleges,phone',
            'email' => 'nullable|email|unique:colleges,email',
            'facebook' => 'nullable|string|max:255',
            'twitter' => 'nullable|string|max:255',
            'youtube' => 'nullable|string|max:255',
            'telegram' => 'nullable|string|max:255',
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
