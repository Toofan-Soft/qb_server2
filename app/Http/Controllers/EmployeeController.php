<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Employee;
use App\Enums\GenderEnum;
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
            new EnumReplacement1( 'gender_name', GenderEnum::class),
            new EnumReplacement1( 'qualification_name', QualificationEnum::class),
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
            // 'arabic_name' => 'required|string|max:255',
            // 'english_name' => 'required|string|max:255',
            // 'logo_url' =>  'image|mimes:jpeg,png,jpg,gif|max:2048', // Adjust max size as needed
            // 'description' => 'nullable|string',
            // 'phone' => 'nullable|string|unique:colleges,phone',
            // 'email' => 'nullable|email|unique:colleges,email',
            // 'facebook' => 'nullable|string|max:255',
            // 'twitter' => 'nullable|string|max:255',
            // 'youtube' => 'nullable|string|max:255',
            // 'telegram' => 'nullable|string|max:255',
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
