<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Employee;
use App\Enums\GenderEnum;
use App\Enums\JobTypeEnum;
use App\Helpers\AddHelper;
use App\Helpers\GetHelper;
use App\Helpers\ImageHelper;
use Illuminate\Http\Request;
use App\Helpers\DeleteHelper;
use App\Helpers\ModifyHelper;
use App\Enums\QualificationEnum;
use App\Helpers\EnumReplacement;
use App\Helpers\EnumReplacement1;
use App\Helpers\ColumnReplacement;
use App\Helpers\ProcessDataHelper;
use Illuminate\Validation\Rules\Enum;

class EmployeeController extends Controller
{
    public function addEmployee(Request $request)
    {

       $employee =  Employee::create([
            'arabic_name' =>  $request->arabic_name,
            'english_name' =>  $request->english_name,
            'phone' => $request->phone,
            'image_url' => ImageHelper::uploadImage($request->image),
            'job_type' => $request->job_type,
            'qualification' =>  $request->qualification,
            'specialization' =>  $request->specialization,
            'gender' =>  $request->gender ?? GenderEnum::MALE->value,
        ]);
        if ($request->email) {
            //Add User, email,type,emp_id
        }


    }

    public function modifyEmployee (Request $request, Employee $employee)
    {
        return ModifyHelper::modifyModel($request, $employee,  $this->rules($request));
    }


    public function deleteEmployee (Employee $employee)
    {
        return DeleteHelper::deleteModel($employee);
    }

    public function retrieveEmployees (Request $request)
    {
        $attributes = ['id', 'arabic_name', 'gender as gender_name', 'phone', 'user_id as email', 'qualification as qualification_name', 'image_url'];
        $enumReplacements = [
            new EnumReplacement( 'gender_name', GenderEnum::class),
            new EnumReplacement( 'qualification_name', QualificationEnum::class),
          //  new EnumReplacement('enum_id_column2_db', 'enum_name_name_2',CourseEnum::class),
          ];
          $columnReplacements = [
            new ColumnReplacement('email', 'email', User::class)
          ];

          $employees =Employee::where('job_type', $request->job_type_id)->get($attributes);
          $employees =  ProcessDataHelper::enumsConvertIdToName($employees, $enumReplacements);
          $employees = ProcessDataHelper::columnConvertIdToName($employees, $columnReplacements);
          return $employees;
    }

    public function retrieveEmployee(Request $request)
    {
        $attributes = ['arabic_name','english_name', 'gender as gender_id', 'phone', 'user_id as email', 'job_type as job_type_id', 'specialization', 'qualification as qualification_id', 'image_url'];
        $columnReplacements = [
            new ColumnReplacement('email', 'email', User::class)
          ];
        $employee =Employee::find($request->id)->get($attributes);
        $employee = ProcessDataHelper::columnConvertIdToName($employee, $columnReplacements);
          //return GetHelper::retrieveModels(Employee::class, $attributes, $conditionAttribute);

    }


    public function rules(Request $request): array
    {
        $rules = [
            'arabic_name' => 'required|string',
            'english_name' => 'required|string',
            'phone' => 'nullable|string|unique:employees,phone',
            'image_url' => 'nullable|string',
            'job_type' => ['required',new Enum (JobTypeEnum::class)], // Assuming JobTypeEnum holds valid values
            'qualification' => ['required',new Enum (QualificationEnum::class)], // Assuming QualificationEnum holds valid values
            'specialization' => 'nullable|string',
            'gender' => ['required',new Enum (GenderEnum::class)], // Assuming GenderEnum holds valid values
           // 'user_id' => 'nullable|uuid|unique:users,id',
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
