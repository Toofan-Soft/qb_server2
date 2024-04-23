<?php

namespace App\Http\Controllers;

use App\Models\User;
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
use App\Helpers\ResponseHelper;
use App\Helpers\ValidateHelper;
use App\Enums\QualificationEnum;
use App\Helpers\EnumReplacement;
use App\Helpers\ColumnReplacement;
use App\Helpers\ProcessDataHelper;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\DepartmentCoursePart;

class CourseLecturerController extends Controller
{
    public function addCourseLecturer(Request $request)
    {
        if($failed = ValidateHelper::validateData($request, $this->rules($request))){
            return  ResponseHelper::clientError($failed);
        }
        CourseLecturer::create([
            'department_course_part_id' => $request->department_course_part_id,
            'lecturer_id' => $request->lecturer_id,
            'academic_year' => now()->format('Y'), ///need to ************
        ]);

        return ResponseHelper::success();
    }

    public function deleteCourseLecturer(Request $request)
    {
        $courseLecturer = CourseLecturer::findeOrFail( $request->id);
        return DeleteHelper::deleteModel($courseLecturer);
    }

    public function retrieveCourceLecturers(Request $request)
    {
              $courseLecturers = DB::table('department_course_parts')
              ->join('department_courses', 'department_course_parts.department_course_id', '=', 'department_courses.id')
              ->join('departments', 'department_courses.department_id', '=', 'departments.id')
              ->join('colleges', 'departments.college_id', '=', 'colleges.id')
              ->join('course_lecturers', 'department_course_parts.id', '=', 'course_lecturers.department_course_part_id')
              ->join('employees', 'course_lecturers.lecturer_id', '=', 'employees.id')
              ->select('departments.arabic_name as department_name',
              'colleges.arabic_name as college_name',
              'course_lecturers.id as course_lecturer_id', 'employees.arabic_name as lecturer_name')
              ->Where('department_course_parts.course_part_id', '=', $request->course_part_id)
             ->when($request->academic_year === null, function ($query) {
                 return  $query->selectRaw('course_lecturers.academic_year');
              })
              ->when($request->academic_year, function ($query) use ($request) {
               return  $query ->Where('course_lecturers.academic_year', '=', $request->academic_year);
            })
            ->get();
        return ResponseHelper::successWithData($courseLecturers);
    }

    public function retrieveLecturerCourses(Request $request)
    {

        $lecturerCourses =  DB::table('employees')
        ->join('course_lecturers', 'employees.id', '=', 'course_lecturers.lecturer_id')
        ->join('department_course_parts', 'course_lecturers.department_course_part_id', '=', 'department_course_parts.id')
        ->join('course_parts', 'department_course_parts.course_part_id', '=', 'course_parts.id')
        ->join('department_courses', 'department_course_parts.department_course_id', '=', 'department_courses.id')
        ->join('courses', 'department_courses.course_id', '=', 'courses.id')  // edited : add this line was missing
        ->join('departments', 'department_courses.department_id', '=', 'departments.id')
        ->join('colleges', 'departments.college_id', '=', 'colleges.id')
        ->select('course_lecturers.id as course_lecturer_id,','course_lecturers.academic_year',
                'course_parts.part_id as course_part_name',
                'department_courses.level as level_name', 'department_courses.semester as semester_name',
                'courses.arabic_name as course_name',
                'departments.arabic_name as department_name',
                'colleges.arabic_name as college_name',
                )
        ->where('employees.id', '=', $request->employee_id)
        // ->where('employees.id', '=', now()->format('Y')) // سؤال محمود والعيال عنها
        ->get();
//course_part_name, level_name, semester name
$enumReplacements = [
    new EnumReplacement('course_part_name', CoursePartsEnum::class),
    new EnumReplacement('semester_name', SemesterEnum::class),
    new EnumReplacement('level_name', LevelsEnum::class),
  ];
        $lecturerCourses = ProcessDataHelper::enumsConvertIdToName($lecturerCourses, $enumReplacements);
        return ResponseHelper::successWithData($lecturerCourses);
    }

    public function retrieveCourceLecturer(Request $request)
    {
  $courseLecturer =  DB::table('course_lecturers')
    ->join('employees', 'course_lecturers.lecturer_id', '=', 'employees.id')
    ->join('department_course_parts', 'course_lecturers.department_course_part_id', '=', 'department_course_parts.id')
    ->join('course_parts', 'department_course_parts.course_part_id', '=', 'course_parts.id')
    ->join('department_courses', 'department_course_parts.department_course_id', '=', 'department_courses.id')
    ->join('courses', 'department_courses.course_id', '=', 'courses.id')
    ->join('departments', 'department_courses.department_id', '=', 'departments.id')
    ->join('colleges', 'departments.college_id', '=', 'colleges.id')
    ->select(
     'employees.arabic_name as name','employees.phone' , 'employees.user_id as email', 'employees.specialization', 'employees.qualification as qualification_name', 'employees.image_url',
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

    $enumReplacements = [
        new EnumReplacement( 'course_part_name', CoursePartsEnum::class),
        new EnumReplacement( 'qualification_name', QualificationEnum::class),
        new EnumReplacement( 'level_name', LevelsEnum::class),
        new EnumReplacement( 'semester_name', SemesterEnum::class),
      ];
      $columnReplacements = [
        new ColumnReplacement('email', 'email', User::class)
      ];
      $courseLecturer = ProcessDataHelper::enumsConvertIdToName($courseLecturer, $enumReplacements);
      $courseLecturer = ProcessDataHelper::columnConvertIdToName($courseLecturer, $columnReplacements);

      return ResponseHelper::successWithData($courseLecturer);

    }
    public function rules(Request $request): array
    {
        $rules = [
            // 'academic_year' => 'required|integer|min:2000|max:' . (date('Y') + 5), // Adjust max year as needed
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
