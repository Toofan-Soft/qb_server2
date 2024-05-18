<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Enums\GenderEnum;
use App\Enums\LevelsEnum;
use App\Models\OnlineExam;
use App\Enums\ExamTypeEnum;
use App\Enums\SemesterEnum;
use App\Helpers\ExamHelper;
use Illuminate\Http\Request;
use App\Enums\ExamStatusEnum;
use App\Models\StudentAnswer;
use App\Enums\CoursePartsEnum;
use App\Helpers\ResponseHelper;
use App\Helpers\EnumReplacement;
use App\Helpers\EnumReplacement1;
use App\Models\StudentOnlineExam;
use App\Helpers\ProcessDataHelper;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Enums\CourseStudentStatusEnum;
use Illuminate\Support\Facades\Storage;
use App\Enums\StudentOnlineExamStatusEnum;
use App\Models\RealExam;

class ProctorOnlinExamController extends Controller
{
    public function retrieveOnlineExams(Request $request)
    {
        $proctor = Employee::where('user_id', auth()->user()->id)->first();
        $onlineExams =  DB::table('online_exams')
        ->join('real_exams', 'online_exams.id', '=', 'real_exams.id')
        ->join('course_lucturers', 'real_exams.course_lucturer_id', '=', 'course_lucturers.id')
        ->join('department_course_parts', 'course_lucturers.department_course_part_id', '=', 'department_course_parts.id')
        ->join('department_courses', 'department_course_parts.department_course_id', '=', 'department_courses.id')
        ->join('courses', 'department_courses.course_id', '=', 'courses.id')
        ->join('course_parts', 'department_course_parts.course_part_id', '=', 'course_parts.id')
        ->select(
         'courses.arabic_name as course_name ',
         'course_parts.part_id as course_part_name ',
         'real_exams.id','real_exams.datetime',
         )
        ->where('online_exams.proctor_id', '=', $proctor->id)
        ->where('online_exams.status','=', ExamStatusEnum::ACTIVE->value)
    ->get();
$onlineExams = ProcessDataHelper::enumsConvertIdToName($onlineExams ,[new EnumReplacement('course_part_name', CoursePartsEnum::class)] );

return $onlineExams;

    }

    public function retrieveOnlineExam(Request $request)
    {
        $realExam = RealExam::findOrFail($request->id ,[
            'id','datetime', 'duration',
            'type as type_name', 'note as special_note', 'course_lecturer_id'
        ]);
        $realExam = ProcessDataHelper::enumsConvertIdToName($realExam, [
            new EnumReplacement('type_name', ExamTypeEnum::class)
        ]);
        $jsonData = Storage::disk('local')->get('generalNotes.json'); // get notes from json file
        $general_note = json_decode($jsonData, true);
        $realExam['general_note'] =  $general_note;        //// Done

        $realExam = ExamHelper::getRealExamsScore($realExam);
        $courselecturer = $realExam->course_lecturer()->first();
        $lecturer =  Employee::where('id', $courselecturer->lecturer_id)->first(['arabic_name as lecturer_name']);
        $departmentCoursePart = $courselecturer->department_course_part()->first();
        $coursePart = $departmentCoursePart->course_part()->first(['part_id as course_part_name']);
        $coursePart = ProcessDataHelper::enumsConvertIdToName($coursePart, [
            new EnumReplacement('course_part_name', CoursePartsEnum::class),
        ]);

        $departmentCourse = $departmentCoursePart->department_course()->first(['level as level_name', 'semester as semester_name', 'department_id', 'course_id']);
        $departmentCourse = ProcessDataHelper::enumsConvertIdToName($departmentCourse, [
            new EnumReplacement('level_name', LevelsEnum::class),
            new EnumReplacement('semester_name', SemesterEnum::class),
        ]);

        $department = $departmentCourse->department()->first(['arabic_name as department_name', 'college_id']);
        $college = $department->college()->first(['arabic_name as college_name']);
        $course = $departmentCourse->course()->first(['arabic_name as course_name']);

        //*** make unset to : 'department_id', 'course_id', 'college_id', 'course_lecturer_id' , 'id'
        $departmentCourse = $departmentCourse->toArray();
        unset($departmentCourse['department_id']);
        unset($departmentCourse['course_id']);

        $department = $department->toArray();
        unset($department['college_id']);

        $realExam = $realExam->toArray();
        unset($realExam['course_lecturer_id']);
        unset($realExam['id']);

        $realExam =
                    $realExam  +
                    $lecturer->toArray() +
                    $coursePart->toArray() +
                    $departmentCourse  +
                    $department +
                    $college->toArray() +
                    $course->toArray();

        return ResponseHelper::successWithData($realExam);
    }

    public function retrieveOnlineExamStudents(Request $request)
    {
        $students =  DB::table('online_exams')
        ->join('real_exams', 'online_exams.id', '=', 'real_exams.id')
        ->join('course_lecturers', 'real_exams.course_lecturer_id', '=', 'course_lecturers.id')
        ->join('department_course_parts', 'course_lecturers.department_course_part_id', '=', 'department_course_parts.id')
        ->join('department_courses', 'department_course_parts.department_course_id', '=', 'department_courses.id')
        ->join('course_students', 'department_courses.id', '=', 'course_students.department_course_id')
        ->join('students', 'course_students.student_id', '=', 'students.id')
        ->select(
         'students.id', 'students.academic_id', 'students.arabic_name as name',
         'students.gender as gender_name', 'students.image_url'
         )
        ->where('online_exams.id', '=', $request->exam_id)
        ->where('course_students.status','=', CourseStudentStatusEnum::ACTIVE->value)
        // ->where('course_students.academic_year','=', 'course_lucturers_academic_year') // check for this condition
        ->get();

        $enumReplacements =[new EnumReplacement('gender_name', GenderEnum::class)];
        $students = ProcessDataHelper::enumsConvertIdToName($students, $enumReplacements);

        foreach ($students as $student) {
            // $student->status_name = null;
            $student->form_name = null;
            // $student->start_datetime = null;
            // $student->end_datetime = null;
            // $student->answered_questions_count = null;
        }
        return $students;
    }

    public function refreshOnlineExamStudents(Request $request)
    {
        $onlineExam = OnlineExam::findOrFail($request->exam_id);
        $onlineExamStudents = $onlineExam->student_online_exams()->get([
            'student_id', 'start_datetime', 'end_datetime', 'status as status_name'
        ]);// to array

        foreach ($onlineExamStudents as $onlineExamStudent) {
            $onlineExamStudent['answered_questions_count'] = $this->getStudentAnsweredQuestionsCount($request->exam_id, $onlineExamStudent->student_id);

            if(intval($onlineExamStudent->status_name ) === StudentOnlineExamStatusEnum::ACTIVE->value){
                $onlineExamStudent['is_started'] = true;
                $onlineExamStudent['is_suspended'] = false;
                $onlineExamStudent['is_finished'] = false;
            }elseif(intval($onlineExamStudent->status_name ) === StudentOnlineExamStatusEnum::SUSPENDED->value){
                $onlineExamStudent['is_started'] = true;
                $onlineExamStudent['is_suspended'] = true;
                $onlineExamStudent['is_finished'] = false;
            }elseif(intval($onlineExamStudent->status_name )  === StudentOnlineExamStatusEnum::COMPLETE->value){
                $onlineExamStudent['is_started'] = true;
                $onlineExamStudent['is_suspended'] = false;
                $onlineExamStudent['is_finished'] = true;
            }else{
                $onlineExamStudent['is_started'] = true;
                $onlineExamStudent['is_suspended'] = false;
                $onlineExamStudent['is_finished'] = true;
            }
// الطلاب الذين لم يبدو الاختبار بعد يتم ارجاع المتغيرات بقية خطاء
        }

        $onlineExamStudents = ProcessDataHelper::enumsConvertIdToName($onlineExamStudents, [new EnumReplacement('status_name', StudentOnlineExamStatusEnum::class)]);
        return $onlineExamStudents;
    }


    public function suspendStudentOnlineExam(Request $request)
    {
        $studentOnlineExam = StudentOnlineExam::where('online_exam_id', $request->exam_id)
            ->where('student_id', $request->student_id)
            ->first();

        if ($studentOnlineExam && intval($studentOnlineExam->status) === StudentOnlineExamStatusEnum::ACTIVE->value) {
            StudentOnlineExam::where('online_exam_id', $request->exam_id)
                ->where('student_id', $request->student_id)
                ->update([
                    'status' => StudentOnlineExamStatusEnum::SUSPENDED->value,
                ]);
            return ResponseHelper::success();
        } else {
            return abort(404);
        }
    }

    public function continueStudentOnlineExam(Request $request)
    {
        $studentOnlineExam = StudentOnlineExam::where('online_exam_id', $request->exam_id)
        ->where('student_id', $request->student_id)
        ->first();

    if ($studentOnlineExam && intval($studentOnlineExam->status) === StudentOnlineExamStatusEnum::SUSPENDED->value) {
        StudentOnlineExam::where('online_exam_id', $request->exam_id)
            ->where('student_id', $request->student_id)
            ->update([
                'status' => StudentOnlineExamStatusEnum::ACTIVE->value,
            ]);
        return ResponseHelper::success();
    } else {
        return abort(404);
    }
    }

    public function finishStudentOnlineExam(Request $request)
    {
        $studentOnlineExam = StudentOnlineExam::where('online_exam_id', $request->exam_id)
        ->where('student_id', $request->student_id)
        ->first();

    if ($studentOnlineExam && intval($studentOnlineExam->status) === StudentOnlineExamStatusEnum::COMPLETE->value) {
        StudentOnlineExam::where('online_exam_id', $request->exam_id)
            ->where('student_id', $request->student_id)
            ->update([
                'status' => StudentOnlineExamStatusEnum::CANCELED->value,
                'end_datetime' => now()
            ]);
        return ResponseHelper::success();
    } else {
        return abort(404);
    }
    }

    // not complete
    private function getStudentAnsweredQuestionsCount($formId, $studentId)
    {

        $formId = 1; // يتم عمل دالة لمعرفة رقم النموذج حق الطالب، او جعل هذه الدالة تستقبل رقم النموذج
        $questionsCount = StudentAnswer::where('form_id', '=', $formId)
        ->where('student_id', '=', $studentId)->count();

        return $questionsCount;
    }
}
