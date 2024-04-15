<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\DeleteHelper;
use App\Models\CourseStudent;
use App\Helpers\ResponseHelper;
use App\Helpers\ValidateHelper;
use App\Helpers\EnumReplacement;
use App\Models\DepartmentCourse;
use App\Helpers\EnumReplacement1;
use App\Helpers\ProcessDataHelper;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rules\Enum;
use App\Enums\CourseStudentStatusEnum;

class CourseStudentController extends Controller
{
    public function addCourseStudent(Request $request)
    {
        if($failed = ValidateHelper::validateData($request, $this->rules($request))){
            return  ResponseHelper::clientError($failed);
        }
        $departmenCourse = DepartmentCourse::findOrFail($request->department_course_id);
        foreach ($request->students_ids as $student_id ) {
            $departmenCourse->course_students()->create([
                'student_id' => $student_id,
                'status' => CourseStudentStatusEnum::ACTIVE->value,// make it as default in megretion
                'academic_year' => now()->format('Y'), ///need to ************
            ]);
        }
        return ResponseHelper::success();
    }

    public function modifyCourseStudent(Request $request)
    {
        if($failed = ValidateHelper::validateData($request, $this->rules($request))){
            return  ResponseHelper::clientError($failed);
        }
        $courseStudent = CourseStudent::where('department_course_id','=',$request->department_course_id)
        ->where('student_id', '=', $request->student_id)->first();
        $courseStudent->update([
            'academic_year' => $request->academic_year,
        ]);
        return ResponseHelper::success();
    }

    public function passCourseStudent(Request $request)
    {
        if($failed = ValidateHelper::validateData($request, $this->rules($request))){
            return  ResponseHelper::clientError($failed);
        }
        $courseStudent = CourseStudent::where('department_course_id','=',$request->department_course_id)
        ->where('student_id', '=', $request->student_id)->first();
        $courseStudent->update([
            'status' => CourseStudentStatusEnum::PASSED->value ,
        ]);
        return ResponseHelper::success();
    }

    public function suspendCourseStudent(Request $request)
    {
        if($failed = ValidateHelper::validateData($request, $this->rules($request))){
            return  ResponseHelper::clientError($failed);
        }
        $courseStudent = CourseStudent::where('department_course_id','=',$request->department_course_id)
        ->where('student_id', '=', $request->student_id)->first();
        if($courseStudent->status ===  CourseStudentStatusEnum::ACTIVE->value ){
            $courseStudent->update([
                'status' => CourseStudentStatusEnum::SUSPENDED->value ,
            ]);
            return ResponseHelper::success();

        }else{
            return ResponseHelper::clientError('student status not active');
        }
    }

    public function deleteCourseStudent(Request $request)
    {
        if($failed = ValidateHelper::validateData($request, $this->rules($request))){
            return  ResponseHelper::clientError($failed);
        }
        $courseStudent = CourseStudent::where('department_course_id','=',$request->department_course_id)
        ->where('student_id', '=', $request->student_id)->first();
        if($courseStudent->status ===  CourseStudentStatusEnum::ACTIVE->value ){
        return DeleteHelper::deleteModel($courseStudent);
        }else{
            return ResponseHelper::clientError('student status not active');
        }
    }

    public function retrieveCourceStudents(Request $request)
    {
        $courseStudents = DB::table('department_courses')
              ->join('course_students', 'department_courses.id', '=', 'course_students.department_course_id')
              ->join('students', 'course_students.student_id', '=', 'students.id')
              ->select('course_students.id as department_name',
              'students.id','students.academic_id','students.image_url','students.arabic_name as name')
              ->Where('department_courses.id ', '=', $request->department_course_id)
              ->Where('course_students.academic_year ', '=', $request->academic_year)
             ->when($request->status_id === null, function ($query) {
                 return  $query->selectRaw('course_students.status as status_name');
              })
              ->when($request->status_id, function ($query) use ($request) {
               return  $query ->Where('course_students.status', '=', $request->status_id);
            })
            ->get();

            if($courseStudents->has(['status_name'])){
                $courseStudents = ProcessDataHelper::enumsConvertIdToName($courseStudents, [new EnumReplacement('status_name', CourseStudentStatusEnum::class)]);
            }

           return ResponseHelper::successWithData($courseStudents);
    }



    public function retrieveUnlinkCourceStudents(Request $request)
    {
        $department = DepartmentCourse::findOrFail($request->department_course_id,['department_id']);
        $departmentStudents = [];
        $departmentCourseStudents = [];
        $departmentStudents = DB::table('departments')
        ->join('department_courses', 'departments.id', '=', 'department_courses.department_id')
        ->join('course_students', 'department_courses.id', '=', 'course_students.department_course_id')
        ->join('students', 'course_students.student_id', '=', 'students.id')
        ->select('students.id', 'students.academic_id', 'students.arabic_name as name', 'students.image_url')
        ->where('departments.id', '=', $department->department_id)
        ->distinct()
        ->get();

        $departmentCourseStudents = DB::table('department_courses')
        ->join('course_students', 'department_courses.id', '=', 'course_students.department_course_id')
        ->select('course_students.student_id')
        ->where('department_courses.id', '=', $request->department_course_id)
        ->distinct()
        ->get();
    $unlinkCourseStudents = $departmentStudents->whereNotIn('id', $departmentCourseStudents->pluck('student_id'));

    return ResponseHelper::successWithData($unlinkCourseStudents);


        // compare or تقاطع بين الناتجين
    // $department = DepartmentCourse::find($request->department_course_id)->get(['department_id']);
    // $studentQuery = DB::table('departments')
    //     ->join('department_courses', 'departments.id', '=', 'department_courses.department_id')
    //     ->join('course_students', 'department_courses.id', '=', 'course_students.department_course_id')
    //     ->join('students', 'course_students.student_id', '=', 'students.id')
    //     ->select('students.id', 'students.academic_id', 'students.arabic_name as name', 'students.image_url');

    // $allDepartmentStudents = $studentQuery->where('departments.id', '=', $department->department_id)->get();
    // $linkedCourseStudents = DB::table('department_courses')
    //     ->join('course_students', 'department_courses.id', '=', 'course_students.department_course_id')
    //     ->where('department_courses.id', '=', $request->department_course_id)
    //     ->select('course_students.student_id');

    // $unlinkCourseStudents = $allDepartmentStudents->whereNotIn('id', $linkedCourseStudents->pluck('student_id'));
    // return $unlinkCourseStudents;



    // OR
     // Union the queries:
    //  $unionQuery = $departmentStudents->union($departmentCourseStudents);
     // Get the results:
    //  $allStudents = $unionQuery->distinct()->get();

    // OR
    // $unionStudents = Collection::make($departmentStudents)->union($departmentCourseStudents)->all();
    }

    public function retrieveEditableCourseStudent(Request $request)
    {
    $attributes = ['academic_year'];
    $courseStudent = CourseStudent::where('department_course_id' , '=', $request->department_course_id)
    ->where('student_id' , '=', $request->student_id)->get($attributes);     
        return ResponseHelper::successWithData($courseStudent);
    }

    public function rules(Request $request): array
    {
            $rules = [
                //'department_course_id' => 'required|exists:department_courses,id',
                //'student_id' => 'required|exists:students,id',
                'status' => ['nullable',new Enum(CourseStudentStatusEnum::class)], // Assuming CourseStudentStatusEnum holds valid values
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
