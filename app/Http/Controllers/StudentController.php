<?php

namespace App\Http\Controllers;

use App\Models\User;
use  App\Models\Student;
use App\Enums\GenderEnum;
use App\Helpers\GetHelper;
use App\Helpers\UserHelper;
use \Illuminate\Support\Str;
use App\Enums\OwnerTypeEnum;
use App\Helpers\ImageHelper;
use Illuminate\Http\Request;
use App\Helpers\DeleteHelper;
use App\Helpers\ModifyHelper;
use App\Enums\StudentTypeEnum;
use App\Helpers\ResponseHelper;
use App\Helpers\ValidateHelper;
use App\Enums\QualificationEnum;
use App\Enums\StudentStatusEnum;
use App\Helpers\EnumReplacement;
use App\Models\DepartmentCourse;
use App\Helpers\EnumReplacement1;
use App\Helpers\ColumnReplacement;
use App\Helpers\ProcessDataHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rules\Enum;
use App\Enums\CourseStudentStatusEnum;
use App\Enums\LevelsEnum;
use App\Models\CourseStudent;
use  Illuminate\Support\Facades\Validator;

class StudentController extends Controller
{

    public function addStudent(Request $request)
    {
        if (ValidateHelper::validateData($request, $this->rules($request))) {
            return  ResponseHelper::clientError(401);
        }
        $student =  Student::create([
            'academic_id' => $request->academic_id,
            'arabic_name' =>  $request->arabic_name,
            'english_name' =>  $request->english_name,
            'gender' =>  $request->gender_id,
            'phone' => $request->phone ?? null,
            'birthdate' =>  $request->birthdate ?? null,
            'image_url' => ImageHelper::uploadImage($request->image),
        ]);

        // add initail student courses, that belonge to (department, level)
        $this->addStudentCoures($student->id, $request->department_id, $request->level_id);

        if ($request->email) {
            if (UserHelper::addUser($request->email, OwnerTypeEnum::STUDENT->value, $student->id)) {
                return ResponseHelper::success();
            }
        }else{
            return ResponseHelper::success();
        }
        return ResponseHelper::serverError();
    }


    public function modifyStudent(Request $request, Student $student)
    {
        if (ValidateHelper::validateData($request, $this->rules($request))) {
            return  ResponseHelper::clientError(401);
        }
        $student = Student::findOrFail($request->id);
        $student->update([
            'academic_id' => $request->academic_id ?? $student->academic_id,
            'arabic_name' =>  $request->arabic_name ?? $student->arabic_name,
            'english_name' =>  $request->english_name ?? $student->english_name,
            'phone' => $request->phone ?? $student->phone,
            'image_url' => ImageHelper::updateImage($request->image, $student->image_url),
            'birthdate' =>  $request->birthdate ?? $student->birthdate,
            'gender' =>  $request->gender_id ?? $student->gender,
        ]);

        if ($request->level_id) {
            $studnetLevel = $this->getStudentLevel($student->id);
            $studnetDepartment = $this->getStudentDepartment($student->id);
            if ($request->level_id <= $studnetLevel) {
                return ResponseHelper::clientError(401);
                // return ResponseHelper::clientError('لا يمكنك تغيير مستوى الطالب الي مستوى ادنى من المستوى الحالي');
            } else {
                // update status of courses for last level
                $currentCourseStudents = DB::table('students')
                    ->join('course_students', 'students.id', '=', 'course_students.student_id')
                    ->join('department_courses', 'course_students.department_course_id', '=', 'department_courses.id')
                    ->select('course_students.department_course_id')
                    ->where('department_courses.level', '=', $studnetLevel)
                    ->where('students.id', '=', $student->id)
                    ->get();

                foreach ($currentCourseStudents as $currentCourseStudent) {
                    $student->course_students()->update([
                        'status' => CourseStudentStatusEnum::PASSED->value
                    ])->where('department_course_id', '=', $currentCourseStudent->department_course_id);
                }
                // add new level courses
                $this->addStudentCoures($student->id, $studnetDepartment, $request->level_id);
            }
        }
        return ResponseHelper::success();
    }


    public function deleteStudent(Request $request)
    {
        $employee = Student::findeOrFail($request->id);
        return DeleteHelper::deleteModel($employee);
    }

    public function retrieveStudents(Request $request)
    {
        $students = DB::table('departments')
            ->join('department_courses', 'departments.id', '=', 'department_courses.department_id')
            ->join('course_students', 'department_courses.id', '=', 'course_students.department_course_id')
            ->join('studets', 'course_students.student_id', '=', 'studets.id')
            ->select('studets.id', 'studets.academic_id', 'studets.arabic_name as name', 'gender as gender_name', 'imge_url')
            ->where('departments.id', '=', $request->department_id)
            ->where('department_courses.level', '=', $request->level_id)
            ->distinct()
            ->get();

        $enumReplacements = [
            new EnumReplacement('gender_name', GenderEnum::class),
        ];
        $students =  ProcessDataHelper::enumsConvertIdToName($students, $enumReplacements);
        return ResponseHelper::successWithData($students);
    }

    public function retrieveStudent(Request $request)
    {
        // $student = Student::with([
        //     'academic_id,arabic_name,english_name,gender as gender_id,phone,user_id as email,image_url,birthdate', //**test sigle column */
        //     'course_students.department_course.department:arabic_name as department_name.college:arabic_name as college_name',
        //     ])->find($request->id);
        // { college name, department name, level id

        $student =  DB::table('students')
            ->join('course_students', 'students.id', '=', 'course_students.student_id')
            ->join('department_courses', 'course_students.department_course_id', '=', 'department_course.id')
            ->join('departments', 'department_courses.department_id', '=', 'departments.id')
            ->join('colleges', 'departments.college_id', '=', 'colleges.id')
            ->select(
                'students.academic_id',
                'students.arabic_name',
                'students.english_name',
                'students.gender as gender_name',
                'students.user_id as email',
                'students.image_url',
                'students.birthdate',
                'students.phone',
                'departments.arabic_name as department_name',
                'colleges.arabic_name as college_name'
            )
            ->where('students.id', '=', $request->id)
            ->get();
        $enumReplacements = [
            new EnumReplacement('gender_name', GenderEnum::class),
            new EnumReplacement('level_name', LevelsEnum::class),
        ];
        $columnReplacements = [
            new ColumnReplacement('email', 'email', User::class)
        ];
        $student['level_name'] = $this->getStudentLevel($request->id);
        $student = ProcessDataHelper::enumsConvertIdToName($student, $enumReplacements);
        $student = ProcessDataHelper::columnConvertIdToName($student, $columnReplacements);

        return ResponseHelper::successWithData($student);
    }

    public function retrieveEditableStudent(Request $request)
    {
        $attributes = ['academic_id', 'arabic_name', 'english_name', 'gender as gender_id', 'phone', 'birthdate', 'image_url'];
        $student = Student::findeOrFail($request->id, $attributes);
        $student['level_id'] = $this->getStudentLevel($request->id);

        return ResponseHelper::successWithData($student);
    }

    private function addStudentCoures($studnetId, $departmentId, $levelId)
    {
        $departmentCourses = DepartmentCourse::where('department_id', '=', $departmentId)
            ->where('level', '=', $levelId);
        foreach ($departmentCourses as $departmentCourse) {
            $departmentCourse->course_students()->create([
                'student_id' => $studnetId,
                'academic_year' => now()->format('Y')
            ]);
        }
    }

    private function getStudentLevel($studnetId)
    {
        $studnetLevel = DB::table('students')
        ->join('course_students', 'students.id', '=', 'course_students.student_id')
        ->join('department_courses', 'course_students.department_course_id', '=', 'department_courses.id')
        ->select('department_courses.level as level_id')
        ->where('students.id', '=', $studnetId)
        ->where('course_students.status', '=', CourseStudentStatusEnum::ACTIVE->value)
        ->distinct()
        ->orderBy('department_courses.level') // التاكد هل يتم الترتيب تنازليا او يتم استخدام دالة المكس وحذف الاردر
        ->first();

        return $studnetLevel;
    }

    private function getStudentDepartment($studnetId)
    {
        $studnetDepartment = DB::table('students')
        ->join('course_students', 'students.id', '=', 'course_students.student_id')
        ->join('department_courses', 'course_students.department_course_id', '=', 'department_courses.id')
        ->select('department_courses.department_id')
        ->where('students.id', '=', $studnetId)
        ->first();
        return $studnetDepartment;
    }

    public function rules(Request $request): array
    {
        $rules = [
            'email' => 'nullable|email|unique:users,email',
            'academic_id' => 'required|integer',
            'arabic_name' => 'required|string',
            'english_name' => 'required|string',
            'phone' => 'nullable|string|unique:students,phone',
            'image' => 'nullable|string',
            'gender_id' => ['required', new Enum(GenderEnum::class)],
            'birthdate' => 'nullable|date',
            // 'user_id' => 'nullable|uuid|unique:users,id',
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
