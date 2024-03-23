<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CourseStudent;
use App\Helpers\EnumReplacement;
use App\Models\DepartmentCourse;
use App\Helpers\EnumReplacement1;
use App\Helpers\ProcessDataHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Enum;
use App\Enums\CourseStudentStatusEnum;

class CourseStudentController extends Controller
{
    public function addCourseStudent(Request $request)
    {

        $departmenCourse = DepartmentCourse::find($request->department_course_id);
        foreach ($request->students_ids as $student_id ) {
            $departmenCourse->course_students()->create([
                'student_id' => $student_id,
                'status' => CourseStudentStatusEnum::ACTIVE->value,
                'academic_year' => now()->format('Y'), ///need to ************
            ]);
        }
       // return AddHelper::addModel($request, DepartmentCourse::class,  $this->rules($request), 'department_course_parts', $request->department_course_id);
    }

    public function modifyCourseStudent(Request $request)
    {
        $courseStudent = CourseStudent::find([$request->department_course_id, $request->student_id]);
        $courseStudent->update([
            'academic_year' => $request->academic_year,
        ]);
    }

    public function passCourseStudent(Request $request)
    {
        $courseStudent = CourseStudent::find([$request->department_course_id, $request->student_id]);
        $courseStudent->update([
            'status' => CourseStudentStatusEnum::PASSED->value ,
        ]);
    }

    public function suspendCourseStudent(Request $request)
    {
        $courseStudent = CourseStudent::find([$request->department_course_id, $request->student_id]);
        if($courseStudent->status ===  CourseStudentStatusEnum::ACTIVE->value ){
            $courseStudent->update([
                'status' => CourseStudentStatusEnum::SUSPENDED->value ,
            ]);
        }else {
            return response()->json(['error_message'=> 'student status not active '] ,400);
        }
    }

    public function deleteCourseStudent(Request $request)
    {
        $courseStudent = CourseStudent::find([$request->department_course_id, $request->student_id]);//
        if($courseStudent->status ===  CourseStudentStatusEnum::ACTIVE->value ){
            $courseStudent->delete();
        }else {
            return response()->json(['error_message'=> 'student status not active '] ,400);
        }
    }

    public function retrieveCourceStudents(Request $request)
    {
        $courseStudents = [];
        if(!$request->status_id){
            $courseStudents = DepartmentCourse::with([
                'course_students:status as status_name.student:id,academic_id,image_url,arabic_name as name', // Eager load with required attributes
              ])->where('academic_year', $request->academic_year)->where('id', $request->department_course_id);
              $courseStudents = ProcessDataHelper::enumsConvertIdToName($courseStudents, [new EnumReplacement1('status_name', CourseStudentStatusEnum::class)]);
        }else{
            $courseStudents = DepartmentCourse::with([
                'course_students.student:id,academic_id,image_url,arabic_name as name', // Eager load with required attributes
              ])->where('academic_year', $request->academic_year)->where('id', $request->department_course_id)->where('status', $request->status_id);
        }
        // test if the where work
        return $courseStudents;
    }


    public function retrieveUnlinkCourceStudents(Request $request)
    {
        $department = DepartmentCourse::find($request->department_course_id)->get(['department_id']);
        $departmentStudents = [];
        $departmentCourseStudents = [];
        $departmentStudents = DB::table('departments')
        ->join('department_courses', 'departments.id', '=', 'department_courses.department_id')
        ->join('course_students', 'department_courses.id', '=', 'course_students.department_course_id')
        ->join('students', 'course_students.student_id', '=', 'students.id')
        ->select('students.id', 'students.academic_id', 'students.arabic_name as name', 'students.image_url')
        ->where('department.id', '=', $department->department_id)
        ->distinct()
        ->get();

        $departmentCourseStudents = DB::table('department_courses')
        ->join('course_students', 'department_courses.id', '=', 'course_students.department_course_id')
        ->join('students', 'course_students.student_id', '=', 'students.id')
        ->select('students.id', 'students.academic_id', 'students.arabic_name as name', 'students.image_url')
        ->where('department_courses.id', '=', $request->department_course_id)
        ->distinct()
        ->get();


        // compare or تقاطع بين الناتجين

    // $department = DepartmentCourse::find($request->department_course_id)->get(['department_id']);
    // $studentQuery = DB::table('departments')
    //     ->join('department_courses', 'departments.id', '=', 'department_courses.department_id')
    //     ->join('course_students', 'department_courses.id', '=', 'course_students.department_course_id')
    //     ->join('students', 'course_students.student_id', '=', 'students.id')
    //     ->select('students.id', 'students.academic_id', 'students.arabic_name as name', 'students.image_url');

    // $allDepartmentStudents = $studentQuery->where('department.id', '=', $department->department_id)->get();
    // $linkedCourseStudents = DB::table('department_courses')
    //     ->join('course_students', 'department_courses.id', '=', 'course_students.department_course_id')
    //     ->where('department_courses.id', '=', $request->department_course_id)
    //     ->select('course_students.student_id');

    // $unlinkCourseStudents = $allDepartmentStudents->whereNotIn('id', $linkedCourseStudents->pluck('student_id'));
    // return $unlinkCourseStudents;



    }



    public function rules(Request $request): array
    {

            $rules = [
                //'department_course_id' => 'required|exists:department_courses,id',
                'student_id' => 'required|exists:students,id',
                'status' => new Enum(CourseStudentStatusEnum::class), // Assuming CourseStudentStatusEnum holds valid values
                'academic_year' => 'required|integer',
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
