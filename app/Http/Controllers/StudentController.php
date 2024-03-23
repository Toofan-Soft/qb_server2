<?php

namespace App\Http\Controllers;

use App\Models\User;
use  App\Models\Student;
use App\Enums\GenderEnum;
use App\Helpers\GetHelper;
use \Illuminate\Support\Str;
use App\Helpers\ImageHelper;
use Illuminate\Http\Request;
use App\Helpers\DeleteHelper;
use App\Helpers\ModifyHelper;
use App\Enums\StudentTypeEnum;
use App\Enums\QualificationEnum;
use App\Enums\StudentStatusEnum;
use App\Helpers\EnumReplacement;
use App\Helpers\EnumReplacement1;
use App\Helpers\ColumnReplacement;
use App\Helpers\ProcessDataHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rules\Enum;
use  Illuminate\Support\Facades\Validator;

class StudentController extends Controller
{

    public function addEmployee(Request $request)
    {

        $student =  Student::create([
            'academic_id' => $request->academic_id,
            'arabic_name' =>  $request->arabic_name,
            'english_name' =>  $request->english_name,
            'phone' => $request->phone,
            'image_url' => ImageHelper::uploadImage($request->image),
            'birthdate' =>  $request->birthdate,
            'gender' =>  $request->gender ?? GenderEnum::MALE->value,
        ]);

        //*************  connect student with department and level

        if ($request->email) {
            //Add User, email,type,emp_id
        }


    }

    public function modifyStudent (Request $request, Student $student)
    {
        $level_id =null;
        if ($request->level_id) {
           $level_id = $request->level_id;
                                          //*************  connect student with courses and level
        }
        return ModifyHelper::modifyModel($request, $student,  $this->rules($request));
    }


    public function deleteStudent (Student $student)
    {
        return DeleteHelper::deleteModel($student);
    }

    public function retrieveStudents (Request $request)
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

        $enumReplacements =[
            new EnumReplacement1('gender' , GenderEnum::class),
        ];
        $students =  ProcessDataHelper::enumsConvertIdToName($students, $enumReplacements);

        return $students;

        // $attributes = ['id', 'arabic_name', 'gender', 'phone', 'email', 'image_url','birthdate','academic_year'];
        // $conditionAttribute = ['job_type' => $request->job_type_id];
        // $enumReplacements = [
        //     new EnumReplacement('gender', 'gender_name', GenderEnum::class),
        //     new EnumReplacement('qualification', 'qualification_name', QualificationEnum::class),
        //   //  new EnumReplacement('enum_id_column2_db', 'enum_name_name_2',CourseEnum::class),
        //   ];
        //   return GetHelper::retrieveModelsWithEnum(Student::class, $attributes, $conditionAttribute, $enumReplacements);
    }

    public function retrieveStudent(Request $request)
    {
        // $student = Student::with([
        //     'academic_id,arabic_name,english_name,gender as gender_id,phone,user_id as email,image_url,birthdate', //**test sigle column */
        //     'course_students.department_course.department:arabic_name as department_name.college:arabic_name as college_name',
        //     ])->find($request->id);

        $student =  DB::table('students')
        ->join('course_students', 'students.id', '=', 'course_students.student_id')
        ->join('department_courses', 'course_students.department_course_id', '=', 'department_course.id')
        ->join('departments', 'department_courses.department_id', '=', 'departments.id')
        ->join('colleges', 'departments.college_id', '=', 'colleges.id')
        ->select('students.academic_id', 'students.arabic_name', 'students.english_name', 'students.gender as gender_id', 'students.user_id as email','students.image_url','students.birthdate',
           'departments.arabic_name as department_name',
           'colleges.arabic_name as college_name')
        ->where('students.id', '=', $request->id)
        ->get();
        $columnReplacements = [
            new ColumnReplacement('email', 'email', User::class)
          ];
        $student = ProcessDataHelper::columnConvertIdToName($student, $columnReplacements);
        return $student; /// ** find level of this student
    }


    public function rules(Request $request): array
    {
        $rules = [
            'phone' => 'nullable|string|unique:students,phone',
            'image_url' => 'nullable|string|url',
            'gender' => new enum(GenderEnum::class), // Assuming GenderEnum holds valid values
            'birthdate' => 'nullable|date',
            'user_id' => 'nullable|uuid|unique:users,id',
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
