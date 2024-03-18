<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CourseLecturer;

class CourseLecturerController extends Controller
{
    public function addCourseLecturer(Request $request)
    {

        CourseLecturer::create([
            'department_course_part_id' => $request->department_course_id,
            'lecturer_id' => $request->course_part_id,
            'academic_year' => now()->format('Y'), ///need to 
        ]);

       // return AddHelper::addModel($request, DepartmentCourse::class,  $this->rules($request), 'department_course_parts', $request->department_course_id);
    }

    public function modifyCourseLecturer(Request $request, CourseLecturer $department)
    {
        return ModifyHelper::modifyModel($request, $department,  $this->rules($request));
    }

    public function deleteCourseLecturer(CourseLecturer $department)
    {
       return DeleteHelper::deleteModel($department);
    }



    public function rules(Request $request): array
    {
        $rules = [
            // 'arabic_name' => 'required|string|max:255',
            // 'english_name' => 'required|string|max:255',
            // 'logo_url' =>  'image|mimes:jpeg,png,jpg,gif|max:2048',
            // 'levels_count' =>  new Enum(LevelsCountEnum::class),
            // 'description' => 'nullable|string',
            // 'college_id' => 'required',
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
