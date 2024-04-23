<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Topic;
use App\Models\Course;
use App\Models\College;
use App\Models\Employee;
use App\Enums\JobTypeEnum;
use App\Helpers\GetHelper;
use App\Models\CoursePart;
use App\Models\Department;
use Illuminate\Http\Request;
use App\Helpers\FilterHelper;
use App\Enums\CoursePartsEnum;
use App\Models\DepartmentCourse;
use App\Helpers\EnumReplacement1;
use App\Helpers\ProcessDataHelper;
use Illuminate\Support\Facades\DB;

class FilterController extends Controller
{
    public function retrieveCourses(Request $request)
    {
        $attributes = ['id', 'arabic_name as name'];
        $conditionAttribute = [];
        return GetHelper::retrieveModels(Course::class, $attributes, null);
    }

    public function retrieveCourseParts(Request $request)
    {
        $attributes = ['id', 'part_id as name'];
        $conditionAttribute = [ 'course_id'  => $request->course_id];
        $enumReplacements = [
            new EnumReplacement1('name', CoursePartsEnum::class),
          ];
        return GetHelper::retrieveModels(CoursePart::class, $attributes,  $conditionAttribute, $enumReplacements);
    }

    public function retrieveChapters (Request $request)
    {
        $attributes = ['id', 'title'];
        $coursePart = CoursePart::where('id', $request->course_id)->where('part_id', $request->part_id)->get();
        $chapters = $coursePart->chapters()->get($attributes);
        return response()->json(['data' => $chapters], 200);
    }

    public function retrieveTopics(Request $request)
    {
        $attributes = ['id', 'title'];
        $conditionAttribute = [ 'chapter_id'  => $request->chapter_id];
        return GetHelper::retrieveModels(Topic::class, $attributes, $conditionAttribute);
    }
    public function retrieveColleges(Request $request)
    {
        $attributes = ['id', 'arabic_name as name'];
        return GetHelper::retrieveModels(College::class, $attributes, null);
    }

    public function retrieveLecturerColleges ()
    {
        $lecturer = Employee::findorFail(auth()->user()->id);
        if($lecturer){
            $lecturerColleges =  DB::table('course_lecturers')
            ->join('department_course_parts', 'course_lecturers.department_course_part_id', '=', 'department_course_parts.id')
            ->join('department_courses', 'department_course_parts.department_course_id', '=', 'department_courses.id')
            ->join('departments', 'department_courses.department_id', '=', 'departments.id')
            ->join('colleges', 'departments.college_id', '=', 'colleges.id')
            ->select('colleges.id', 'colleges.arabic_name as name')
            ->where('course_lecturers.lecturer_id', '=', $lecturer->id)
            ->distinct()
            ->get();
            return response()->json(['data' => $lecturerColleges], 200);
        }else{
            return response()->json(['error_message' => 'lectuer not authorized'], 401);
        }
    }

    public function retrieveDepartmentCourses(Request $request)
    {
        if($request->department_id){
            $departmentCourses =  DB::table('departments')
            ->join('department_courses', 'departments.id', '=', 'department_courses.department_id')
            ->join('courses', 'department_courses.course_id', '=', 'courses.id')
            ->select('department_courses.id', 'courses.arabic_name as name')
            ->where('departments.id', '=', $request->department_id)
            ->get();
            return response()->json(['data' => $departmentCourses], 200);
        }else{
            return response()->json(['error_message' => 'department_id is empty'], 401);
        }
    }

    public function retrieveDepartmentLevelCourses(Request $request)
    {
        if($request->department_id && $request->level_id){
            $departmentCourses =  DB::table('departments')
            ->join('department_courses', 'departments.id', '=', 'department_courses.department_id')
            ->join('courses', 'department_courses.course_id', '=', 'courses.id')
            ->select('department_courses.id', 'courses.arabic_name as name')
            ->where('departments.id', '=', $request->department_id)
            ->where('department_courses.level', '=', $request->level_id)
            ->get();
            return response()->json(['data' => $departmentCourses], 200);
        }else{
            return response()->json(['error_message' => 'department_id or level_id is empty'], 401);
        }
    }
    public function retrieveDepartmentLevelSemesterCourses(Request $request)
    {
        if($request->department_id && $request->level_id && $request->semester_id ){
            $departmentCourses =  DB::table('departments')
            ->join('department_courses', 'departments.id', '=', 'department_courses.department_id')
            ->join('courses', 'department_courses.course_id', '=', 'courses.id')
            ->select('department_courses.id', 'courses.arabic_name as name')
            ->where('departments.id', '=', $request->department_id)
            ->where('department_courses.level', '=', $request->level_id)
            ->where('department_courses.semester', '=', $request->semester_id)
            ->get();
            return response()->json(['data' => $departmentCourses], 200);
        }else{
            return response()->json(['error_message' => 'department_id or level_id or semester_id is empty'], 401);
        }
    }

    public function retrieveDepartmentCourseParts(Request $request)
    {
        if($request->department_course_id){
            $DepartmentCourseParts =  DB::table('department_courses')
            ->join('department_course_parts', 'department_courses.id', '=', 'department_course_parts.department_course_id')
            ->join('course_parts', 'department_course_parts.course_part_id', '=', 'course_parts.id')
            ->select('department_course_parts.id', 'course_parts.part_id as name')
            ->where('department_courses.id', '=', $request->department_course_id)
            ->get();
            $DepartmentCourseParts = ProcessDataHelper::enumsConvertIdToName($DepartmentCourseParts, [
                new EnumReplacement1('name', CoursePartsEnum::class),
             ]);
            return response()->json(['data' => $DepartmentCourseParts], 200);
        }else{
            return response()->json(['error_message' => 'department_course_id is empty'], 401);
        }
    }
    public function retrieveLecturerCourses()
    {
        $lecturer = Employee::findorFail(auth()->user()->id);
        if($lecturer){
            $lecturerCourses =  DB::table('course_lecturers')
            ->join('department_course_parts', 'course_lecturers.department_course_part_id', '=', 'department_course_parts.id')
            ->join('course_parts', 'department_course_parts.course_part_id', '=', 'course_parts.id')
            ->join('courses', 'course_parts.course_id', '=', 'courses.id')
            ->select('courses.id', 'courses.arabic_name as course_name', 'course_parts.part_id as part_name')
            ->where('course_lecturers.lecturer_id', '=', $lecturer->id)
            ->get();
            $lecturerCourses = ProcessDataHelper::enumsConvertIdToName($lecturerCourses, [
                new EnumReplacement1('part_name', CoursePartsEnum::class),
             ]);
            return response()->json(['data' => $lecturerCourses], 200);
        }else{
            return response()->json(['error_message' => 'lectuer not authorized'], 401);
        }
    }

    public function retrieveDepartmentLecturerCourses (Request $request)
    {
        $lecturer = Employee::findorFail(auth()->user()->id);
        if($lecturer){
            $DepartmentLecturerCourses =  DB::table('course_lecturers')
            ->join('department_course_parts', 'course_lecturers.department_course_part_id', '=', 'department_course_parts.id')
            ->join('department_courses', 'department_course_parts.department_course_id', '=', 'department_courses.id')
            ->join('departments', 'department_courses.department_id', '=', 'departments.id')
            ->join('courses', 'department_courses.course_id', '=', 'courses.id')
            ->join('course_parts', 'courses.id', '=', 'course_parts.course_id')
            ->select('courses.id', 'courses.arabic_name as course_name','course_parts.part_id as part_name' )
            ->where('course_lecturers.lecturer_id', '=', $lecturer->id)
            ->where('departments.id', '=', $request->department_id)
            ->get();
            $DepartmentLecturerCourses = ProcessDataHelper::enumsConvertIdToName($DepartmentLecturerCourses, [
                new EnumReplacement1('part_name', CoursePartsEnum::class),
             ]);
            return response()->json(['data' => $DepartmentLecturerCourses], 200);
        }else{
            return response()->json(['error_message' => 'lectuer not authorized or department_id is empty'], 401);
        }
    }


    public function retrieveEmployees ()
    {
        $attributes = ['id', 'arabic_name as name'];
        return GetHelper::retrieveModels(Employee::class, $attributes, null);
    }

    public function retrieveLecturers  ()
    {
        $attributes = ['id', 'arabic_name as name'];
        $conditionAttribute = [ 'job_type' => JobTypeEnum::LECTURER->value];
        return GetHelper::retrieveModels(Employee::class, $attributes, $conditionAttribute);
    }

    public function retrieveEmployeesOfJob  (Request $request)
    {
        $attributes = ['id', 'arabic_name as name'];
        $conditionAttribute = [ 'job_type' => $request->job_type_id];
        return GetHelper::retrieveModels(Employee::class, $attributes, $conditionAttribute);
    }

    public function retrieveAcademicYears  ()
    {
        // need to overview
        // $attributes = ['id', 'arabic_name as name'];
        // $conditionAttribute = [ 'job_type' => $request->job_type_id];
        // return GetHelper::retrieveModels(Employee::class, $attributes, $conditionAttribute);
    }

    public function retrieveDepartmentStudents (Request $request)
    {
         if($request->department_id){
           $department = Department::findOrFail($request->department_id);
           //*test this , or use join instead */
           $DepartmentStudents = $department->department_courses()->student_courses()->student()->get(['id', 'name']);
            return response()->json(['data' => $DepartmentStudents], 200);
        }else{
            return response()->json(['error_message' => ' department_id is empty'], 401);
        }
    }

    public function retrieveCourseStudents (Request $request)
    {
         if($request->department_course_id){
           $departmentCourse = DepartmentCourse::findOrFail($request->department_course_id);
           //*test this , or use join instead */
           $departmentCourse = $departmentCourse->student_courses()->student()->get(['id', 'name']);
            return response()->json(['data' => $departmentCourse], 200);
        }else{
            return response()->json(['error_message' => ' department_course_id is empty'], 401);
        }
    }


    public function retrieveOwners (Request $request)
    {
        // need to overview
        // $attributes = ['id', 'arabic_name as name'];
        // $conditionAttribute = [ 'owner_type' => $request->owner_type_id];
        // return GetHelper::retrieveModels(User::class, $attributes, $conditionAttribute);
    }

    public function retrieveProctors ()
    {
        // need to overview
        // is current proctors
    }



}
