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
use App\Enums\CoursePartsEnum;
use App\Helpers\EnumReplacement1;
use App\Models\StudentOnlineExam;
use App\Helpers\ProcessDataHelper;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Enums\CourseStudentStatusEnum;
use App\Enums\StudentOnlineExamStatusEnum;

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
$onlineExams = ProcessDataHelper::enumsConvertIdToName($onlineExams ,[new EnumReplacement1('course_part_name', CoursePartsEnum::class)] );

return $onlineExams;

    }

    public function retrieveOnlineExam(Request $request)
    {
        $realExam = OnlineExam::findOrFail($request->id)->real_exam()->get([
            'datetime', 'duration',
            'type as type_name', 'note as special_note'
        ]);
        $realExam = ProcessDataHelper::enumsConvertIdToName($realExam, [
               new EnumReplacement1('type_name', ExamTypeEnum::class)
            ]);
            // $realExam['general_note'] = getGeneralNotes();        //// need add   general_note from json file

        $realExam = ExamHelper::getRealExamsScore($realExam);
        $courselecturer = $realExam->lecturer_course();
        $lecturer = $courselecturer->employee()->get(['arabic_name as lecturer_name']);
        $departmentCoursePart = $courselecturer->department_course_part();
        $coursePart = $departmentCoursePart->course_part(['part_id as course_part_name']);
        $coursePart = ProcessDataHelper::enumsConvertIdToName($coursePart, [
               new EnumReplacement1('course_part_name', CoursePartsEnum::class),
        ]);
        $departmentCourse = $departmentCoursePart->department_course()->get(['level as level_name', 'semester as semester_name']);
        $departmentCourse = ProcessDataHelper::enumsConvertIdToName($departmentCourse, [
               new EnumReplacement1('level_name', LevelsEnum::class),
               new EnumReplacement1('semester_name', SemesterEnum::class),
        ]);
        $department = $departmentCourse->department()->get(['arabic_name as department_name']);
        $college = $department->college()->get(['arabic_name as college_name']);
        $course = $departmentCourse->course()->get(['arabic_name as course_name']);

        array_merge($realExam, $lecturer, $coursePart, $departmentCourse, $department, $college, $course); // merge all with realExam

        return $realExam;
    }

    public function retrieveOnlineExamStudents(Request $request)
    {
        $students =  DB::table('online_exams')
        ->join('real_exams', 'online_exams.id', '=', 'real_exams.id')
        ->join('course_lucturers', 'real_exams.course_lucturer_id', '=', 'course_lucturers.id')
        ->join('department_course_parts', 'course_lucturers.department_course_part_id', '=', 'department_course_parts.id')
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

        $enumReplacements =[new EnumReplacement1('gender_name', GenderEnum::class)];
        $students = ProcessDataHelper::enumsConvertIdToName($students, $enumReplacements);
        foreach ($students as $student) {
            // $student['status_name'] = null;
            $student['form_name'] = null;
            // $student['start_datetime'] = null;
            // $student['end_datetime'] = null;
            // $student['answered_questions_count'] = null;
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
            $onlineExamStudent['answered_questions_count'] = ExamHelper::getStudentAnsweredQuestionsCount($request->exam_id, $onlineExamStudent->student_id);
            if($onlineExamStudent->status === StudentOnlineExamStatusEnum::ACTIVE->value){
                $onlineExamStudent['is_started'] = true;
                $onlineExamStudent['is_suspended'] = false;
                $onlineExamStudent['is_finished'] = false;
            }elseif($onlineExamStudent->status === StudentOnlineExamStatusEnum::SUSPENDED->value){
                $onlineExamStudent['is_started'] = true;
                $onlineExamStudent['is_suspended'] = true;
                $onlineExamStudent['is_finished'] = false;
            }elseif($onlineExamStudent->status === StudentOnlineExamStatusEnum::COMPLETE->value){
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
        return $onlineExamStudents;
    }


    public function suspendStudentOnlineExam(Request $request)
    {
       $studentOnlineExam =  StudentOnlineExam
       ::where('online_exam_id', '=', $request->exam_id)
       ->where('student_id', '=', $request->student_id);
       if($studentOnlineExam->status === StudentOnlineExamStatusEnum::ACTIVE->value){
           $studentOnlineExam->update([
               'status' => StudentOnlineExamStatusEnum::SUSPENDED->value
           ]);
       }else{
        // return message: faild
       }
    }

    public function continueStudentOnlineExam(Request $request)
    {
        $studentOnlineExam =  StudentOnlineExam
       ::where('online_exam_id', '=', $request->exam_id)
       ->where('student_id', '=', $request->student_id);
       if($studentOnlineExam->status === StudentOnlineExamStatusEnum::SUSPENDED->value){
        $studentOnlineExam->update([
            'status' => StudentOnlineExamStatusEnum::ACTIVE->value
        ]);
    }else{
     // return message: faild
    }
    }

    public function finishStudentOnlineExam(Request $request)
    {
        $studentOnlineExam =  StudentOnlineExam
        ::where('online_exam_id', '=', $request->exam_id)
        ->where('student_id', '=', $request->student_id);
        if(!$studentOnlineExam->status === StudentOnlineExamStatusEnum::COMPLETE->value){
            $studentOnlineExam->update([
                'status' => StudentOnlineExamStatusEnum::CANCELED->value,
                'end_datetime' => now()
            ]);
        }else{
         // return message: faild
        }
    }

}
