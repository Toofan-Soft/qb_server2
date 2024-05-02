<?php

namespace App\Helpers;

use Traversable;
use App\Models\Form;
use App\Models\Student;
use App\Models\RealExam;
use App\Enums\LevelsEnum;
use App\Enums\ExamTypeEnum;
use App\Enums\LanguageEnum;
use App\Enums\SemesterEnum;
use Illuminate\Http\Request;
use App\Enums\ExamStatusEnum;
use App\Models\StudentAnswer;
use App\Enums\CoursePartsEnum;
use App\Enums\QuestionTypeEnum;
use App\Enums\RealExamTypeEnum;
use App\Helpers\QuestionHelper;
use App\Helpers\EnumReplacement1;
use App\Models\TrueFalseQuestion;
use Illuminate\Http\UploadedFile;
use App\Helpers\ProcessDataHelper;
use Illuminate\Support\Facades\DB;
use App\Enums\ExamConductMethodEnum;
use App\Models\RealExamQuestionType;
use Illuminate\Support\Facades\Storage;
use App\Models\QuestionChoiceCombination;
use App\Enums\FormConfigurationMethodEnum;
use App\Enums\StudentOnlineExamStatusEnum;
use Illuminate\Database\Eloquent\Collection;
use stdClass;

class ExamHelper
{

    /**
     * delete real (paper, online) exam by id
     */
    public static function deleteRealExam($realExamId)
    {

        $realExam = RealExam::findOrFail($realExamId);
        try {
            $realExam->real_exam_question_types()->delete();
            $readExamForms = $realExam->forms();
            foreach ($readExamForms as $readExamForm) {
                $readExamForm->form_questions()->delete();
            }
            $realExam->forms()->delete();
            if ($realExam->exam_type === RealExamTypeEnum::PAPER->value) {

                $realExam->paper_exam()->delete();
            } else {
                $realExam->online_exam()->delete();
            }
            $realExam->delete();
            return ResponseHelper::success();
        } catch (\Exception $e) {
            return ResponseHelper::serverError('An error occurred while deleting models.');
        }
    }
    /**
     * add total score of each exam.
     * $realExams: list of real exam
     */

    public static function getRealExamsScore($realExams)
    {
        // Check if $realExams is an array or a single object
        $isArray = is_array($realExams) || $realExams instanceof Traversable;

        $realExamsToProcess = $isArray ? $realExams : [$realExams];

        $processedRealExams = [];

        foreach ($realExamsToProcess as $realExam) {
            if (is_array($realExam)) {
                if (isset($realExam['id'])) {
                    $temp = RealExam::findOrFail($realExam['id']);
                    $realExamQuestionTypes = $temp->real_exam_question_types()->get(['question_count', 'question_score']);
                    $score = 0;
                    foreach ($realExamQuestionTypes as $realExamQuestionType) {
                        $score += $realExamQuestionType->question_count * $realExamQuestionType->question_score;
                    }
                    $realExam['score'] = $score;
                    $processedRealExams[] = $realExam;
                }
            } else {
                $temp = RealExam::findOrFail($realExam->id);
                $realExamQuestionTypes = $temp->real_exam_question_types()->get(['question_count', 'question_score']);
                // $realExamQuestionTypes = $realExam->real_exam_question_types()->get(['question_count', 'question_score']);
                $score = 0;
                foreach ($realExamQuestionTypes as $realExamQuestionType) {
                    $score += $realExamQuestionType->question_count * $realExamQuestionType->question_score;
                }
                $realExam->score = $score;
                $processedRealExams[] = $realExam;
            }
        }
        // If $realExams was a single object, return the first item in $processedRealExams
        return $isArray ? $processedRealExams : $processedRealExams[0];
    }

    // public static function getRealExamsScore($realExams) // recieve object has multiple array data
    // {
    //     foreach ($realExams as $realExam ) {
    //         $temp = RealExam::findOrFail($realExam['id']);
    //         $realExamQuestionTypes = $temp->real_exam_question_types()->get(['question_count', 'question_score']);

    //         $score = 0;
    //         foreach ($realExamQuestionTypes as $realExamQuestionType) {
    //             $score += $realExamQuestionType->question_count *  $realExamQuestionType->question_score;
    //         }

    //         $realExam['score'] = $score;
    //         // return $realExam;
    //         $score = 0;
    //     }

    //     return $realExams;
    // }

    // public static function getRealExamScore1($realExam) // recieve single array of data , not object has multiple
    // {
    //         $temp = RealExam::findOrFail($realExam['id']);
    //         $realExamQuestionTypes = $temp->real_exam_question_types()->get(['question_count', 'question_score']);

    //         $score = 0;
    //         foreach ($realExamQuestionTypes as $realExamQuestionType) {
    //             $score += $realExamQuestionType->question_count *  $realExamQuestionType->question_score;
    //         }
    //         $realExam['score'] = $score;

    //     return $realExam;
    // }

    public static function getStudentForm($realExam)
    {
        // يتم عمل دالة تختار لي رقم النموذج المناسب لطالب
        $formId = 0;
        $form = Form::findOrFail($formId);
        return $form;
    }

    /**
     * return froms nams: [name1, name2, name3,......]
     */
    private static function getRealExamFormsNames($form_name_method, $forms_count)
    {

        ////
        return [];
    }
    public static function getStudentAnsweredQuestionsCount($onlineExamId, $studentId)
    {

        $studentFormId = 1; // يتم عمل دالة لمعرفة رقم النموذج حق الطالب، او جعل هذه الدالة تستقبل رقم النموذج بدل رقم الاختبار
        $questionsCount = StudentAnswer::where('form_id', '=', $studentFormId)
        ->where('student_id', '=', $studentId)->count();

        return $questionsCount;
    }

    public static function retrieveCompleteStudentOnlineExams(Student $student)
    {
        // تتعدل وترجع تستقبل رقم الطالب
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
                'real_exams.id',
                'real_exams.datetime',
            )
            ->where('student_online_exams.student_id', '=', $student->id)
            ->where('student_online_exams.status', '=', StudentOnlineExamStatusEnum::COMPLETE->value)
            ->get();
        $onlineExams = ProcessDataHelper::enumsConvertIdToName($onlineExams, [new EnumReplacement('course_part_name', CoursePartsEnum::class)]);
        $onlineExams = self::retrieveStudentOnlineExamsResult($onlineExams);
        return $onlineExams;
    }

    public static function retrieveIncompleteStudentOnlineExams(Student $student)
    {
                // تتعدل وترجع تستقبل رقم الطالب
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
                'real_exams.id',
                'real_exams.datetime',
            )
            ->where('student_online_exams.student_id', '=', $student->id)
            ->where('student_online_exams.status', '!=', StudentOnlineExamStatusEnum::COMPLETE->value)
            ->get();
        $onlineExams = ProcessDataHelper::enumsConvertIdToName($onlineExams, [new EnumReplacement('course_part_name', CoursePartsEnum::class)]);

        return $onlineExams;
    }


    private static function retrieveStudentOnlineExamsResult($onlineExams)
    {

        // حساب المعدل والتقدير لكل اختبار
        return $onlineExams;
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

        return ResponseHelper::successWithData($realExamChapters);
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
       return ResponseHelper::successWithData($realExamChapterTopics);
    }

    /**
     * return forms: [[id, name1], [id, name2], ....].
     */
    public static function retrieveRealExamForms($realExamId)
    {
        $realExam = RealExam::findOrFail($realExamId);
        $forms = $realExam->forms()->get(['id']);
        $formsNames = self::getRealExamFormsNames($realExam->form_name_method, $realExam->forms_count);
        if (intval($realExam->form_configuration_methode) === FormConfigurationMethodEnum::DIFFERENT_FORMS->value) {
            $i = 0;
            foreach ($forms as $form) {
                $form['name'] = $formsNames[$i++];
            }
        } else {
            $formId = $forms->id;
            $forms = [];
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
        $realExam = RealExam::where('id', $form->real_exam_id)->first();
        $queationsTypes =  $realExam->real_exam_question_types()->get(['question_type as type_name']);

        foreach ($queationsTypes as $type) {
            $questions = DB::table('forms')
                ->join('form_questions', 'forms.id', '=', 'form_questions.form_id')
                ->join('questions', 'form_questions.question_id', '=', 'questions.id')
                ->join('topics', 'questions.topic_id', '=', 'topics.id')
                ->join('chapters', 'topics.chapter_id', '=', 'chapters.id')
                ->select(
                    'chapters.arabic_title as chapter_title',
                    'topics.arabic_title as topic_title',
                    'questions.id',
                    'questions.content',
                    'questions.attachment',
                    'form_questions.combination_id',
                )
                ->where('forms.id', '=', $form->id)
                ->where('questions.type', '=', $type->type_name)
                ->get();

            $questions = QuestionHelper::retrieveQuestionsAnswer($questions, $type->type_name);
            $formQuestions[QuestionTypeEnum::getNameByNumber($type->type_name)] = $questions;
        }
        return $formQuestions;
    }


}
