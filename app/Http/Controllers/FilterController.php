<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Topic;
use App\Models\Course;
use App\Enums\RoleEnum;
use App\Models\Chapter;
use App\Models\College;
use App\Models\Student;
use App\Models\Employee;
use App\Enums\LevelsEnum;
use App\Enums\JobTypeEnum;
use App\Helpers\GetHelper;
use App\Models\CoursePart;
use App\Models\Department;
use App\Enums\OwnerTypeEnum;
use Illuminate\Http\Request;
use App\Enums\CoursePartsEnum;
use App\Enums\SemesterEnum;
use App\Helpers\LanguageHelper;
use App\Helpers\ResponseHelper;
use App\Helpers\ValidateHelper;
use App\Helpers\EnumReplacement;
use App\Helpers\ProcessDataHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\Enum;

class FilterController extends Controller
{
    public function retrieveCourses()
    {
        Gate::authorize('retrieveCourses', FilterController::class);

        $attributes = ['id', LanguageHelper::getNameColumnName(null, 'name')];
        try {
            $courses = GetHelper::retrieveModels(Course::class, $attributes, null);

            return ResponseHelper::successWithData($courses);
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function retrieveCourseParts(Request $request)
    {
        Gate::authorize('retrieveCourseParts', FilterController::class);
        if (ValidateHelper::validateData($request, [
            'course_id' => 'required|integer'
        ])) {
            return  ResponseHelper::clientError();
        }
        $attributes = ['id', 'part_id as name'];
        $conditionAttribute = ['course_id'  => $request->course_id];
        $enumReplacements = [
            new EnumReplacement('name', CoursePartsEnum::class),
        ];
        try {

            $courseParts = GetHelper::retrieveModels(CoursePart::class, $attributes,  $conditionAttribute, $enumReplacements);
            return ResponseHelper::successWithData($courseParts);
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function retrieveChapters(Request $request)
    {
        Gate::authorize('retrieveChapters', FilterController::class);
        if (ValidateHelper::validateData($request, [
            'course_part_id' => 'required|integer'
        ])) {
            return  ResponseHelper::clientError();
        }
        $attributes = ['id', LanguageHelper::getTitleColumnName(null, 'title')];
        $conditionAttribute = ['course_part_id'  => $request->course_part_id];
        try {
            $chapters = GetHelper::retrieveModels(Chapter::class, $attributes,  $conditionAttribute);

            return ResponseHelper::successWithData($chapters);
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function retrieveTopics(Request $request)
    {
        Gate::authorize('retrieveTopics', FilterController::class);
        if (ValidateHelper::validateData($request, [
            'chapter_id' => 'required|integer'
        ])) {
            return  ResponseHelper::clientError();
        }
        $attributes = ['id', LanguageHelper::getTitleColumnName(null, 'title')];
        $conditionAttribute = ['chapter_id'  => $request->chapter_id];
        try {

            $topics = GetHelper::retrieveModels(Topic::class, $attributes, $conditionAttribute);

            return ResponseHelper::successWithData($topics);
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function retrieveColleges()
    {
        Gate::authorize('retrieveColleges', FilterController::class);

        $attributes = ['id', LanguageHelper::getNameColumnName(null, 'name')];
        try {
            $colleges = GetHelper::retrieveModels(College::class, $attributes, null);

            return ResponseHelper::successWithData($colleges);
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function retrieveLecturerColleges()
    {
        Gate::authorize('retrieveLecturerColleges', FilterController::class);

        try {
            $user = auth()->user();
            $lecturer = Employee::where('user_id', $user->id)->first();

            if ($lecturer) {
                $lecturerColleges =  DB::table('course_lecturers')
                    ->join('department_course_parts', 'course_lecturers.department_course_part_id', '=', 'department_course_parts.id')
                    ->join('department_courses', 'department_course_parts.department_course_id', '=', 'department_courses.id')
                    ->join('departments', 'department_courses.department_id', '=', 'departments.id')
                    ->join('colleges', 'departments.college_id', '=', 'colleges.id')
                    ->select('colleges.id', LanguageHelper::getNameColumnName('colleges', 'name'))
                    ->where('course_lecturers.lecturer_id', '=', $lecturer->id)
                    ->distinct()
                    ->get();

                return ResponseHelper::successWithData($lecturerColleges);
            } else {
                return ResponseHelper::clientError();
                // return response()->json(['error_message' => 'lectuer not authorized'], 401);
            }
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function retrieveLecturerCurrentColleges()
    {
        Gate::authorize('retrieveLecturerCurrentColleges', FilterController::class);

        try {
            $user = auth()->user();
            $lecturer = Employee::where('user_id', $user->id)->first();

            if ($lecturer) {
                $lecturerColleges =  DB::table('course_lecturers')
                    ->join('department_course_parts', 'course_lecturers.department_course_part_id', '=', 'department_course_parts.id')
                    ->join('department_courses', 'department_course_parts.department_course_id', '=', 'department_courses.id')
                    ->join('departments', 'department_courses.department_id', '=', 'departments.id')
                    ->join('colleges', 'departments.college_id', '=', 'colleges.id')
                    ->select('colleges.id', LanguageHelper::getNameColumnName('colleges', 'name'))
                    ->where('course_lecturers.lecturer_id', '=', $lecturer->id)
                    ->where('course_lecturers.academic_year', '=', now()->format('Y'))
                    ->distinct()
                    ->get();

                return ResponseHelper::successWithData($lecturerColleges);
            } else {
                return ResponseHelper::clientError();
                // return response()->json(['error_message' => 'lectuer not authorized'], 401);
            }
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function retrieveDepartments(Request $request)
    {
        Gate::authorize('retrieveDepartments', FilterController::class);
        if (ValidateHelper::validateData($request, [
            'college_id' => 'required|integer'
        ])) {
            return  ResponseHelper::clientError();
        }
        try {
            $attributes = ['id', LanguageHelper::getNameColumnName(null, 'name')];
            $conditionAttribute = ['college_id'  => $request->college_id];

            $departments = GetHelper::retrieveModels(Department::class, $attributes,  $conditionAttribute);

            return ResponseHelper::successWithData($departments);
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function retrieveLecturerDepartments(Request $request)
    {
        Gate::authorize('retrieveLecturerDepartments', FilterController::class);
        if (ValidateHelper::validateData($request, [
            'college_id' => 'required|integer'
        ])) {
            return  ResponseHelper::clientError();
        }
        try {
            $user = auth()->user();
            $lecturer = Employee::where('user_id', $user->id)->first();

            if ($lecturer) {
                $lecturerDepartments =  DB::table('course_lecturers')
                    ->join('department_course_parts', 'course_lecturers.department_course_part_id', '=', 'department_course_parts.id')
                    ->join('department_courses', 'department_course_parts.department_course_id', '=', 'department_courses.id')
                    ->join('departments', 'department_courses.department_id', '=', 'departments.id')
                    ->select('departments.id', LanguageHelper::getNameColumnName('departments', 'name'))
                    ->where('departments.college_id', '=', $request->college_id)
                    ->where('course_lecturers.lecturer_id', '=', $lecturer->id)
                    ->distinct()
                    ->get();

                return ResponseHelper::successWithData($lecturerDepartments);
            } else {
                return ResponseHelper::clientError();
                // return response()->json(['error_message' => 'lectuer not authorized'], 401);
            }
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function retrieveLecturerCurrentDepartments(Request $request)
    {
        Gate::authorize('retrieveLecturerCurrentDepartments', FilterController::class);
        if (ValidateHelper::validateData($request, [
            'college_id' => 'required|integer'
        ])) {
            return  ResponseHelper::clientError();
        }
        try {
            $user = auth()->user();
            $lecturer = Employee::where('user_id', $user->id)->first();

            if ($lecturer) {
                $lecturerDepartments =  DB::table('course_lecturers')
                    ->join('department_course_parts', 'course_lecturers.department_course_part_id', '=', 'department_course_parts.id')
                    ->join('department_courses', 'department_course_parts.department_course_id', '=', 'department_courses.id')
                    ->join('departments', 'department_courses.department_id', '=', 'departments.id')
                    ->select('departments.id', LanguageHelper::getNameColumnName('departments', 'name'))
                    ->where('departments.college_id', '=', $request->college_id)
                    ->where('course_lecturers.lecturer_id', '=', $lecturer->id)
                    ->where('course_lecturers.academic_year', '=', now()->format('Y'))
                    ->distinct()
                    ->get();

                return ResponseHelper::successWithData($lecturerDepartments);
            } else {
                return ResponseHelper::clientError();
                // return response()->json(['error_message' => 'lectuer not authorized'], 401);
            }
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function retrieveDepartmentLevels(Request $request)
    {
        Gate::authorize('retrieveDepartmentLevels', FilterController::class);
        if (ValidateHelper::validateData($request, [
            'department_id' => 'required|integer'
        ])) {
            return  ResponseHelper::clientError();
        }
        try {
            $attributes = ['levels_count'];

            $levelsCount = Department::findOrFail($request->department_id, $attributes)['levels_count'];

            $levels = LevelsEnum::getLevelsTo($levelsCount);

            return ResponseHelper::successWithData($levels);
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function retrieveDepartmentCourses(Request $request)
    {
        Gate::authorize('retrieveDepartmentCourses', FilterController::class);
        if (ValidateHelper::validateData($request, [
            'department_id' => 'required|integer'
        ])) {
            return  ResponseHelper::clientError();
        }
        try {
            if ($request->department_id) {
                $departmentCourses =  DB::table('departments')
                    ->join('department_courses', 'departments.id', '=', 'department_courses.department_id')
                    ->join('courses', 'department_courses.course_id', '=', 'courses.id')
                    ->select('department_courses.id', LanguageHelper::getNameColumnName('courses', 'name'))
                    ->where('departments.id', '=', $request->department_id)
                    ->get();

                return ResponseHelper::successWithData($departmentCourses);
            } else {
                return ResponseHelper::clientError();
                // return response()->json(['error_message' => 'department_id is empty'], 401);
            }
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function retrieveDepartmentLevelCourses(Request $request)
    {
        Gate::authorize('retrieveDepartmentLevelCourses', FilterController::class);
        if (ValidateHelper::validateData($request, [
            'department_id' => 'required|integer',
            'level_id' =>  ['required', new Enum(LevelsEnum::class)],
        ])) {
            return  ResponseHelper::clientError();
        }
        try {
            if ($request->department_id && $request->level_id) {
                // // Fetch department with related department courses and courses
                // $departmentCourses = Department::where('id', $request->department_id)
                // ->with(['department_courses' => function($query) use ($request) {
                //     $query->where('level', $request->level_id)
                //             ->with('course');
                // }])
                // ->first();

                // // Extract the required information
                //  $data = $departmentCourses->department_courses->map(function($departmentCourse) {
                //     return [
                //         'id' => $departmentCourse->id,
                //         'name' => $departmentCourse->course->arabic_name,
                //     ];
                // });

                $departmentCourses =  DB::table('departments')
                    ->join('department_courses', 'departments.id', '=', 'department_courses.department_id')
                    ->join('courses', 'department_courses.course_id', '=', 'courses.id')
                    ->select('department_courses.id', LanguageHelper::getNameColumnName('courses', 'name'))
                    ->where('departments.id', '=', $request->department_id)
                    ->where('department_courses.level', '=', $request->level_id)
                    ->get();

                return ResponseHelper::successWithData($departmentCourses);
            } else {
                return ResponseHelper::clientError();
                // return response()->json(['error_message' => 'department_id or level_id is empty'], 401);
            }
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function retrieveDepartmentLevelSemesterCourses(Request $request)
    {
        Gate::authorize('retrieveDepartmentLevelSemesterCourses', FilterController::class);
        if (ValidateHelper::validateData($request, [
            'department_id' => 'required|integer',
            'level_id' =>  ['required', new Enum(LevelsEnum::class)],
            'semester_id' =>  ['required', new Enum(SemesterEnum::class)],
        ])) {
            return  ResponseHelper::clientError();
        }
        try {
            if ($request->department_id && $request->level_id && $request->semester_id) {
                $departmentCourses =  DB::table('departments')
                    ->join('department_courses', 'departments.id', '=', 'department_courses.department_id')
                    ->join('courses', 'department_courses.course_id', '=', 'courses.id')
                    ->select('department_courses.id', LanguageHelper::getNameColumnName('courses', 'name'))
                    ->where('departments.id', '=', $request->department_id)
                    ->where('department_courses.level', '=', $request->level_id)
                    ->where('department_courses.semester', '=', $request->semester_id)
                    ->get();

                return ResponseHelper::successWithData($departmentCourses);
            } else {
                return ResponseHelper::clientError();
                // return response()->json(['error_message' => 'department_id or level_id or semester_id is empty'], 401);
            }
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function retrieveDepartmentCourseParts(Request $request)
    {
        Gate::authorize('retrieveDepartmentCourseParts', FilterController::class);
        if (ValidateHelper::validateData($request, [
            'department_course_id' => 'required|integer'
        ])) {
            return  ResponseHelper::clientError();
        }
        try {
            if ($request->department_course_id) {
                $departmentCourseParts =  DB::table('department_course_parts')
                    ->join('course_parts', 'department_course_parts.course_part_id', '=', 'course_parts.id')
                    ->select(
                        'department_course_parts.id',
                        'course_parts.part_id as name'
                    )
                    ->where('department_course_parts.department_course_id', '=', $request->department_course_id)
                    ->get();

                $departmentCourseParts = ProcessDataHelper::enumsConvertIdToName($departmentCourseParts, [
                    new EnumReplacement('name', CoursePartsEnum::class),
                ]);

                return ResponseHelper::successWithData($departmentCourseParts);
            } else {
                return ResponseHelper::clientError();
                // return response()->json(['error_message' => 'department_course_id is empty'], 401);
            }
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function retrieveDepartmentLecturerCourses(Request $request)
    {
        Gate::authorize('retrieveDepartmentLecturerCourses', FilterController::class);
        if (ValidateHelper::validateData($request, [
            'department_id' => 'required|integer'
        ])) {
            return  ResponseHelper::clientError();
        }
        try {
            $user = auth()->user();
            $lecturer = Employee::where('user_id', $user->id)->first();

            // $lecturer = Employee::findOrFail(auth()->user()->id);

            if ($lecturer) {
                $departmentLecturerCourses =  DB::table('course_lecturers')
                    ->join('department_course_parts', 'course_lecturers.department_course_part_id', '=', 'department_course_parts.id')
                    ->join('department_courses', 'department_course_parts.department_course_id', '=', 'department_courses.id')
                    ->join('departments', 'department_courses.department_id', '=', 'departments.id')
                    ->join('courses', 'department_courses.course_id', '=', 'courses.id')
                    ->select(
                        'department_courses.id',
                        LanguageHelper::getNameColumnName('courses', 'name')
                    )
                    ->where('departments.id', '=', $request->department_id)
                    ->where('course_lecturers.lecturer_id', '=', $lecturer->id)
                    ->get();

                return ResponseHelper::successWithData($departmentLecturerCourses);
            } else {
                return ResponseHelper::clientError();
                // return response()->json(['error_message' => 'lectuer not authorized or department_id is empty'], 401);
            }
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function retrieveDepartmentLecturerCurrentCourses(Request $request)
    {
        Gate::authorize('retrieveDepartmentLecturerCurrentCourses', FilterController::class);
        if (ValidateHelper::validateData($request, [
            'department_id' => 'required|integer'
        ])) {
            return  ResponseHelper::clientError();
        }
        try {
            $user = auth()->user();
            $lecturer = Employee::where('user_id', $user->id)->first();

            // $lecturer = Employee::findOrFail(auth()->user()->id);

            if ($lecturer) {
                $departmentLecturerCourses =  DB::table('course_lecturers')
                    ->join('department_course_parts', 'course_lecturers.department_course_part_id', '=', 'department_course_parts.id')
                    ->join('department_courses', 'department_course_parts.department_course_id', '=', 'department_courses.id')
                    ->join('departments', 'department_courses.department_id', '=', 'departments.id')
                    ->join('courses', 'department_courses.course_id', '=', 'courses.id')
                    ->select(
                        'department_courses.id',
                        LanguageHelper::getNameColumnName('courses', 'name')
                    )
                    ->where('departments.id', '=', $request->department_id)
                    ->where('course_lecturers.lecturer_id', '=', $lecturer->id)
                    ->where('course_lecturers.academic_year', '=', now()->format('Y'))
                    ->get();
                return ResponseHelper::successWithData($departmentLecturerCourses);
            } else {
                return ResponseHelper::clientError();
                // return response()->json(['error_message' => 'lectuer not authorized or department_id is empty'], 401);
            }
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function retrieveDepartmentLecturerCourseParts(Request $request)
    {
        Gate::authorize('retrieveDepartmentLecturerCourseParts', FilterController::class);
        if (ValidateHelper::validateData($request, [
            'department_course_id' => 'required|integer'
        ])) {
            return  ResponseHelper::clientError();
        }
        try {
            $user = auth()->user();
            $lecturer = Employee::where('user_id', $user->id)->first();

            // $lecturer = Employee::findOrFail(auth()->user()->id);

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
                    [
                        new EnumReplacement('name', CoursePartsEnum::class)
                    ]
                );

                return ResponseHelper::successWithData($departmentLecturerCourseParts);
            } else {
                return ResponseHelper::clientError();
                // return response()->json(['error_message' => 'lectuer not authorized or department_id is empty'], 401);
            }
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function retrieveDepartmentLecturerCurrentCourseParts(Request $request)
    {
        Gate::authorize('retrieveDepartmentLecturerCurrentCourseParts', FilterController::class);
        if (ValidateHelper::validateData($request, [
            'department_course_id' => 'required|integer'
        ])) {
            return  ResponseHelper::clientError();
        }
        try {
            $user = auth()->user();
            $lecturer = Employee::where('user_id', $user->id)->first();

            // $lecturer = Employee::findOrFail(auth()->user()->id);

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
                    [
                        new EnumReplacement('name', CoursePartsEnum::class)
                    ]
                );

                return ResponseHelper::successWithData($departmentLecturerCourseParts);
            } else {
                return ResponseHelper::clientError();
                // return response()->json(['error_message' => 'lectuer not authorized or department_id is empty'], 401);
            }
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }


    public function retrieveEmployees()
    {
        Gate::authorize('retrieveEmployees', FilterController::class);

        $attributes = ['id', LanguageHelper::getNameColumnName(null, 'name')];
        try {
            $employees = GetHelper::retrieveModels(Employee::class, $attributes, null);

            return ResponseHelper::successWithData($employees);
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function retrieveLecturers()
    {
        Gate::authorize('retrieveLecturers', FilterController::class);

        $attributes = ['id', LanguageHelper::getNameColumnName(null, 'name')];
        try {
            $lecturers = Employee::whereIn(
                'job_type',
                [JobTypeEnum::LECTURER->value, JobTypeEnum::EMPLOYEE_LECTURE->value]
            )
                ->get($attributes);
            return ResponseHelper::successWithData($lecturers);
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function retrieveEmployeesOfJob(Request $request)
    {
        Gate::authorize('retrieveEmployeesOfJob', FilterController::class);
        if (ValidateHelper::validateData($request, [
            'job_type_id' =>  ['required', new Enum(JobTypeEnum::class)]
        ])) {
            return  ResponseHelper::clientError();
        }
        $attributes = ['id', LanguageHelper::getNameColumnName(null, 'name')];
        $conditionAttribute = ['job_type' => $request->job_type_id];
        try {
            $employees = GetHelper::retrieveModels(Employee::class, $attributes, $conditionAttribute);

            return ResponseHelper::successWithData($employees);
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    // public function retrieveAcademicYears()
    // {
    //     Gate::authorize('retrieveEditableCourse', FilterController::class);
    // }

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

    public function retrieveNonOwnerEmployees(Request $request)
    {
        Gate::authorize('retrieveNonOwnerEmployees', FilterController::class);
        if (ValidateHelper::validateData($request, [
            'job_type_id' =>  ['required', new Enum(JobTypeEnum::class)]
        ])) {
            return  ResponseHelper::clientError();
        }
        try {
            $nonOwnerEmployees = Employee::where('user_id', '=', null)
                ->where('job_type', '=', $request->job_type_id)
                ->get(['id', LanguageHelper::getNameColumnName(null, 'name')]);
            return ResponseHelper::successWithData($nonOwnerEmployees);
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }
    public function retrieveNonOwnerStudents(Request $request)
    {
        Gate::authorize('retrieveNonOwnerStudents', FilterController::class);
        if (ValidateHelper::validateData($request, [
            'department_id' => 'required|integer',
            'level_id' =>  ['required', new Enum(LevelsEnum::class)]
        ])) {
            return  ResponseHelper::clientError();
        }
        try {

            $nonOwnerStudents = DB::table('department_courses')
            ->join('course_students', 'department_courses.id', '=', 'course_students.department_course_id')
            ->join('students', 'course_students.student_id', '=', 'students.id')
            ->select('students.id', LanguageHelper::getNameColumnName('students', 'name'),)
            ->where('department_courses.department_id', '=', $request->department_id)
            ->where('department_courses.level', '=', $request->level_id)
            ->where('students.user_id', '=', null)
            ->distinct()
            ->get();
            return ResponseHelper::successWithData($nonOwnerStudents);
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function retrieveRoles(Request $request)
    {
        Gate::authorize('retrieveRoles', FilterController::class);
        if (ValidateHelper::validateData($request, [
            'owner_type_id' =>  ['required', new Enum(OwnerTypeEnum::class)]
        ])) {
            return  ResponseHelper::clientError();
        }
        try {
            $roles = RoleEnum::getOwnerRoles($request->owner_type_id);
            return ResponseHelper::successWithData($roles);
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function retrieveProctors()
    {
        Gate::authorize('retrieveProctors', FilterController::class);

        try {
            $proctors =  DB::table('employees')
                ->join('users', 'employees.user_id', '=', 'users.id')
                ->join('user_roles', 'users.id', '=', 'user_roles.user_id')
                ->select('employees.id', LanguageHelper::getNameColumnName('employees', 'name'))
                ->where('user_roles.role_id', '=', RoleEnum::PROCTOR->value)
                ->get();

            return ResponseHelper::successWithData($proctors);
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }
}
