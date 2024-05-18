<?php

namespace App\Helpers;

use App\Models\Form;
use App\Models\Student;
use App\Models\RealExam;
use App\Enums\LevelsEnum;
use App\Enums\ExamTypeEnum;
use App\Enums\LanguageEnum;
use App\Enums\SemesterEnum;
use Illuminate\Http\Request;
use App\Enums\CoursePartsEnum;
use App\Enums\QuestionTypeEnum;
use App\Helpers\EnumReplacement1;
use Illuminate\Http\UploadedFile;
use App\Helpers\ProcessDataHelper;
use Illuminate\Support\Facades\DB;
use App\Enums\ExamConductMethodEnum;
use Illuminate\Support\Facades\Storage;
use App\Enums\StudentOnlineExamStatusEnum;

class OnlinExamHelper
{
    /**
     * sumation the score of exams .
     */
    public static function getExamsScore($data){

        foreach ($data as $onlineExam) {
            $realExam = RealExam::find($onlineExam->id);
            $realExamQuestionTypes = $realExam->real_exam_question_types()->get(['questions_count', ' question_score']);
            $score = 0;
            foreach ($realExamQuestionTypes as $realExamQuestionType) {
                $score += $realExamQuestionType->questions_count * $realExamQuestionType->question_score;
            }
            $onlineExam['score'] = $score;
            $score = 0;
        }

        return $data;
    }

    public static function getExamFormsNames($form_name_method, $forms_count){

        ////
        return [];
    }

    public static function retrieveCompleteStudentOnlineExams(Student $student){

        $onlineExams =  DB::table('student_online_exams')
                ->join('online_exams', 'student_online_exams.online_exam_id', '=', 'online_exams.id')
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
                ->where('student_online_exams.student_id', '=', $student->id)
                ->where('student_online_exams.status', '=', StudentOnlineExamStatusEnum::COMPLETE->value)
            ->get();
            $onlineExams = ProcessDataHelper::enumsConvertIdToName($onlineExams ,[new EnumReplacement('course_part_name', CoursePartsEnum::class)] );
            $onlineExams = self::retrieveStudentOnlineExamsResult($onlineExams);
        return $onlineExams;
    }
    public static function retrieveIncompleteStudentOnlineExams(Student $student){
        $onlineExams =  DB::table('student_online_exams')
                ->join('online_exams', 'student_online_exams.online_exam_id', '=', 'online_exams.id')
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
                ->where('student_online_exams.student_id', '=', $student->id)
                ->where('student_online_exams.status','!=', StudentOnlineExamStatusEnum::COMPLETE->value)
            ->get();
        $onlineExams = ProcessDataHelper::enumsConvertIdToName($onlineExams ,[new EnumReplacement('course_part_name', CoursePartsEnum::class)] );

        return $onlineExams;
    }


    private static function retrieveStudentOnlineExamsResult($onlineExams){

        // حساب المعدل والتقدير لكل اختبار
        return $onlineExams;

    }


    public static function getStudentForm($realExam){
        // يتم عمل دالة تختار لي رقم النموذج المناسب لطالب
        $formId = 0;
        $form = Form::findOrFail($formId);
        return $form;

    }


}
