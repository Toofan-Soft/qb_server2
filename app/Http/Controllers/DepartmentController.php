<?php

namespace App\Http\Controllers;

use App\Models\College;
use App\Helpers\AddHelper;
use App\Helpers\GetHelper;
use App\Models\Department;
use App\Helpers\ImageHelper;
use Illuminate\Http\Request;
use App\Helpers\DeleteHelper;
use App\Helpers\ModifyHelper;
use App\Enums\LevelsCountEnum;
use App\Helpers\ResponseHelper;
use App\Helpers\ValidateHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Support\Facades\Validator;

class DepartmentController extends Controller
{


    public function addDepartment(Request $request)
    {
        if($failed = ValidateHelper::validateData($request, $this->rules($request))){
            return  ResponseHelper::clientError($failed);
        }
        $college = College::findOrFail($request->college_id);
        $college->departments()->create([
            'arabic_name' => $request->arabic_name,
            'english_name' => $request->english_name,
            'levels_count' => $request->phone ?? null,
            'description' => $request->description?? null,
            'logo_url' => ImageHelper::uploadImage($request->logo)
        ]);
       return ResponseHelper::success();
    }

    public function modifyDepartment(Request $request)
    {
        if($failed = ValidateHelper::validateData($request, $this->rules($request))){
            return  ResponseHelper::clientError($failed);
        }

        $department = Department::findOrFail($request->id);
        $department->update([
            'arabic_name' => $request->arabic_name,
            'english_name' => $request->english_name,
            'levels_count' => $request->phone ?? null,
            'description' => $request->description?? null,
            'logo_url' => ImageHelper::updateImage($request->logo, $department->logo_url)
        ]);
       return ResponseHelper::success();

    }

    public function deleteDepartment(Request $request)
    {
        $department = Department::findOrFail($request->id);
       return DeleteHelper::deleteModel($department);
    }

    public function retrieveDepartments(Request $request)
    {
        $attributes = ['id', 'arabic_name', 'english_name', 'levels_count', 'logo_url'];
        $conditionAttribute = ['college_id' => $request->college_id];
        return GetHelper::retrieveModels(Department::class, $attributes, $conditionAttribute);
    }


    public function retrieveBasicDepartmentsInfo(Request $request)
    {
        $attributes = ['id', 'arabic_name as name', 'logo_url'];
        $conditionAttribute = ['college_id', $request->college_id];
        return GetHelper::retrieveModels(Department::class, $attributes, $conditionAttribute);
    }


    public function retrieveDepartment(Request $request)
    {
        $attributes = ['arabic_name', 'english_name', 'levels_count', 'logo_url', 'description'];
        $conditionAttribute = ['id', $request->id];
        return GetHelper::retrieveModels(Department::class, $attributes, $conditionAttribute);
    }


    public function rules(Request $request): array
    {
        $rules = [
            'arabic_name' => 'required|string|max:255',
            'english_name' => 'required|string|max:255',
            'logo_url' =>  'image|mimes:jpeg,png,jpg,gif|max:2048',
            'levels_count' =>  ['required', new Enum(LevelsCountEnum::class)],
            'description' => 'nullable|string',
            'college_id' => 'required',
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
