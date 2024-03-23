<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Helpers\AddHelper;
use App\Helpers\GetHelper;
use Illuminate\Http\Request;
use App\Helpers\DeleteHelper;
use App\Helpers\ModifyHelper;
use App\Models\DepartmentCourse;
use App\Models\DepartmentCoursePart;

class DepartmentCoursePartController extends Controller
{
    public function addDepartmentCoursePart(Request $request)
    {

        DepartmentCoursePart::create([
            'department_course_id' => $request->department_course_id,
            'course_part_id' => $request->course_part_id,
            'score' => $request->score ?? null,
            'lectures_count' => $request->lectures_count ?? null ,
            'lecture_duration' => $request->lecture_duration ?? null,
            'note' => $request->note ?? null,
        ]);
        //return AddHelper::addModel($request, DepartmentCourse::class,  $this->rules($request), 'department_course_parts', $request->department_course_id);
    }

    public function modifyDepartmentCoursePart(Request $request, DepartmentCoursePart $department)
    {
        return ModifyHelper::modifyModel($request, $department,  $this->rules($request));
    }

    public function deleteDepartmentCoursePart(DepartmentCoursePart $department)
    {
       return DeleteHelper::deleteModel($department);
    }



    public function rules(Request $request): array
    {
        $rules = [
            'department_course_id' => 'required|exists:department_courses,id',
            'course_part_id' => 'required|exists:course_parts,id',
            'note' => 'nullable|string',
            'score' => 'nullable|integer',
            'lectures_count' => 'nullable|integer',
            'lecture_duration' => 'nullable|integer',
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
