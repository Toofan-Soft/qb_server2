<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Helpers\AddHelper;
use App\Helpers\GetHelper;
use Illuminate\Http\Request;
use App\Helpers\DeleteHelper;
use App\Helpers\ModifyHelper;
use App\Helpers\ResponseHelper;
use App\Helpers\ValidateHelper;
use App\Models\DepartmentCourse;
use App\Models\DepartmentCoursePart;

class DepartmentCoursePartController extends Controller
{
    public function addDepartmentCoursePart(Request $request)
    {
        if (ValidateHelper::validateData($request, $this->rules($request))) {
            return  ResponseHelper::clientError(401);
        }
        $departmentCoursePart = DepartmentCoursePart::create([
            'department_course_id' => $request->department_course_id,
            'course_part_id' => $request->course_part_id,
            'score' => $request->score ?? null,
            'lectures_count' => $request->lectures_count ?? null,
            'lecture_duration' => $request->lecture_duration ?? null,
            'note' => $request->note ?? null,
        ]);
        return ResponseHelper::successWithData(['id' => $departmentCoursePart->id]);
    }

    public function modifyDepartmentCoursePart(Request $request)
    {
        $departmentCoursePart = DepartmentCoursePart::findOrFail($request->id);
        return ModifyHelper::modifyModel($request, $departmentCoursePart,  $this->rules($request));
    }

    public function deleteDepartmentCoursePart(Request $request)
    {
        $departmentCoursePart = DepartmentCoursePart::findOrFail($request->id);
        return DeleteHelper::deleteModel($departmentCoursePart);
    }

    public function retrieveEditableDepartmentCoursePart(Request $request)
    {
        $attributes = ['score', 'lectures_count', 'lecture_duration', 'note'];
        $departmentCourse = DepartmentCoursePart::findOrFail($request->id, $attributes); // edited
        return ResponseHelper::successWithData($departmentCourse);
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
