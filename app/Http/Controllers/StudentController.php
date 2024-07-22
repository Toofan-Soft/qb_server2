<?php

namespace App\Http\Controllers;

use App\Models\User;
use  App\Models\Student;
use App\Enums\GenderEnum;
use App\Enums\LevelsEnum;
use App\Helpers\GetHelper;
use App\Enums\SemesterEnum;
use App\Helpers\NullHelper;
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
use App\Helpers\LanguageHelper;
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
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\Enum;
use App\Enums\CourseStudentStatusEnum;
use  Illuminate\Support\Facades\Validator;
use App\Helpers\Roles\ByteArrayValidationRule;

class StudentController extends Controller
{

    public function addStudent(Request $request)
    {
        // Gate::authorize('addStudent', StudentController::class);

        if ($x = ValidateHelper::validateData($request, $this->rules($request))) {
            return  ResponseHelper::clientError1($x);
        }

        DB::beginTransaction();
        try {
            $student =  Student::create([
                'academic_id' => $request->academic_id,
                'arabic_name' =>  $request->arabic_name,
                'english_name' =>  $request->english_name,
                'phone' => $request->phone ?? null,
                'image_url' => ImageHelper::uploadImage($request->image),
                // 'image_url' => $request->hasFile('image') ? ImageHelper::uploadImage($request->file('image')) : null,
                'birthdate' =>  $request->birthdate ?? null,
                'gender' =>  $request->gender_id,
            ]);
            // add initail student courses, that belonge to (department, level, semester)
            $this->addStudentCoures($student->id, $request->department_id, $request->level_id, $request->semester_id);

            if ($request->email) {
                if (!UserHelper::addUser($request->email, OwnerTypeEnum::STUDENT->value, $student->id)) {
                    return ResponseHelper::serverError();
                    // return ResponseHelper::serverError('لم يتم اضافة حساب لهذا الطالب');
                }
            }

            DB::commit();
            return ResponseHelper::success();
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseHelper::serverError();
        }
    }

    public function modifyStudent(Request $request, Student $student)
    {
        Gate::authorize('modifyStudent', StudentController::class);

        if (ValidateHelper::validateData($request, $this->rules($request))) {
            return  ResponseHelper::clientError();
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

            if (isset($request->level_id)) {
                if (isset($request->semester_id)) {
                    $studnetDepartmentAndLevel = $this->getStudentDepartmentLevelSemesterIds($student->id);
                  
                    if ($request->level_id < intval($studnetDepartmentAndLevel->level_id)) {
                        return  "currentCourseStudents";
                        return ResponseHelper::clientError();
                        // return ResponseHelper::clientError('لا يمكنك تغيير مستوى الطالب الي مستوى ادنى من المستوى الحالي');
                    } elseif ($request->level_id < intval($studnetDepartmentAndLevel->semester_id)) {
                        return ResponseHelper::clientError();
                        // return ResponseHelper::clientError('لا يمكنك تغيير فصل الطالب الي فصل ادنى من الفصل الحالي');
                    } else {
                        // هل صحيح اننا انجح الطالب للمقررات حق الفصل الحالي
                        // aupdate status of courses for last level
                        $currentCourseStudents = DB::table('students')
                            ->join('course_students', 'students.id', '=', 'course_students.student_id')
                            ->join('department_courses', 'course_students.department_course_id', '=', 'department_courses.id')
                            ->select('course_students.department_course_id')
                            ->where('department_courses.level', '=', $studnetDepartmentAndLevel->level_id)
                            ->where('department_courses.semester', '=', $studnetDepartmentAndLevel->semester_id)
                            ->where('students.id', '=', $student->id)
                            ->get();
                          
                        foreach ($currentCourseStudents as $currentCourseStudent) {
                            $student->course_students()->where('department_course_id', '=', $currentCourseStudent->department_course_id)
                                ->update([
                                    'status' => CourseStudentStatusEnum::PASSED->value
                                ]);
                        }
                        // add new level courses
                        $this->addStudentCoures($student->id, $studnetDepartmentAndLevel->department_id, $request->level_id, $request->semester_id);
                    }
                } else {
                    return ResponseHelper::clientError();
                    // semester id is required
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
        Gate::authorize('deleteStudent', StudentController::class);

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
        Gate::authorize('retrieveStudents', StudentController::class);

        try {
            $students = DB::table('departments')
                ->join('department_courses', 'departments.id', '=', 'department_courses.department_id')
                ->join('course_students', 'department_courses.id', '=', 'course_students.department_course_id')
                ->join('students', 'course_students.student_id', '=', 'students.id')
                ->select('students.id', 'students.academic_id', LanguageHelper::getNameColumnName('students', 'name'), 'gender as gender_name', 'image_url')
                ->where('departments.id', '=', $request->department_id)
                ->where('department_courses.level', '=', $request->level_id)
                ->where('department_courses.semester', '=', $request->semester_id)
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
        Gate::authorize('retrieveStudent', StudentController::class);

        try {
            // $student = Student::findOrFail($request->id);
            $student = Student::where('id', $request->id)
                ->firstOrFail();

            $studentData = [
                'academic_id' => $student->academic_id,
                'arabic_name' => $student->arabic_name,
                'english_name' => $student->english_name,
                'gender_name' => $student->gender,
                // 'email' => $student->user()->first()->email,
                'user_id' => $student->user_id,
                'image_url' => $student->image_url,
                'birthdate' => $student->birthdate,
                'phone' => $student->phone,
                'department_name' => $student->course_students()->first()->department_course()->first()->department()->first()[LanguageHelper::getNameColumnName(null, null)],
                'college_name' => $student->course_students()->first()->department_course()->first()->department()->first()->college()->first()[LanguageHelper::getNameColumnName(null, null)]
            ];

            $studentData['email'] = $student->user_id ? $student->user()->first()->email : null;


            $departmentLevelSemesterIds = $this->getStudentDepartmentLevelSemesterIds($request->id);
            $studentData['level_name'] = $departmentLevelSemesterIds->level_id;
            $studentData['semester_name'] = $departmentLevelSemesterIds->semester_id;

            // Enum replacements
            $enumReplacements = [
                new EnumReplacement('gender_name', GenderEnum::class),
                new EnumReplacement('level_name', LevelsEnum::class),
                new EnumReplacement('semester_name', SemesterEnum::class),
            ];

            $studentData = ProcessDataHelper::enumsConvertIdToName((object) $studentData, $enumReplacements);

            $studentData = NullHelper::filter($studentData);

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
        Gate::authorize('retrieveEditableStudent', StudentController::class);

        $attributes = ['academic_id', 'arabic_name', 'english_name', 'gender as gender_id', 'phone', 'birthdate', 'image_url'];
        try {
            $student = Student::findOrFail($request->id, $attributes);
            $departmentLevelSemesterIds = $this->getStudentDepartmentLevelSemesterIds($request->id);
            $student['level_id'] = $departmentLevelSemesterIds->level_id;
            $student['semester_id'] = $departmentLevelSemesterIds->semester_id;

            $student = NullHelper::filter($student);

            return ResponseHelper::successWithData($student);
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    private function addStudentCoures($studentId, $departmentId, $levelId, $semesterId)
    {
        try {
            $departmentCourses = DepartmentCourse::where('department_id', $departmentId)
            ->where('level', $levelId)
            ->where('semester', $semesterId)
            ->get();
        
        // Check if there's only one departmentCourse
        if ($departmentCourses->count() === 1) {
            $departmentCourse = $departmentCourses->first();
          
            // Check if the course_student already exists
            $existingCourseStudent = $departmentCourse->course_students()->where('student_id', $studentId)->first();
            
            if (!$existingCourseStudent) {
                // Create new course_student if not found
                $departmentCourse->course_students()->create([
                    'student_id' => $studentId,
                    'status' => CourseStudentStatusEnum::ACTIVE->value,
                    'academic_year' => now()->format('Y')
                ]);
            }
        } else {
            foreach ($departmentCourses as $departmentCourse) {
                // Check if the course_student already exists
                $existingCourseStudent = $departmentCourse->course_students()->where('student_id', $studentId)->first();
                
                if (!$existingCourseStudent) {
                    // Create new course_student if not found
                    $departmentCourse->course_students()->create([
                        'student_id' => $studentId,
                        'status' => CourseStudentStatusEnum::ACTIVE->value,
                        'academic_year' => now()->format('Y')
                    ]);
                }
            }
        }


            //////////old 
            // $departmentCourses = DepartmentCourse::where('department_id', '=', $departmentId)
            //     ->where('level', '=', $levelId)
            //     ->where('semester', '=', $semesterId)
            //     ->get();
                 
            // foreach ($departmentCourses as $departmentCourse) {
            //     $departmentCourse->course_students()->create([
            //         'student_id' => $studentId,
            //         'status' => CourseStudentStatusEnum::ACTIVE->value,
            //         'academic_year' => now()->format('Y')
            //     ]);
            // }

        } catch (\Exception $e) {
            throw $e;
        }
    }

    private function getStudentDepartmentLevelSemesterIds($studnetId)
    {
        try {
            // return ResponseHelper::success();
            $studentDepartmentLevelSemesterIds =  DB::table('students')
                ->join('course_students', 'students.id', '=', 'course_students.student_id')
                ->join('department_courses', 'course_students.department_course_id', '=', 'department_courses.id')
                // ->join('courses', 'department_courses.course_id', '=', 'courses.id')
                // ->join('course_parts', 'courses.id', '=', 'course_parts.course_id')
                // ->join('departments', 'department_courses.department_id', '=', 'departments.id')
                ->select(
                    'department_courses.level as level_id',
                    'department_courses.semester as semester_id',
                    'department_courses.department_id as department_id',
                )
                ->where('students.id', '=', $studnetId)
                // ->where('course_parts.status', '=', CourseStatusEnum::AVAILABLE->value) // Assuming there's a column indicating if the course is active
                ->orderBy('department_courses.level', 'desc') // Order by level in descending order
                ->orderBy('department_courses.semester', 'desc') // Order by level in descending order
                ->first();

            return $studentDepartmentLevelSemesterIds;
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
            'image' => ['nullable', new ByteArrayValidationRule],
            'gender_id' => ['required', new Enum(GenderEnum::class)],
            'birthdate' => 'nullable|integer',
            'department_id' => 'required|exists:departments,id',
            'level_id' => ['required', new Enum(LevelsEnum::class)], // Assuming LevelsEnum holds valid values
            'semester_id' => ['required', new Enum(SemesterEnum::class)],
            // 'user_id' => 'nullable|uuid|unique:users,id',
            // يتم اضافة الايميل وجعله قابل للنل ، وفريد
            // التحقق من ان رقم المستوى المرسل موجود في القسم، اي يتوافق مع عدد المستويات، وليس في الاينم
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
