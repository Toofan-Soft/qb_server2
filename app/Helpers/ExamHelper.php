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
use App\Enums\ExamStatusEnum;
use App\Enums\CoursePartsEnum;
use App\Enums\QuestionTypeEnum;
use App\Helpers\QuestionHelper;
use App\Helpers\EnumReplacement1;
use Illuminate\Http\UploadedFile;
use App\Helpers\ProcessDataHelper;
use Illuminate\Support\Facades\DB;
use App\Enums\ExamConductMethodEnum;
use Illuminate\Support\Facades\Storage;
use App\Enums\FormConfigurationMethodEnum;
use App\Enums\RealExamTypeEnum;
use App\Enums\StudentOnlineExamStatusEnum;
use App\Models\RealExamQuestionType;

class ExamHelper
{



    public static function deleteRealExam($realExamId ){

        $realExam = RealExam::findOrFail($realExamId);
        try {
            $realExam->real_exam_question_types()->delete();
            $readExamForms = $realExam->forms();
            foreach ($readExamForms as $readExamForm) {
                $readExamForm->form_questions()->delete();
            }
            $realExam->forms()->delete();
            if($realExam->exam_type === RealExamTypeEnum::PAPER->value){

                $realExam->paper_exam()->delete();
            }else{
                $realExam->online_exam()->delete();
            }
            $realExam->delete();
            return ResponseHelper::success();
        } catch (\Exception $e) {
            return ResponseHelper::serverError('An error occurred while deleting models.');
        }

    }
    /**
     * sumation the score of exams .
     */
    public static function getRealExamsScore($readExams){

        foreach ($readExams as $readExam) {
            $readExam = RealExam::find($readExam->id);
            $realExamQuestionTypes = $readExam->real_exam_question_types()->get(['questions_count', ' question_score']);
            $score = 0;
            foreach ($realExamQuestionTypes as $realExamQuestionType) {
                $score += $realExamQuestionType->questions_count * $realExamQuestionType->question_score;
            }
            $readExam['score'] = $score;
            $score = 0;
        }

        return $readExams;
    }

    private static function getRealExamFormsNames($form_name_method, $forms_count){

        ////
        return [];
    }
    public static function getStudentAnsweredQuestionsCount($onlineExamId, $studentId){
//
        return 0;
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


    public static function getStudentForm($realExam){
        // يتم عمل دالة تختار لي رقم النموذج المناسب لطالب
        $formId = 0;
        $form = Form::findOrFail($formId);
        return $form;

    }


    public static function retrieveRealExamChapters($realExamId)
    {

        $realExamChapters = DB::table('real_exams')
        ->join('forms', 'real_exams.id', '=', 'forms.real_exam_id')
        ->join('form_questions', 'forms.id', '=', 'form_questions.form_id')
        ->join('questions', 'form_questions.question_id', '=', 'questions.id')
        ->join('topics', 'questions.topic_id', '=', 'topics.id')
        ->join('chapters', 'topics.chapter_id', '=', 'chapters.id')
        ->select('chapters.id', 'chapters.arabic_title as title')
        ->where('real_exams.id', '=', $realExamId)
        ->distinct()
        ->get();

        return $realExamChapters;
    }

    public static function retrieveRealExamChapterTopics($realExamId, $chapterId)
    {
        $realExamChapterTopics = DB::table('real_exams')
        ->join('forms', 'real_exams.id', '=', 'forms.real_exam_id')
        ->join('form_questions', 'forms.id', '=', 'form_questions.form_id')
        ->join('questions', 'form_questions.question_id', '=', 'questions.id')
        ->join('topics', 'questions.topic_id', '=', 'topics.id')
        ->select('topics.arabic_title as title')
        ->where('real_exams.id', '=', $realExamId)
        ->where('topics.chapter_id', '=', $chapterId)
        ->distinct()
        ->get();
        return $realExamChapterTopics;
    }

    public static function retrieveRealExamForms($realExamId)
    {
       $realExam = RealExam::findOrFail($realExamId);
       $forms = $realExam->forms()->get(['id']);
       $formsNames = self::getRealExamFormsNames($realExam->form_name_method, $realExam->forms_count);
       if($realExam->form_configuration_methode === FormConfigurationMethodEnum::DIFFERENT_FORMS->value){
        $i = 0;
        foreach ($forms as $form) {
            $form['name'] = $formsNames[$i++];
        }
       }else {
        $formId = $forms->id;
        foreach ($formsNames as $formName) {
            $forms['id'] = $formId;
            $forms['name'] = $formName;
        }

       }
        return $forms;
    }

    public static function retrieveRealExamFormQuestions($formId) //////////////////////*********** More condition needed
    {
        $form = Form::findOrFail($formId);
        $formQuestions = [];
        $queationsTypes = $form->real_exam()->real_exam_question_types()->get(['question_type as type_name']);

        foreach ($queationsTypes as $type) {
            $questions = DB::table('forms')
            ->join('form_questions', 'forms.id', '=', 'form_questions.form_id')
            ->join('questions', 'form_questions.question_id', '=', 'questions.id')
            ->join('topics', 'questions.topic_id', '=', 'topics.id')
            ->join('chapters', 'topics.chapter_id', '=', 'chapters.id')
            ->select(
                'chapters.arabic_title as chapter_title',
                'topics.arabic_title as topic_title',
                'questions.id ',
                'questions.content',
                'questions.attachment_url',
                'form_questions.combination_id',

            )
                ->where('forms.id', '=', $form->id)
                ->where('questions.type', '=', $type)
                ->get();

            $questions = QuestionHelper::retrieveQuestionsAnswer($questions, $type->type_name);
            $formQuestions[QuestionTypeEnum::getNameByNumber($type->type_name)] = $questions;
        }
        return $formQuestions;
    }

    //////////////////// special for practise exam
    private static function retrievePractiseExamsResult($practiseExams){

        // حساب المعدل والتقدير لكل اختبار
        // appreciation, score rate
        // يجب ان يتم اولا فحص اذا كان الاختبار حالته مكتملة، يتم ارجاع له نتيجة فقط
        return $practiseExams;

    }

    public static function retrieveCompletePractiseExams($userId, $departmentCoursePartId){

        $practiseExams =  DB::table('practise_exams')
                ->join('department_course_parts', 'practise_exams.department_course_part_id', '=', 'department_course_parts.id')
                ->join('department_courses', 'department_course_parts.department_course_id', '=', 'department_courses.id')
                ->join('courses', 'department_courses.course_id', '=', 'courses.id')
                ->join('course_parts', 'department_course_parts.course_part_id', '=', 'course_parts.id')
                ->select(
                 'courses.arabic_name as course_name ',
                 'course_parts.part_id as course_part_name ',
                 'practise_exams.id','practise_exams.title'
                 // practise_exams.datetime,
                 )
                ->where('practise_exams.department_course_part_id', '=', $departmentCoursePartId)
                ->where('practise_exams.id', '=', $userId)
                ->where('practise_exams.status', '=', ExamStatusEnum::COMPLETE->value)
            ->get();
            $practiseExams = ProcessDataHelper::enumsConvertIdToName($practiseExams ,[new EnumReplacement1('course_part_name', CoursePartsEnum::class)] );
            $practiseExams = self::retrievePractiseExamsResult($practiseExams);
        return $practiseExams;
    }

    public static function retrieveSuspendedPractiseExams($userId, $departmentCoursePartId){

        $practiseExams =  DB::table('practise_exams')
                ->join('department_course_parts', 'practise_exams.department_course_part_id', '=', 'department_course_parts.id')
                ->join('department_courses', 'department_course_parts.department_course_id', '=', 'department_courses.id')
                ->join('courses', 'department_courses.course_id', '=', 'courses.id')
                ->join('course_parts', 'department_course_parts.course_part_id', '=', 'course_parts.id')
                ->select(
                 'courses.arabic_name as course_name ',
                 'course_parts.part_id as course_part_name ',
                 'practise_exams.id','practise_exams.title'
                 // practise_exams.datetime,
                 )
                ->where('practise_exams.department_course_part_id', '=', $departmentCoursePartId)
                ->where('practise_exams.id', '=', $userId)
                ->where('practise_exams.status', '=', ExamStatusEnum::SUSPENDED->value)
            ->get();
            $practiseExams = ProcessDataHelper::enumsConvertIdToName($practiseExams ,[new EnumReplacement1('course_part_name', CoursePartsEnum::class)] );
            $practiseExams = self::retrievePractiseExamsResult($practiseExams);
        return $practiseExams;
    }

    public static function retrievePractiseExams($userId, $departmentCoursePartId){

        $practiseExams =  DB::table('practise_exams')
                ->join('department_course_parts', 'practise_exams.department_course_part_id', '=', 'department_course_parts.id')
                ->join('department_courses', 'department_course_parts.department_course_id', '=', 'department_courses.id')
                ->join('courses', 'department_courses.course_id', '=', 'courses.id')
                ->join('course_parts', 'department_course_parts.course_part_id', '=', 'course_parts.id')
                ->select(
                 'courses.arabic_name as course_name ',
                 'course_parts.part_id as course_part_name ',
                 'practise_exams.id','practise_exams.title', 'practise_exams.status as status_name'
                 // practise_exams.datetime,
                 )
                ->where('practise_exams.department_course_part_id', '=', $departmentCoursePartId)
                ->where('practise_exams.id', '=', $userId)
            ->get();
            $practiseExams = ProcessDataHelper::enumsConvertIdToName($practiseExams ,[
                new EnumReplacement1('course_part_name', CoursePartsEnum::class),
                new EnumReplacement1('status_name', ExamStatusEnum::class),

                ] );
            $practiseExams = self::retrievePractiseExamsResult($practiseExams);
        return $practiseExams;
    }



}
