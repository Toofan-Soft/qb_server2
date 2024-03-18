<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Enums\GenderEnum;
use App\Enums\QualificationEnum;
use App\Helpers\AddHelper;
use App\Helpers\GetHelper;
use Illuminate\Http\Request;
use App\Helpers\DeleteHelper;
use App\Helpers\ModifyHelper;
use App\Helpers\EnumReplacement;

class EmployeeController extends Controller
{
    public function addEmployee(Request $request)
    {

        $userId = 0;
        if ($request->email) {
            //add user
            // $userid = addUser();
            unset($request['email']);
        }
        // return AddHelper::addModel($request, Employee::class,  $this->rules($request)); uncomlete

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
        $attributes = ['id', 'arabic_name', 'gender', 'phone', 'email', 'qualification', 'image_url'];
        $conditionAttribute = ['job_type' => $request->job_type_id];
        $enumReplacements = [
            new EnumReplacement('gender', 'gender_name', GenderEnum::class),
            new EnumReplacement('qualification', 'qualification_name', QualificationEnum::class),
          //  new EnumReplacement('enum_id_column2_db', 'enum_name_name_2',CourseEnum::class),
          ];
          return GetHelper::retrieveModelsWithEnum(Employee::class, $attributes, $conditionAttribute, $enumReplacements);
    }
    public function retrieveBasicEmployeesInfo ()
    {
        $attributes = ['id', 'arabic_name','logo_url'];
        return GetHelper::retrieveModels(Employee::class, $attributes, null);
    }


    public function retrieveEmployee(Request $request)
    {
        $attributes = ['arabic_name','english_name', 'gender', 'phone', 'email', 'job_type', 'specialization', 'qualification', 'image_url'];
        $conditionAttribute = ['id' => $request->id];
          return GetHelper::retrieveModels(Employee::class, $attributes, $conditionAttribute);

          // rename (gender => gender_id , job_type => job_type_id , qualification =>qualification_id )
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
