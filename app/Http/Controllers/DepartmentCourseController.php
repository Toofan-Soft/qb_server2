<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\College;
use App\Enums\LevelsEnum;
use App\Helpers\AddHelper;
use App\Helpers\GetHelper;
use App\Models\CoursePart;
use App\Models\Department;
use App\Enums\SemesterEnum;
use Illuminate\Http\Request;
use App\Helpers\DeleteHelper;
use App\Helpers\ModifyHelper;
use App\Enums\CoursePartsEnum;
use App\Models\DepartmentCourse;
use Illuminate\Validation\Rules\Enum;

class DepartmentCourseController extends Controller
{
    public function addDepartmentCourse(Request $request)
    {
        ////without rules
         DepartmentCourse::create([
            'department_id' => $request->department_id,
            'course_id' => $request->course_id,
            'level' => $request->level,
            'semester' => $request->semester,
        ]);

       // return AddHelper::addModel($request, Department::class,  $this->rules($request), 'department_courses', $request->department_id);
    }

    public function modifyDepartmentCourse(Request $request, DepartmentCourse $departmentCourse)
    {
        return ModifyHelper::modifyModel($request, $departmentCourse,  $this->rules($request));
    }

    public function deleteDepartmentCourse(DepartmentCourse $departmentCourse)
    {
       return DeleteHelper::deleteModel($departmentCourse);
    }

    public function retrieveDepartmentCourses(Request $request)
    {
        $department= Department::find($request->department_id);
        $departmentCourses = $department->department_courses()->get( ['id', 'course_id', 'level', 'semester']);
        foreach ($departmentCourses as $departmentCourse ) {
            $departmentCourse['course_name'] = Course::where('id', $departmentCourse->course_id)->get(['arabic_name']);
            $departmentCourse['level_name'] = LevelsEnum::getNameByNumber($departmentCourse->level);
            $departmentCourse['semester_name'] = SemesterEnum::getNameByNumber($departmentCourse->semester);
            unset($departmentCourse['course_id']);
            unset($departmentCourse['level']);
            unset($departmentCourse['semester']);
        }
        return $departmentCourses;

        //we optimized the code by this :
        // $department = Department::with([
        //     'department_courses:id,course_id,level,semester',
        //     'department_courses.course:id,arabic_name', // Eager load related course with arabic_name
        //     'department_courses.level:number,name as level_name', // Eager load level with renamed attribute
        //     'department_courses.semester:number,name as semester_name', // Eager load semester with renamed attribute
        // ])->find($request->department_id);

        // // No need to modify $departmentCourses, data is already available through eager loading

        // // Optional: Return only necessary attributes (using map)
        // return $department->department_courses->map(function ($departmentCourse) {
        //     return [
        //         'id' => $departmentCourse->id,
        //         'course_name' => $departmentCourse->course->arabic_name,
        //         'level_name' => $departmentCourse->level_name,
        //         'semester_name' => $departmentCourse->semester_name,
        //     ];
        // });


    }

     ////// **** NEED TO GOIN BETWEEN MORE THAN ONEW
     public function retrieveCourseDepartments(Request $request)
     {
        $course = Course::find($request->course_id);
        $departmentCourses = $course->department_courses()->get( ['id', 'department_id', 'level', 'semester']);
        foreach ($departmentCourses as $departmentCourse ) {
            $departmentCourse['department_name'] = Department::where('id', $departmentCourse->department_id)->get(['arabic_name']);
            $departmentCourse['college_name'] = College::where('id', $departmentCourse->department_id)->get(['arabic_name']);
            $departmentCourse['level_name'] = LevelsEnum::getNameByNumber($departmentCourse->level);
            $departmentCourse['semester_name'] = SemesterEnum::getNameByNumber($departmentCourse->semester);
            unset($departmentCourse['department_id']);
            unset($departmentCourse['level']);
            unset($departmentCourse['semester']);
        }
        return $departmentCourses;

        //we can optimize to this :
        // $course = Course::with([
        //     'department_courses:id,department_id,level,semester', // Eager load with required attributes
        //     'department_courses.department:id,arabic_name.college:id,arabic_name as college_name', // Nested eager load
        //     'department_courses.level:number,name as level_name',
        //     'department_courses.semester:number,name as semester_name',
        //   ])->find($request->course_id);

        //   // No need to modify $departmentCourses, data is already available through eager loading

        //   // Return only necessary attributes (optional)
        //   return $course->department_courses->map(function ($departmentCourse) {
        //     return [
        //       'id' => $departmentCourse->id,
        //       'department_name' => $departmentCourse->department->arabic_name,
        //       'college_name' => $departmentCourse->department->college->arabic_name,
        //       'level_name' => $departmentCourse->level_name,
        //       'semester_name' => $departmentCourse->semester_name,
        //     ];
        //   });
    }

    ////// **** NEED TO GOIN BETWEEN MORE THAN ONEW

    public function retrieveDepartmentLevelCourses(Request $request)
    {
        $departmentCourses = DepartmentCourse::where('department_id', $request->department_id)->where('level', $request->level_id)->get( ['id', 'course_id', 'semester']);
        $semesters = [];
        foreach ($departmentCourses as $departmentCourse ) {
            $semesters = [
                'id' => $departmentCourse['semester'],
                'name' => SemesterEnum::getNameByNumber($departmentCourse->semester),
                'department_courses' => [
                    'id' => $departmentCourse['id'],
                    'name' => Course::where('id', $departmentCourse->course_id)->get(['arabic_name']) /// test >> get only value
                ]
                ];
        }
        return $semesters;

        // if we optimized that :
            // $departmentCourses = DepartmentCourse::with([
            //     'semester:number,name as semester_name', // Eager load semester with renamed attribute
            //     'course:id,arabic_name', // Eager load course with arabic_name
            //   ])
            //   ->where('department_id', $request->department_id)
            //   ->where('level', $request->level_id)
            //   ->get();

            //   $semesters = $departmentCourses->groupBy('semester_name')->map(function ($semesterCourses) {
            //     return [
            //       'id' => $semesterCourses->first()->semester_name, // Get semester_name from first course
            //       'name' => $semesterCourses->first()->semester_name, // Same as id (assuming unique names)
            //       'department_courses' => $semesterCourses->map(function ($departmentCourse) {
            //         return [
            //           'id' => $departmentCourse->id,
            //           'name' => $departmentCourse->course->arabic_name,
            //         ];
            //       }),
            //     ];
            //   })->values(); // Remove empty keys from grouped results
            //   return $semesters;
     }


    public function retrieveDepartmentCourse(Request $request)
    {
        // department_id, course_id, level, semester
        $departmentCourse = DepartmentCourse::find($request->id); //updated successfull
        $departmentCourse['department_name'] = Department::where('id', $departmentCourse->department_id)->get(['arabic_name']);
        $departmentCourse['college_name'] = College::where('id', $departmentCourse->department_id)->get(['arabic_name']);
        $departmentCourse['course_name'] = Course::where('id', $departmentCourse->course_id)->get(['arabic_name']);
        $departmentCourse['level_name'] = LevelsEnum::getNameByNumber($departmentCourse->level);
        $departmentCourse['semester_name'] = SemesterEnum::getNameByNumber($departmentCourse->semester);
        $departmentCourse['department_course_parts'] = $this->retrieveDepartmentCourseParts($departmentCourse);
        unset($departmentCourse['department_id']);
        unset($departmentCourse['course_id']);
        unset($departmentCourse['level']);
        unset($departmentCourse['semester']);
        unset($departmentCourse['id']); //updated
        return $departmentCourse;

        //we can optimize this function to this :
        // $departmentCourse = DepartmentCourse::with([
        //     'department:id,arabic_name.college:id,arabic_name as college_name', // Eager load with nested relation
        //     'course:id,arabic_name',
        //     'level:number,name as level_name',
        //     'semester:number,name as semester_name',
        //     'department_course_parts.course_part:id,part_id.coursePartsEnum:number,name as part_name', // Eager load with nested relation for departmentCourseParts
        //   ])->find($request->id);

        //   return $departmentCourse->only([
        //     'id', // Add back if needed
        //     'department_name',
        //     'college_name',
        //     'course_name',
        //     'level_name',
        //     'semester_name',
        //     'department_course_parts',
        //   ]);
    }


    private function retrieveDepartmentCourseParts($model){
        $departmentCourseParts = $model->department_course_parts()->get(['department_course_id']);
        foreach ($departmentCourseParts as $departmentCoursePart ) {
            $coursePart = CoursePart::where('id', $departmentCoursePart->course_part_id)->get(['part_id']);
            $departmentCoursePart['name'] = CoursePartsEnum::getNameByNumber($coursePart->part_id);
            unset($departmentCoursePart['course_part_id']);
        }
        return $departmentCourseParts;
    }


    public function rules(Request $request): array
    {
        $rules = [
            'course_id' => 'required|exists:courses,id',
            'level' => new Enum(LevelsEnum::class), // Assuming LevelsEnum holds valid values
            'semester' => new Enum(SemesterEnum::class), // Assuming SemesterEnum holds valid values
            'department_id' => 'required|exists:departments,id',
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
