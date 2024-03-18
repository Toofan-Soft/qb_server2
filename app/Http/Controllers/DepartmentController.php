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
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Support\Facades\Validator;

class DepartmentController extends Controller
{


    public function addDepartment(Request $request)
    {
        return AddHelper::addModel($request, College::class,  $this->rules($request), 'departments', $request->college_id);
    }

    public function modifyDepartment(Request $request, Department $department)
    {
        return ModifyHelper::modifyModel($request, $department,  $this->rules($request));
    }

    public function deleteDepartment(Department $department)
    {
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
        $attributes = ['id', 'arabic_name', 'logo_url'];
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
            'levels_count' =>  new Enum(LevelsCountEnum::class),
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
