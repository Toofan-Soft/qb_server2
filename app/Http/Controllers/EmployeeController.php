<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Employee;
use App\Enums\GenderEnum;
use App\Enums\JobTypeEnum;
use App\Helpers\AddHelper;
use App\Helpers\GetHelper;
use App\Helpers\NullHelper;
use App\Helpers\UserHelper;
use App\Enums\OwnerTypeEnum;
use App\Helpers\ImageHelper;
use Illuminate\Http\Request;
use App\Helpers\DeleteHelper;
use App\Helpers\ModifyHelper;
use App\Helpers\ResponseHelper;
use App\Helpers\ValidateHelper;
use App\Enums\QualificationEnum;
use App\Helpers\EnumReplacement;
use App\Helpers\EnumReplacement1;
use Illuminate\Http\JsonResponse;
use App\Helpers\ColumnReplacement;
use App\Helpers\ProcessDataHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Enum;

class EmployeeController extends Controller
{
    public function addEmployee(Request $request)
    {
        if (ValidateHelper::validateData($request, $this->rules($request))) {
            return  ResponseHelper::clientError(401);
        }
        try {
            $employee =  Employee::create([
                'arabic_name' =>  $request->arabic_name,
                'english_name' =>  $request->english_name,
                'phone' => $request->phone ?? null,
                'image_url' => ImageHelper::uploadImage($request->image),
                'job_type' => $request->job_type_id,
                'qualification' =>  $request->qualification_id,
                'specialization' =>  $request->specialization ?? null,
                'gender' =>  $request->gender_id,
            ]);

            if ($request->email) {
                if (!UserHelper::addUser($request->email, OwnerTypeEnum::EMPLOYEE->value, $employee->id)) {
                    //  return ResponseHelper::serverError('لم يتم اضافة حساب لهذا الموظف');
                    return ResponseHelper::serverError(401);
                }

                //!!!!!!!!!!** this two lines only for test , then will delete them
                // $response = UserHelper::addUser($request->email, $ownerTypeId, $employee->id);
                // return ResponseHelper::successWithToken($response);
            }
            return ResponseHelper::success();
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function modifyEmployee(Request $request)
    {
        if (ValidateHelper::validateData($request, $this->rules($request))) {
            return  ResponseHelper::clientError(401);
        }
        try {
            $employee = Employee::findOrFail($request->id);

            $employee->update([
                'arabic_name' =>  $request->arabic_name ?? $employee->arabic_name,
                'english_name' =>  $request->english_name ?? $employee->english_name,
                'phone' => $request->phone ?? $employee->phone,
                'image_url' => ImageHelper::updateImage($request->image, $employee->image_url),
                'job_type' => $request->job_type_id ?? $employee->job_type,
                'qualification' =>  $request->qualification_id ?? $employee->qualification,
                'specialization' =>  $request->specialization ?? $employee->specialization,
                'gender' =>  $request->gender_id ?? $employee->gender,
            ]);
            return ResponseHelper::success();
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function deleteEmployee(Request $request)
    {
        DB::beginTransaction();
        try {
            $employee = Employee::findOrFail($request->id);
            $employee->user()->delete();
            $employee->delete();
            DB::commit();
            return ResponseHelper::success();
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseHelper::serverError();
        }
    }

    public function retrieveEmployees(Request $request)
    {
        $attributes = ['id', 'arabic_name as name', 'gender as gender_name', 'phone', 'user_id as email', 'qualification as qualification_name', 'image_url'];
        $conditionAttribute = ['job_type' => $request->job_type_id];
        $enumReplacements = [
            new EnumReplacement('gender_name', GenderEnum::class),
            new EnumReplacement('qualification_name', QualificationEnum::class),
        ];

        $columnReplacements = [
            new ColumnReplacement('email', 'email', User::class)
        ];
        try {
            $employees = GetHelper::retrieveModels(Employee::class, $attributes, $conditionAttribute, $enumReplacements, $columnReplacements);
    
            $employees = NullHelper::filter($employees);
    
            return ResponseHelper::successWithData($employees);
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function retrieveEmployee(Request $request)
    {
        $attributes = ['arabic_name', 'english_name', 'gender as gender_name', 'phone', 'user_id as email', 'job_type as job_type_name', 'specialization', 'qualification as qualification_name', 'image_url'];
        $conditionAttribute = ['id' => $request->id];
        $enumReplacements = [
            new EnumReplacement('gender_name', GenderEnum::class),
            new EnumReplacement('job_type_name', JobTypeEnum::class),
            new EnumReplacement('qualification_name', QualificationEnum::class),
        ];
        $columnReplacements = [
            new ColumnReplacement('email', 'email', User::class)
        ];
        try {
            $employee = GetHelper::retrieveModel(Employee::class, $attributes, $conditionAttribute, $enumReplacements, $columnReplacements);
    
            $employee = NullHelper::filter($employee);
            return ResponseHelper::successWithData($employee);
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function retrieveEditableEmployee(Request $request)
    {
        $attributes = ['arabic_name', 'english_name', 'gender as gender_id', 'phone', 'job_type as job_type_id', 'specialization', 'qualification as qualification_id', 'image_url'];
        $conditionAttribute = ['id' => $request->id];
        try {
            $employee = GetHelper::retrieveModel(Employee::class, $attributes, $conditionAttribute);
    
            $employee = NullHelper::filter($employee);
    
            return ResponseHelper::successWithData($employee);
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function rules(Request $request): array
    {
        $rules = [
            'arabic_name' => 'required|string',
            'english_name' => 'required|string',
            'phone' => 'nullable|integer',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'job_type_id' => ['required', new Enum(JobTypeEnum::class)], // Assuming JobTypeEnum holds valid values
            'qualification_id' => ['required', new Enum(QualificationEnum::class)], // Assuming QualificationEnum holds valid values
            'specialization' => 'nullable|string',
            'gender_id' => ['required', new Enum(GenderEnum::class)], // Assuming GenderEnum holds valid values
            // 'user_id' => 'nullable|uuid|unique:users,id',
            // يتم اضافة الايميل وجعله قابل للنل ، وفريد

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
