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
use App\Enums\LevelsEnum;
use App\Enums\OwnerTypeEnum;
use App\Enums\RoleEnum;
use App\Helpers\EnumReplacement;
use App\Models\DepartmentCourse;
use App\Helpers\EnumReplacement1;
use App\Helpers\ProcessDataHelper;
use App\Helpers\ResponseHelper;
use App\Models\Chapter;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\MockObject\Builder\Stub;
use Symfony\Component\Console\Helper\ProcessHelper;

class FilterController extends Controller
{
    public function retrieveCourses()
    {
        $attributes = ['id', 'arabic_name as name'];
        return GetHelper::retrieveModels(Course::class, $attributes, null);
    }

    public function retrieveCourseParts(Request $request)
    {
        $attributes = ['id', 'part_id as name'];
        $conditionAttribute = ['course_id'  => $request->course_id];
        $enumReplacements = [
            new EnumReplacement('name', CoursePartsEnum::class),
        ];
        return GetHelper::retrieveModels(CoursePart::class, $attributes,  $conditionAttribute, $enumReplacements);
    }

    public function retrieveChapters(Request $request)
    {
        $attributes = ['id', 'arabic_title as title'];
        $conditionAttribute = ['course_part_id'  => $request->course_part_id];
        return GetHelper::retrieveModels(Chapter::class, $attributes,  $conditionAttribute);
    }

    public function retrieveTopics(Request $request)
    {
        $attributes = ['id', 'arabic_title as title'];
        $conditionAttribute = ['chapter_id'  => $request->chapter_id];
        return GetHelper::retrieveModels(Topic::class, $attributes, $conditionAttribute);
    }

    public function retrieveColleges()
    {
        $attributes = ['id', 'arabic_name as name'];
        return GetHelper::retrieveModels(College::class, $attributes, null);
    }

    public function retrieveLecturerColleges()
    {
        $lecturer = Employee::findOrFail(auth()->user()->id);
        if ($lecturer) {
            $lecturerColleges =  DB::table('course_lecturers')
                ->join('department_course_parts', 'course_lecturers.department_course_part_id', '=', 'department_course_parts.id')
                ->join('department_courses', 'department_course_parts.department_course_id', '=', 'department_courses.id')
                ->join('departments', 'department_courses.department_id', '=', 'departments.id')
                ->join('colleges', 'departments.college_id', '=', 'colleges.id')
                ->select('colleges.id', 'colleges.arabic_name as name')
                ->where('course_lecturers.lecturer_id', '=', $lecturer->id)
                ->distinct()
                ->get();
            return ResponseHelper::successWithData($lecturerColleges);
        } else {
            return ResponseHelper::clientError(402);
            // return response()->json(['error_message' => 'lectuer not authorized'], 401);
        }
    }

    public function retrieveLecturerCurrentColleges()
    {
        $lecturer = Employee::findOrFail(auth()->user()->id);
        if ($lecturer) {
            $lecturerColleges =  DB::table('course_lecturers')
                ->join('department_course_parts', 'course_lecturers.department_course_part_id', '=', 'department_course_parts.id')
                ->join('department_courses', 'department_course_parts.department_course_id', '=', 'department_courses.id')
                ->join('departments', 'department_courses.department_id', '=', 'departments.id')
                ->join('colleges', 'departments.college_id', '=', 'colleges.id')
                ->select('colleges.id', 'colleges.arabic_name as name')
                ->where('course_lecturers.lecturer_id', '=', $lecturer->id)
                ->where('course_lecturers.academic_year', '=', now()->format('Y'))
                ->distinct()
                ->get();
            return ResponseHelper::successWithData($lecturerColleges);
        } else {
            return ResponseHelper::clientError(402);
            // return response()->json(['error_message' => 'lectuer not authorized'], 401);
        }
    }

    public function retrieveDepartments(Request $request)
    {
        $attributes = ['id', 'arabic_name as name'];
        $conditionAttribute = ['college_id'  => $request->college_id];
        return GetHelper::retrieveModels(Department::class, $attributes,  $conditionAttribute);
    }

    public function retrieveLecturerDepartments(Request $request)
    {
        $lecturer = Employee::findOrFail(auth()->user()->id);
        if ($lecturer) {
            $lecturerDepartments =  DB::table('course_lecturers')
                ->join('department_course_parts', 'course_lecturers.department_course_part_id', '=', 'department_course_parts.id')
                ->join('department_courses', 'department_course_parts.department_course_id', '=', 'department_courses.id')
                ->join('departments', 'department_courses.department_id', '=', 'departments.id')
                ->select('departments.id', 'departments.arabic_name as name')
                ->where('departments.college_id', '=', $request->college_id)
                ->where('course_lecturers.lecturer_id', '=', $lecturer->id)
                ->distinct()
                ->get();
            return ResponseHelper::successWithData($lecturerDepartments);
        } else {
            return ResponseHelper::clientError(402);
            // return response()->json(['error_message' => 'lectuer not authorized'], 401);
        }
    }

    public function retrieveLecturerCurrentDepartments(Request $request)
    {
        $lecturer = Employee::findOrFail(auth()->user()->id);
        if ($lecturer) {
            $lecturerDepartments =  DB::table('course_lecturers')
                ->join('department_course_parts', 'course_lecturers.department_course_part_id', '=', 'department_course_parts.id')
                ->join('department_courses', 'department_course_parts.department_course_id', '=', 'department_courses.id')
                ->join('departments', 'department_courses.department_id', '=', 'departments.id')
                ->select('departments.id', 'departments.arabic_name as name')
                ->where('departments.college_id', '=', $request->college_id)
                ->where('course_lecturers.lecturer_id', '=', $lecturer->id)
                ->where('course_lecturers.academic_year', '=', now()->format('Y'))
                ->distinct()
                ->get();
            return ResponseHelper::successWithData($lecturerDepartments);
        } else {
            return ResponseHelper::clientError(402);
            // return response()->json(['error_message' => 'lectuer not authorized'], 401);
        }
    }

    public function retrieveDepartmentLevels(Request $request)
    {
        $attributes = ['level as name'];
        $department = Department::findOrFail($request->department_id);
        $departmentLevels = $department->department_courses()->first($attributes);

        $id = $departmentLevels['name'];
        $enumReplacements = [
            new EnumReplacement('name', LevelsEnum::class),
        ];
        $departmentLevels = ProcessDataHelper::enumsConvertIdToName($departmentLevels, $enumReplacements);

        $data = [
            'id' => $id,
            'name' => $departmentLevels->name,
        ];
        return ResponseHelper::successWithData([$data]);
    }

    public function retrieveDepartmentCourses(Request $request)
    {
        if ($request->department_id) {
            $departmentCourses =  DB::table('departments')
                ->join('department_courses', 'departments.id', '=', 'department_courses.department_id')
                ->join('courses', 'department_courses.course_id', '=', 'courses.id')
                ->select('department_courses.id', 'courses.arabic_name as name')
                ->where('departments.id', '=', $request->department_id)
                ->get();

            return ResponseHelper::successWithData($departmentCourses);
        } else {
            return ResponseHelper::clientError(401);
            // return response()->json(['error_message' => 'department_id is empty'], 401);
        }
    }

    public function retrieveDepartmentLevelCourses(Request $request)
    {
        // if ($request->department_id && $request->level_id) {
        //     $departmentCourses =  DB::table('departments')
        //         ->join('department_courses', 'departments.id', '=', 'department_courses.department_id')
        //         ->join('courses', 'department_courses.course_id', '=', 'courses.id')
        //         ->select('department_courses.id', 'courses.arabic_name as name')
        //         ->where('departments.id', '=', $request->department_id)
        //         ->where('department_courses.level', '=', $request->level_id)
        //         ->get();
        //     return ResponseHelper::successWithData($departmentCourses);

        if ($request->department_id && $request->level_id) {
            // Fetch department with related department courses and courses
            $departmentCourses = Department::with(['department_courses.course'])
                ->where('id', $request->department_id)
                ->whereHas('department_courses', function($query) use ($request) {
                    $query->where('level', $request->level_id);
                })
                ->first();

            // Extract the required information
            $data = $departmentCourses->department_courses->map(function($departmentCourse) {
                return [
                    'id' => $departmentCourse->id,
                    'name' => $departmentCourse->course->arabic_name,
                ];
            });

            return ResponseHelper::successWithData($data);

        } else {
            return ResponseHelper::clientError(401);
            // return response()->json(['error_message' => 'department_id or level_id is empty'], 401);
        }
    }

    public function retrieveDepartmentLevelSemesterCourses(Request $request)
    {
        if ($request->department_id && $request->level_id && $request->semester_id) {
            $departmentCourses =  DB::table('departments')
                ->join('department_courses', 'departments.id', '=', 'department_courses.department_id')
                ->join('courses', 'department_courses.course_id', '=', 'courses.id')
                ->select('department_courses.id', 'courses.arabic_name as name')
                ->where('departments.id', '=', $request->department_id)
                ->where('department_courses.level', '=', $request->level_id)
                ->where('department_courses.semester', '=', $request->semester_id)
                ->get();
            return ResponseHelper::successWithData($departmentCourses);
        } else {
            return ResponseHelper::clientError(401);
            // return response()->json(['error_message' => 'department_id or level_id or semester_id is empty'], 401);
        }
    }

    public function retrieveDepartmentCourseParts(Request $request)
    {
        if ($request->department_course_id) {
            $departmentCourseParts =  DB::table('department_courses')
                ->join('department_course_parts', 'department_courses.id', '=', 'department_course_parts.department_course_id')
                ->join('course_parts', 'department_course_parts.course_part_id', '=', 'course_parts.id')
                ->select(
                    'department_course_parts.id',
                    'course_parts.part_id as name'
                )
                ->where('department_courses.id', '=', $request->department_course_id)
                ->get();

            $departmentCourseParts = ProcessDataHelper::enumsConvertIdToName($departmentCourseParts, [
                new EnumReplacement('name', CoursePartsEnum::class),
            ]);
            return ResponseHelper::successWithData($departmentCourseParts);
        } else {
            return ResponseHelper::clientError(401);
            // return response()->json(['error_message' => 'department_course_id is empty'], 401);
        }
    }

    public function retrieveDepartmentLecturerCourses(Request $request)
    {
        $lecturer = Employee::findOrFail(auth()->user()->id);
        if ($lecturer) {
            $departmentLecturerCourses =  DB::table('course_lecturers')
                ->join('department_course_parts', 'course_lecturers.department_course_part_id', '=', 'department_course_parts.id')
                ->join('department_courses', 'department_course_parts.department_course_id', '=', 'department_courses.id')
                ->join('departments', 'department_courses.department_id', '=', 'departments.id')
                ->join('courses', 'department_courses.course_id', '=', 'courses.id')
                ->select(
                    'department_courses.id',
                    'courses.arabic_name as name'
                )
                ->where('departments.id', '=', $request->department_id)
                ->where('course_lecturers.lecturer_id', '=', $lecturer->id)
                ->get();
            return ResponseHelper::successWithData($departmentLecturerCourses);
        } else {
            return ResponseHelper::clientError(402);
            // return response()->json(['error_message' => 'lectuer not authorized or department_id is empty'], 401);
        }
    }
    public function retrieveDepartmentLecturerCurrentCourses(Request $request)
    {
        $lecturer = Employee::findOrFail(auth()->user()->id);
        if ($lecturer) {
            $departmentLecturerCourses =  DB::table('course_lecturers')
                ->join('department_course_parts', 'course_lecturers.department_course_part_id', '=', 'department_course_parts.id')
                ->join('department_courses', 'department_course_parts.department_course_id', '=', 'department_courses.id')
                ->join('departments', 'department_courses.department_id', '=', 'departments.id')
                ->join('courses', 'department_courses.course_id', '=', 'courses.id')
                ->select(
                    'department_courses.id',
                    'courses.arabic_name as name'
                )
                ->where('departments.id', '=', $request->department_id)
                ->where('course_lecturers.lecturer_id', '=', $lecturer->id)
                ->where('course_lecturers.academic_year', '=', now()->format('Y'))
                ->get();
            return ResponseHelper::successWithData($departmentLecturerCourses);
        } else {
            return ResponseHelper::clientError(402);
            // return response()->json(['error_message' => 'lectuer not authorized or department_id is empty'], 401);
        }
    }

    public function retrieveDepartmentLecturerCourseParts(Request $request)
    {
        $lecturer = Employee::findOrFail(auth()->user()->id);
        if ($lecturer) {
            $departmentLecturerCourseParts =  DB::table('course_lecturers')
                ->join('department_course_parts', 'course_lecturers.department_course_part_id', '=', 'department_course_parts.id')
                ->join('course_parts', 'department_course_parts.course_part_id', '=', 'course_parts.id')
                ->join('department_courses', 'department_course_parts.department_course_id', '=', 'department_courses.id')
                ->select(
                    'department_course_parts.id',
                    'course_parts.part_id as name'
                )
                ->where('department_courses.id', '=', $request->department_course_id)
                ->where('course_lecturers.lecturer_id', '=', $lecturer->id)
                ->get();

            $departmentLecturerCourseParts = ProcessDataHelper::enumsConvertIdToName(
                $departmentLecturerCourseParts,
                new EnumReplacement('name', CoursePartsEnum::class)
            );

            return ResponseHelper::successWithData($departmentLecturerCourseParts);
        } else {
            return ResponseHelper::clientError(402);
            // return response()->json(['error_message' => 'lectuer not authorized or department_id is empty'], 401);
        }
    }

    public function retrieveDepartmentLecturerCurrentCourseParts(Request $request)
    {
        $lecturer = Employee::findOrFail(auth()->user()->id);
        if ($lecturer) {
            $departmentLecturerCourseParts =  DB::table('course_lecturers')
                ->join('department_course_parts', 'course_lecturers.department_course_part_id', '=', 'department_course_parts.id')
                ->join('course_parts', 'department_course_parts.course_part_id', '=', 'course_parts.id')
                ->join('department_courses', 'department_course_parts.department_course_id', '=', 'department_courses.id')
                ->select(
                    'department_course_parts.id',
                    'course_parts.part_id as name'
                )
                ->where('department_courses.id', '=', $request->department_course_id)
                ->where('course_lecturers.lecturer_id', '=', $lecturer->id)
                ->where('course_lecturers.academic_year', '=', now()->format('Y')) // سؤال العيال
                ->get();

            $departmentLecturerCourseParts = ProcessDataHelper::enumsConvertIdToName(
                $departmentLecturerCourseParts,
                new EnumReplacement('name', CoursePartsEnum::class)
            );

            return ResponseHelper::successWithData($departmentLecturerCourseParts);
        } else {
            return ResponseHelper::clientError(402);
            // return response()->json(['error_message' => 'lectuer not authorized or department_id is empty'], 401);
        }
    }


    public function retrieveEmployees()
    {
        // هل يتم ارجاع كل الموظفين (موظف ومحاضر) او موظف فقط؟؟؟؟؟؟؟؟؟؟؟
        // يجب ان اسال العيال على هذه ايش المقصود فيها
        $attributes = ['id', 'arabic_name as name'];
        return GetHelper::retrieveModels(Employee::class, $attributes, null);
    }

    public function retrieveLecturers()
    {
        $attributes = ['id', 'arabic_name as name'];
        $lecturers = Employee::whereIn(
            'job_type',
            [JobTypeEnum::LECTURER->value, JobTypeEnum::EMPLOYEE_LECTURE->value]
        )
            ->get($attributes);
        return ResponseHelper::successWithData($lecturers);
    }

    public function retrieveEmployeesOfJob(Request $request)
    {
        $attributes = ['id', 'arabic_name as name'];
        $conditionAttribute = ['job_type' => $request->job_type_id];
        return GetHelper::retrieveModels(Employee::class, $attributes, $conditionAttribute);
    }

    public function retrieveAcademicYears()
    {
        // need to overview
        // $attributes = ['id', 'arabic_name as name'];
        // $conditionAttribute = [ 'job_type' => $request->job_type_id];
        // return GetHelper::retrieveModels(Employee::class, $attributes, $conditionAttribute);
    }

    // public function retrieveDepartmentStudents(Request $request)
    // {
    //     if ($request->department_id) {
    //         $department = Department::findOrFail($request->department_id);
    //         //*test this , or use join instead */
    //         $DepartmentStudents = $department->department_courses()->student_courses()->student()->get(['id', 'name']);
    //         return response()->json(['data' => $DepartmentStudents], 200);
    //     } else {
    //         return response()->json(['error_message' => ' department_id is empty'], 401);
    //     }
    // }

    // public function retrieveCourseStudents(Request $request)
    // {
    //     if ($request->department_course_id) {
    //         $departmentCourse = DepartmentCourse::findOrFail($request->department_course_id);
    //         //*test this , or use join instead */
    //         $departmentCourse = $departmentCourse->student_courses()->student()->get(['id', 'name']);
    //         return response()->json(['data' => $departmentCourse], 200);
    //     } else {
    //         return response()->json(['error_message' => ' department_course_id is empty'], 401);
    //     }
    // }


    public function retrieveOwners(Request $request)
    {
        $attributes = ['id', 'arabic_name as name'];
        $owners = [];
        if ($request->owner_type_id === OwnerTypeEnum::STUDENT->value) {
            $owners = Student::where('user_id', '=', null)->get($attributes);
        } elseif ($request->owner_type_id === OwnerTypeEnum::LECTURER->value) {
            $owners = Employee::whereIn(
                'job_type',
                [JobTypeEnum::LECTURER->value, JobTypeEnum::EMPLOYEE_LECTURE->value]
            )
                ->where('user_id', '=', null)
                ->get($attributes);
        } elseif ($request->owner_type_id === OwnerTypeEnum::EMPLOYEE->value) {
            $owners = Employee::whereIn(
                'job_type',
                [JobTypeEnum::EMPLOYEE->value, JobTypeEnum::EMPLOYEE_LECTURE->value]
            )
                ->where('user_id', '=', null)
                ->get($attributes);
        } else {
            return ResponseHelper::clientError(401);
        }
        return ResponseHelper::successWithData($owners);
    }

    public function retrieveRoles(Request $request)
    {
        ///////////////
    }

    public function retrieveProctors()
    {
        $proctors =  DB::table('employees')
            ->join('users', 'employees.user_id', '=', 'users.id')
            ->join('user_roles', 'users.id', '=', 'user_roles.user_id')
            ->select('employees.id', 'employees.arabic_name as name')
            ->where('user_roles.role_id', '=', RoleEnum::PROCTOR->value)
            ->get();
        return ResponseHelper::successWithData($proctors);
    }
}
