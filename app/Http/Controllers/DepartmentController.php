<?php

namespace App\Http\Controllers;

use App\Models\College;
use App\Helpers\AddHelper;
use App\Helpers\GetHelper;
use App\Models\Department;
use App\Helpers\NullHelper;
use App\Helpers\ImageHelper;
use Illuminate\Http\Request;
use App\Helpers\DeleteHelper;
use App\Helpers\ModifyHelper;
use App\Enums\LevelsCountEnum;
use App\Helpers\LanguageHelper;
use App\Helpers\ResponseHelper;
use App\Helpers\ValidateHelper;
use App\Helpers\EnumReplacement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Support\Facades\Validator;
use App\Helpers\Roles\ByteArrayValidationRule;

class DepartmentController extends Controller
{
    public function addDepartment(Request $request)
    {
        Gate::authorize('addDepartment', DepartmentController::class);

        if (ValidateHelper::validateData($request, $this->rules($request))) {
            return  ResponseHelper::clientError();
        }

        try {
            $college = College::findOrFail($request->college_id);
            $college->departments()->create([
                'arabic_name' => $request->arabic_name,
                'english_name' => $request->english_name,
                'levels_count' => $request->levels_count_id,
                'description' => $request->description ?? null,
                'logo_url' => ImageHelper::uploadImage($request->logo)
            ]);
            return ResponseHelper::success();
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function modifyDepartment(Request $request)
    {
        Gate::authorize('modifyDepartment', DepartmentController::class);
        if (ValidateHelper::validateData($request, $this->rules($request))) {
            return  ResponseHelper::clientError();
        }

        try {
            $department = Department::findOrFail($request->id);
            $department->update([
                'arabic_name' => $request->arabic_name ?? $department->arabic_name,
                'english_name' => $request->english_name ?? $department->english_name,
                'levels_count' => $request->levels_count_id ?? $department->levels_count,
                'description' => $request->description ?? $department->description,
                'logo_url' => ImageHelper::updateImage($request->logo, $department->logo_url)
            ]);
            return ResponseHelper::success();
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function deleteDepartment(Request $request)
    {
        Gate::authorize('deleteDepartment', DepartmentController::class);
        if (ValidateHelper::validateData($request, [
            'id' => 'required|integer'
        ])) {
            return  ResponseHelper::clientError();
        }
        try {
            $department = Department::findOrFail($request->id);
            $department->delete();
            return ResponseHelper::success();
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function retrieveDepartments(Request $request)
    {
        Gate::authorize('retrieveDepartments', DepartmentController::class);
        if (ValidateHelper::validateData($request, [
            'college_id' => 'required|integer'
        ])) {
            return  ResponseHelper::clientError();
        }
        try {
            $attributes = ['id', 'arabic_name', 'english_name', 'levels_count as levels_count_name', 'logo_url'];
            $conditionAttribute = ['college_id' => $request->college_id];
            $enumReplacements = [
                new EnumReplacement('levels_count_name', LevelsCountEnum::class),
            ];
            $departments = GetHelper::retrieveModels(Department::class, $attributes, $conditionAttribute, $enumReplacements);
            $departments = NullHelper::filter($departments);
            return ResponseHelper::successWithData($departments);
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }


    public function retrieveBasicDepartmentsInfo(Request $request)
    {
        Gate::authorize('retrieveBasicDepartmentsInfo', DepartmentController::class);
        if (ValidateHelper::validateData($request, [
            'college_id' => 'required|integer'
        ])) {
            return  ResponseHelper::clientError();
        }
        try {
            $attributes = ['id', LanguageHelper::getNameColumnName(null, 'name'), 'logo_url'];
            $conditionAttribute = ['college_id' => $request->college_id];
            $departments = GetHelper::retrieveModels(Department::class, $attributes, $conditionAttribute);
            $departments = NullHelper::filter($departments);
            return ResponseHelper::successWithData($departments);
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }


    public function retrieveDepartment(Request $request)
    {
        Gate::authorize('retrieveDepartment', DepartmentController::class);
        if (ValidateHelper::validateData($request, [
            'id' => 'required|integer'
        ])) {
            return  ResponseHelper::clientError();
        }
        try {
            $attributes = ['arabic_name', 'english_name', 'levels_count as levels_count_name', 'logo_url', 'description'];
            $conditionAttribute = ['id' => $request->id];
            $enumReplacements = [
                new EnumReplacement('levels_count_name', LevelsCountEnum::class),
            ];
            $department = GetHelper::retrieveModel(Department::class, $attributes, $conditionAttribute, $enumReplacements);
            $department = NullHelper::filter($department);
            return ResponseHelper::successWithData($department);
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function retrieveEditableDepartment(Request $request)
    {
        Gate::authorize('retrieveEditableDepartment', DepartmentController::class);
        if (ValidateHelper::validateData($request, [
            'id' => 'required|integer'
        ])) {
            return  ResponseHelper::clientError();
        }
        try {
            $attributes = ['arabic_name', 'english_name', 'levels_count as levels_count_id', 'logo_url', 'description'];
            $conditionAttribute = ['id' => $request->id];
            $department = GetHelper::retrieveModel(Department::class, $attributes, $conditionAttribute);
            $department = NullHelper::filter($department);
            return ResponseHelper::successWithData($department);
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }


    public function rules(Request $request): array
    {
        $rules = [
            'arabic_name' => 'required|string|unique:departments,arabic_name|max:255',
            'english_name' => 'required|string|unique:departments,english_name|max:255',
            'logo' =>  ['nullable', new ByteArrayValidationRule],
            'levels_count_id' =>  ['required', new Enum(LevelsCountEnum::class)],
            'description' => 'nullable|string',
            'college_id' => 'required|exists:colleges,id',

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
