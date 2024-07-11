<?php

namespace App\Http\Controllers;

use App\Models\User;
use  App\Models\Student;
use App\Enums\GenderEnum;
use App\Enums\LevelsEnum;
use App\Helpers\GetHelper;
use App\Helpers\UserHelper;
use \Illuminate\Support\Str;
use App\Enums\OwnerTypeEnum;
use App\Helpers\ImageHelper;
use Illuminate\Http\Request;
use App\Helpers\DeleteHelper;
use App\Helpers\ModifyHelper;
use App\Models\CourseStudent;
use App\Enums\CoursePartsEnum;
use App\Enums\StudentTypeEnum;
use App\Enums\CourseStatusEnum;
use App\Helpers\DatetimeHelper;
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
use App\Helpers\NullHelper;
use  Illuminate\Support\Facades\Validator;

class StudentController extends Controller
{

    public function addStudent(Request $request)
    {
        if (ValidateHelper::validateData($request, $this->rules($request))) {
            return  ResponseHelper::clientError1(401);
        }
        DB::beginTransaction();
        try {
            $student =  Student::create([
                'academic_id' => $request->academic_id,
                'arabic_name' =>  $request->arabic_name,
                'english_name' =>  $request->english_name,
                'phone' => $request->phone ?? null,
                'image_url' => $request->hasFile('image') ? ImageHelper::uploadImage($request->file('image')) : null,
                'birthdate' =>  $request->birthdate ?? null,
                'gender' =>  $request->gender_id,
            ]);

            // add initail student courses, that belonge to (department, level)
            $this->addStudentCoures($student->id, $request->department_id, $request->level_id);
            DB::commit();
            if ($request->email) {
                if (!UserHelper::addUser($request->email, OwnerTypeEnum::STUDENT->value, $student->id)) {
                    return ResponseHelper::serverError(401);
                    // return ResponseHelper::serverError('لم يتم اضافة حساب لهذا الطالب');
                }
            }
            return ResponseHelper::success();
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseHelper::serverError();
        }
    }

    public function modifyStudent(Request $request, Student $student)
    {
        if (ValidateHelper::validateData($request, $this->rules($request))) {
            return  ResponseHelper::clientError(401);
        }
        DB::beginTransaction();
        try {
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
                $studnetDepartmentAndLevel = $this->getStudentDepartmentAndLevel($student->id);

                if ($request->level_id <= $studnetDepartmentAndLevel->level_id) {
                    return ResponseHelper::clientError(401);
                    // return ResponseHelper::clientError('لا يمكنك تغيير مستوى الطالب الي مستوى ادنى من المستوى الحالي');
                } else {
                    // aupdate status of courses for last level
                    $currentCourseStudents = DB::table('students')
                        ->join('course_students', 'students.id', '=', 'course_students.student_id')
                        ->join('department_courses', 'course_students.department_course_id', '=', 'department_courses.id')
                        ->select('course_students.department_course_id')
                        ->where('department_courses.level', '=', $studnetDepartmentAndLevel->level_id)
                        ->where('students.id', '=', $student->id)
                        ->get();

                    foreach ($currentCourseStudents as $currentCourseStudent) {
                        $student->course_students()->where('department_course_id', '=', $currentCourseStudent->department_course_id)
                            ->update([
                                'status' => CourseStudentStatusEnum::PASSED->value
                            ]);
                    }
                    // add new level courses
                    $this->addStudentCoures($student->id, $studnetDepartmentAndLevel->department_id, $request->level_id);
                }
            }
            DB::commit();
            return ResponseHelper::success();
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseHelper::serverError();
        }
    }

    public function deleteStudent(Request $request)
    {
        DB::beginTransaction();
        try {
            $student = Student::findOrFail($request->id);
            $student->user()->delete();
            $student->delete();
            DB::commit();
            return ResponseHelper::success();
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseHelper::serverError();
        }
    }

    public function retrieveStudents(Request $request)
    {
        try {
            $students = DB::table('departments')
                ->join('department_courses', 'departments.id', '=', 'department_courses.department_id')
                ->join('course_students', 'department_courses.id', '=', 'course_students.department_course_id')
                ->join('students', 'course_students.student_id', '=', 'students.id')
                ->select('students.id', 'students.academic_id', 'students.arabic_name as name', 'gender as gender_name', 'image_url')
                ->where('departments.id', '=', $request->department_id)
                ->where('department_courses.level', '=', $request->level_id)
                ->distinct()
                ->get();

            $enumReplacements = [
                new EnumReplacement('gender_name', GenderEnum::class),
            ];
            $students =  ProcessDataHelper::enumsConvertIdToName($students, $enumReplacements);
            $students = NullHelper::filter($students);
            return ResponseHelper::successWithData($students);
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function retrieveStudent(Request $request)
    {
        try {
            // return ResponseHelper::success();
            $student = Student::where('id', $request->id)
                ->firstOrFail();

            $studentData = [
                'academic_id' => $student->academic_id,
                'arabic_name' => $student->arabic_name,
                'english_name' => $student->english_name,
                'gender_name' => $student->gender,
                'email' => $student->user->email,
                'image_url' => $student->image_url,
                'birthdate' => $student->birthdate,
                'phone' => $student->phone,
                'department_name' => $student->course_students->first()->department_course->department->arabic_name,
                'college_name' => $student->course_students->first()->department_course->department->college->arabic_name,
            ];
            $studentData = NullHelper::filter($studentData);
            $studentData['level_name'] = $this->getStudentDepartmentAndLevel($request->id)->level_id;

            // Enum replacements
            $enumReplacements = [
                new EnumReplacement('gender_name', GenderEnum::class),
                new EnumReplacement('level_name', LevelsEnum::class),
            ];

            $studentData = ProcessDataHelper::enumsConvertIdToName((object) $studentData, $enumReplacements);

            return ResponseHelper::successWithData($studentData);
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    // public function retrieveStudent1(Request $request)
    // {
    //     $student =  DB::table('students')
    //         ->join('course_students', 'students.id', '=', 'course_students.student_id')
    //         ->join('department_courses', 'course_students.department_course_id', '=', 'department_courses.id')
    //         ->join('departments', 'department_courses.department_id', '=', 'departments.id')
    //         ->join('colleges', 'departments.college_id', '=', 'colleges.id')
    //         ->select(
    //             'students.academic_id',
    //             'students.arabic_name',
    //             'students.english_name',
    //             'students.gender as gender_name',
    //             'students.user_id as email',
    //             'students.image_url',
    //             'students.birthdate',
    //             'students.phone',
    //             'departments.arabic_name as department_name',
    //             'colleges.arabic_name as college_name'
    //         )
    //         ->where('students.id', '=', $request->id)
    //         ->first();

    //     $student->birthdate = DatetimeHelper::convertTimestampToMilliseconds($student->birthdate);

    //     $enumReplacements = [
    //         new EnumReplacement('gender_name', GenderEnum::class),
    //         new EnumReplacement('level_name', LevelsEnum::class),
    //     ];
    //     $columnReplacements = [
    //         new ColumnReplacement('email', 'email', User::class)
    //     ];
    //     $student->level_name = ($this->getStudentDepartmentAndLevel($request->id))->level_id;
    //     $student = ProcessDataHelper::enumsConvertIdToName($student, $enumReplacements);
    //     $student = ProcessDataHelper::columnConvertIdToName($student, $columnReplacements);

    //     return ResponseHelper::successWithData($student);
    // }

    public function retrieveEditableStudent(Request $request)
    {
        $attributes = ['academic_id', 'arabic_name', 'english_name', 'gender as gender_id', 'phone', 'birthdate', 'image_url'];
        try {
            $student = Student::findOrFail($request->id, $attributes);
            $student = NullHelper::filter($student);
            $studnetDepartmentAndLevel = $this->getStudentDepartmentAndLevel($request->id);
            if ($studnetDepartmentAndLevel !==  null) {
                $student['level_id'] = $studnetDepartmentAndLevel->level_id;
            }
            return ResponseHelper::successWithData($student);
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    private function addStudentCoures($studnetId, $departmentId, $levelId)
    {
        try {
            $departmentCourses = DepartmentCourse::where('department_id', '=', $departmentId)
                ->where('level', '=', $levelId)->get();
            foreach ($departmentCourses as $departmentCourse) {
                $departmentCourse->course_students()->create([
                    'student_id' => $studnetId,
                    'status' => CourseStudentStatusEnum::ACTIVE->value,
                    'academic_year' => now()->format('Y')
                ]);
            }
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    private function getStudentDepartmentAndLevel($studnetId)
    {
        try {
            return ResponseHelper::success();
            $studnetDepartmentAndLevel =  DB::table('students')
                ->join('course_students', 'students.id', '=', 'course_students.student_id')
                ->join('department_courses', 'course_students.department_course_id', '=', 'department_courses.id')
                // ->join('courses', 'department_courses.course_id', '=', 'courses.id')
                // ->join('course_parts', 'courses.id', '=', 'course_parts.course_id')
                // ->join('departments', 'department_courses.department_id', '=', 'departments.id')
                ->select(
                    'department_courses.level as level_id',
                    'department_courses.department_id as department_id',
                )
                ->where('students.id', '=', $studnetId)
                // ->where('course_parts.status', '=', CourseStatusEnum::AVAILABLE->value) // Assuming there's a column indicating if the course is active
                ->orderBy('department_courses.level', 'desc') // Order by level in descending order
                ->first();

            return $studnetDepartmentAndLevel;
        } catch (\Exception $e) {
            throw $e;
        }
    }


    public function rules(Request $request): array
    {
        $rules = [
            'academic_id' => 'required|integer',
            'arabic_name' => 'required|string',
            'english_name' => 'required|string',
            'phone' => 'nullable|integer',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'gender_id' => ['required', new Enum(GenderEnum::class)],
            'birthdate' => 'nullable|integer',
            'department_id' => 'required|exists:departments,id',
            'level_id' => ['required', new Enum(LevelsEnum::class)], // Assuming LevelsEnum holds valid values

            // 'user_id' => 'nullable|uuid|unique:users,id',
            // يتم اضافة الايميل وجعله قابل للنل ، وفريد

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
