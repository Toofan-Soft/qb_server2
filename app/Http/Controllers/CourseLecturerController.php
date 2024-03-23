<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Enums\LevelsEnum;
use App\Models\CoursePart;
use App\Models\Department;
use App\Enums\SemesterEnum;
use Illuminate\Http\Request;
use App\Helpers\DeleteHelper;
use App\Helpers\ModifyHelper;
use App\Enums\CoursePartsEnum;
use App\Models\CourseLecturer;
use Illuminate\Support\Facades\DB;
use App\Models\DepartmentCoursePart;

class CourseLecturerController extends Controller
{
    public function addCourseLecturer(Request $request)
    {

        CourseLecturer::create([
            'department_course_part_id' => $request->department_course_id,
            'lecturer_id' => $request->course_part_id,
            'academic_year' => now()->format('Y'), ///need to ************
        ]);

       // return AddHelper::addModel($request, DepartmentCourse::class,  $this->rules($request), 'department_course_parts', $request->department_course_id);
    }

    public function deleteCourseLecturer(CourseLecturer $department)
    {
       return DeleteHelper::deleteModel($department);
    }

    public function retrieveCourceLecturers(Request $request)
    {
        $courseLecturers = [];
        if(!$request->academic_year){
            // $courseLecturers = DepartmentCoursePart::with([
            //     'departmetn_course.department:arabic_name as department_name.college:arabic_name as college_name', // Eager load with required attributes
            //     'course_lecturers:id,academic_year.employee:arabic_name as lecturer_name', // Nested eager load
            //   ])->where('course_part_id', $request->course_part_id);

              $courseLecturers = DB::table('department_course_parts')
              ->join('department_courses', 'department_course_parts.department_course_id', '=', 'department_courses.id')
              ->join('departments', 'department_courses.department_id', '=', 'departments.id')
              ->join('colleges', 'departments.college_id', '=', 'colleges.id')
              ->join('course_lecturers', 'department_course_parts.id', '=', 'course_lecturers.department_course_part_id')
              ->join('employees', 'course_lecturers.lecturer_id', '=', 'employees.id')
              ->select('departments.arabic_name as department_name', 'colleges.arabic_name as college_name', 'course_lecturers.id','course_lecturers.academic_year', 'employees.arabic_name as lecturer_name')
              ->Where('department_course_parts.course_part_id', '=', $request->course_part_id)
              ->get();
        }else{
        //   $courseLecturers = DepartmentCoursePart::with([
        //         'department_course.department:arabic_name as department_name.college:arabic_name as college_name', // Eager load with required attributes
        //         'course_lecturers:id.employee:arabic_name as lecturer_name', // Nested eager load
        //       ])->where('course_part_id', $request->course_part_id)->where('academic_year', $request->academic_year);

              $courseLecturers = DB::table('department_course_parts')
              ->join('department_courses', 'department_course_parts.department_course_id', '=', 'department_courses.id')
              ->join('departments', 'department_courses.department_id', '=', 'departments.id')
              ->join('colleges', 'departments.college_id', '=', 'colleges.id')
              ->join('course_lecturers', 'department_course_parts.id', '=', 'course_lecturers.department_course_part_id')
              ->join('employees', 'course_lecturers.lecturer_id', '=', 'employees.id')
              ->select('departments.arabic_name as department_name', 'colleges.arabic_name as college_name', 'course_lecturers.id', 'employees.arabic_name as lecturer_name')
              ->Where('department_course_parts.course_part_id', '=', $request->course_part_id)
              ->Where('course_lecturers.academic_year', '=', $request->course_part_id)
              ->get();
        }
        return $courseLecturers;
    }

    public function retrieveLecturerCourses(Request $request)
    {
            // $lecturerCourses = Employee::with([
            //     'course_lecturers:id,academic_year.department_course_part.course_part:part_id as course_part_name',
            //     'course_lecturers.department_course_part.department_course:level as level_name, semester as semester_name.course:arabic_name as course_name',
            //     'course_lecturers.department_course_part.department_course.department:arabic_name as department_name.college:arabic_name as college_name',
            //   ])->find($request->employee_id);

        $lecturerCourses =  DB::table('employees')
        ->join('course_lecturers', 'employees.id', '=', 'course_lecturers.lecturer_id')
        ->join('department_course_parts', 'course_lecturers.department_course_part_id', '=', 'department_course_parts.id')
        ->join('course_parts', 'department_course_parts.course_part_id', '=', 'course_parts.id')
        ->join('department_courses', 'department_course_parts.department_course_id', '=', 'department_courses.id')
        ->join('departments', 'department_courses.department_id', '=', 'departments.id')
        ->join('colleges', 'departments.college_id', '=', 'colleges.id')
        ->select('course_lecturers.id,','course_lecturers.academic_year',
                'course_parts.part_id as course_part_name',
                'department_courses.level as level_name', 'department_courses.semester as semester_name',
                'courses.arabic_name as course_name',
                'departments.arabic_name as department_name',
                'colleges.arabic_name as college_name',
                )
        ->where('employees.id', '=', $request->employee_id)
        ->get();

              foreach ($lecturerCourses as $lecturerCourse  ) {
                $lecturerCourse['part_name'] = CoursePartsEnum::getNameByNumber($lecturerCourse['part_name']);
                $lecturerCourse['semester_name'] = SemesterEnum::getNameByNumber($lecturerCourse['semester_name']);
                $lecturerCourse['level_name'] = LevelsEnum::getNameByNumber($lecturerCourse['level_name']);
              }
        return $lecturerCourses;
    }

    public function retrieveCourceLecturer(Request $request)
    {
    ///eagear loading
    // $courseLecturer = CourseLecturer::with([
        //     'employee:arabic_name as name,phone,email,specialization,qualification as qualification_name,image_url', // Eager load with required attributes
        //     'academic_year', ///************* */
        //     'department_course_part:score,lectures_count,lecture_duration.course_part:part_id as course_part_name',
        //     'department_course_part.department_course:level as level_name, semester as semester_name.course:arabic_name as course_name',
        //     'course_lecturers.department_course_part.department_course.department:arabic_name as department_name.college:arabic_name as college_name',
        // ])->find($request->id);
  $courseLecturer =  DB::table('course_lecturers')
    ->join('employees', 'course_lecturers.lecturer_id', '=', 'employees.id')
    ->join('department_course_parts', 'course_lecturers.department_course_part_id', '=', 'department_course_parts.id')
    ->join('course_parts', 'department_course_parts.course_part_id', '=', 'course_parts.id')
    ->join('department_courses', 'department_course_parts.department_course_id', '=', 'department_course.id')
    ->join('courses', 'department_courses.course_id', '=', 'courses.id')
    ->join('departments', 'department_courses.department_id', '=', 'departments.id')
    ->join('colleges', 'departments.college_id', '=', 'colleges.id')
    ->select('table1.column1',
     'employees.arabic_name as name','employees.phone' , 'employees.email', 'employees.specialization', 'employees.qualification as qualification_name', 'employees.image_url',
     'course_lecturers.academic_year',
     'department_course_parts.score','department_course_parts.lectures_count','department_course_parts.lecture_duration',
     'course_parts.part_id as course_part_name',
     'department_courses.level as level_name', 'department_courses.semester as semester_name',
     'courses.arabic_name as course_name',
     'departments.arabic_name as department_name',
     'colleges.arabic_name as college_name',
     )
    ->where('course_lecturers.id', '=', $request->id)
    ->get();

        return $courseLecturer;
    }
    public function rules(Request $request): array
    {
        $rules = [
            'academic_year' => 'required|integer|min:2000|max:' . (date('Y') + 5), // Adjust max year as needed
            'department_course_part_id' => 'required|exists:department_course_parts,id',
            'lecturer_id' => 'required|exists:employees,id',
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
