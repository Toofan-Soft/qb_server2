<?php

namespace App\Http\Controllers;

use App\Models\Form;
use App\Models\Student;
use App\Models\Employee;
use App\Models\Question;
use App\Models\RealExam;
use App\Enums\LevelsEnum;
use App\Models\Department;
use App\Models\OnlineExam;
use App\Traits\EnumTraits;
use App\Enums\ExamTypeEnum;
use App\Enums\LanguageEnum;
use App\Enums\SemesterEnum;
use App\Helpers\ExamHelper;
use Illuminate\Http\Request;
use App\Enums\ExamStatusEnum;
use App\Models\StudentAnswer;
use App\Enums\CoursePartsEnum;
use App\Enums\QuestionTypeEnum;
use App\Helpers\QuestionHelper;
use App\Helpers\ResponseHelper;
use App\Helpers\EnumReplacement;
use App\Helpers\OnlinExamHelper;
use App\Helpers\EnumReplacement1;
use App\Models\StudentOnlineExam;
use App\Enums\TrueFalseAnswerEnum;
use App\Helpers\ProcessDataHelper;
use Illuminate\Support\Facades\DB;
use App\Enums\ExamConductMethodEnum;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rules\Enum;
use App\Enums\ExamDifficultyLevelEnum;
use Illuminate\Support\Facades\Storage;
use App\Enums\OnlineExamTakingStatusEnum;
use App\Enums\FormConfigurationMethodEnum;
use App\Enums\StudentOnlineExamStatusEnum;

class StudentOnlinExamController extends Controller
{



    public function retrieveOnlineExams(Request $request)
    {
        $student = Student::where('user_id', auth()->user()->id)->first();
        $onlineExams =[];
        if(intval($request->status_id) === OnlineExamTakingStatusEnum::COMPLETE->value){
            $onlineExams = $this->retrieveCompleteStudentOnlineExams($student->id);

        }else{
            $onlineExams = $this->retrieveIncompleteStudentOnlineExams($student->id);
        }
        return ResponseHelper::successWithData($onlineExams);
    }


    public function retrieveOnlineExam(Request $request)
    {
        // تستخدم هذه الدالة لارجاع الاختبارات الغير مكتملة فقط
        $studentonlinExam = StudentOnlineExam::where('online_exam_id',$request->id)->first();
        $realExam = [];

        $isComplete = ( intval($studentonlinExam->status) === StudentOnlineExamStatusEnum::COMPLETE->value)? true : false ;
        if(!$isComplete ){
            $realExam = RealExam::find($studentonlinExam->online_exam_id, ['id','language as language_name' ,
            'datetime', 'duration', 'type as type_name', 'note as special_note' , 'course_lecturer_id' ]);

            $enumReplacement = [
                new EnumReplacement('language_name', LanguageEnum::class),
                new EnumReplacement('type_name', ExamTypeEnum::class),
            ];
            $realExam = ProcessDataHelper::enumsConvertIdToName($realExam, $enumReplacement);
            $jsonData = Storage::disk('local')->get('generalNotes.json');// get notes from json file
            $general_note = json_decode($jsonData, true);
            $realExam['general_note'] =  $general_note;        //// Done

            $realExam = ExamHelper::getRealExamsScore($realExam);
            $onlineExam = OnlineExam::where('id', $realExam->id)->first(['conduct_method as is_mandatory_question_sequense']);
            $onlineExam->is_mandatory_question_sequense = ($onlineExam->is_mandatory_question_sequense === ExamConductMethodEnum::MANDATORY->value) ? true : false;
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

            //*** make unset to : 'department_id', 'course_id', 'college_id', 'course_lecturer_id'
            $departmentCourse = $departmentCourse->toArray();
            unset($departmentCourse['department_id']);
            unset($departmentCourse['course_id']);

            $department = $department->toArray();
            unset($department['college_id']);

            $realExam = $realExam->toArray();
            unset($realExam['course_lecturer_id']);
    }
        $realExam =
            $realExam  +
            $onlineExam ->toArray()+
            $lecturer->toArray() +
            $coursePart->toArray() +
            $departmentCourse  +
            $department +
            $college->toArray() +
            $course->toArray();

        return ResponseHelper::successWithData($realExam);

    }

    public function retrieveOnlineExamQuestions(Request $request)
    {
        $realExam = RealExam::findOrFail($request->id);
        $form = self::selectStudentForm($realExam);
        $formQuestions = [];
        $queationsTypes = $form->real_exam()->real_exam_question_types()->get(['question_type as type_name']);

        foreach ($queationsTypes as $type) {
            $questions = DB::table('forms')
            ->join('form_questions', 'forms.id', '=', 'form_questions.form_id')
            ->join('questions', 'form_questions.question_id', '=', 'questions.id')
            ->select(
                'questions.id ',
                'questions.content',
                'questions.attachment_url',
                'form_questions.combination_id',
            )
                ->where('forms.id', '=', $form->id)
                ->where('questions.type', '=', $type)
                ->get();

            $questions = QuestionHelper::retrieveStudentExamQuestions($questions, $type->type_name);
            // $formQuestions[QuestionTypeEnum::getNameByNumber($type->type_name)] = $questions;
            $formQuestions[ EnumTraits::getNameByNumber($type->type_name, QuestionTypeEnum::class)] = $questions;
        }
        return ResponseHelper::successWithData($formQuestions);

    }

    public function finishOnlineExam(Request $request)
    {
        $student = Student::where('user_id', auth()->user()->id)->first();
        $studentOnlineExam = StudentOnlineExam::where('student_id', $student->id)
            ->where('online_exam_id', $request->id)->firstOrFail();
        if ($studentOnlineExam) {
            StudentOnlineExam::where('student_id', $student->id)
                ->where('online_exam_id', $request->id)
                ->update([
                    'status' => StudentOnlineExamStatusEnum::COMPLETE->value,
                    'end_datetime' => now(),
                ]);
            return ResponseHelper::success();
        } else {
            return abort(404);
        }
    }


    public function saveOnlineExamQuestionAnswer (Request $request){
        // يتم تحديث بيانات استخدام السؤال
        $student = Student::where('user_id', auth()->user()->id)->first();
        $studentAnswer = StudentAnswer::where('student_id',$student->id)
                                        ->where('form_id', $request->form_id)
                                        ->where('question_id', $request->question_id); // need to get() func

        $questionType = Question::findOrFail($request->question_id, ['type']);
        $answerId = null;
        if($questionType->type === QuestionTypeEnum::TRUE_FALSE->value){

            $answerId = ($request->is_true === true) ? TrueFalseAnswerEnum::TRUE->value : TrueFalseAnswerEnum::FALSE->value ;
        }else{
            $answerId =  $request->choice_id ;
        }
        StudentAnswer::create([
            'student_id' => $student->id,
            'form_id' => $request->form_id,
            'question_id' => $request->question_id,
            'answer' => $answerId,
            'answer_duration' => $request->answer_duration ?? null,
        ]);

        return ResponseHelper::success();
    }

    private static function selectStudentForm(RealExam $realExam) // need to test
    {
        $examForms = $realExam->forms()->get(['id'])->toArray();
        $selectedStudentForm = array_rand($examForms);
        return $examForms[$selectedStudentForm];
    }

    // need to test
    private function retrieveCompleteStudentOnlineExams($studentId)
    {
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
            ->where('student_online_exams.student_id', '=', $studentId)
            ->where('student_online_exams.status', '=', StudentOnlineExamStatusEnum::COMPLETE->value)
            ->get();
        $onlineExams = ProcessDataHelper::enumsConvertIdToName($onlineExams, [new EnumReplacement('course_part_name', CoursePartsEnum::class)]);
        foreach ($onlineExams as $onlineExam) {
            $studentResult = $this->retrieveStudentOnlineExamsResult($onlineExam->id, 1, $studentId);
            $onlineExam->score_rate = $studentResult['score_rate'];
            $onlineExam->appreciation = $studentResult['appreciation'];
        }

        return $onlineExams;
    }

    private function retrieveStudentOnlineExamsResult($onlineExamId, $formId, $studentId)
    {
        $questionsAnswers =  DB::table('student_answers')
        ->join('form_questions', 'student_answers.form_id', '=', 'form_questions.form_id')
        ->join('form_questions', 'student_answers.question_id', '=', 'form_questions.question_id')
        ->select(
            'student_answers.answer',
            'form_questions.question_id',
            'form_questions.combination_id'
        )
        ->where('student_answers.student_id', '=', $studentId)
        ->where('student_answers.form_id', '=', $formId)
        ->get();

        $examScores = $this->getRealExamQuestionScore($onlineExamId);

        $StudentScore = 0;
        foreach ($questionsAnswers as $questionAnswer) {
            $question = Question::findOrFail($questionAnswer->questoin_id);
            if(intval($question->type) === QuestionTypeEnum::TRUE_FALSE->value){
                if(ExamHelper::checkTrueFalseQuestionAnswer($question, $questionAnswer->answer)){
                    $StudentScore += $examScores[QuestionTypeEnum::TRUE_FALSE->value];
                }

            }else{
                if(ExamHelper::checkChoicesQuestionAnswer($question, $questionAnswer->answer, $questionAnswer->combination_id )){
                    $StudentScore += $examScores[QuestionTypeEnum::MULTIPLE_CHOICE->value];
                }
            }
        }

        $scoreRate = $StudentScore / $examScores['totalScore'] * 100;
        $appreciation = ExamHelper::getExamResultAppreciation($scoreRate);

        $studentResult = [
            'score_rate' => $scoreRate,
            'appreciation' => $appreciation
        ];

        return $studentResult;
    }

    private function getRealExamQuestionScore($onlineExamId){
        $examScores = [];

        $realExam = RealExam::findOrFail($onlineExamId);

        $realExamQuestionTypes = $realExam->real_exam_question_types()
        ->get(['question_type', 'question_score', 'questions_count']);

        $totalScore = 0;
        foreach ($realExamQuestionTypes as $realExamQuestionType) {
            $examScores[intval($realExamQuestionType->question_type)] = $realExamQuestionType->question_score;
            $totalScore += $realExamQuestionType->questions_count * $realExamQuestionType->question_score;
        }

        $examScores['totalScore'] = $totalScore;
        return $examScores;
    }

    private static function retrieveIncompleteStudentOnlineExams($studentId)
    {
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
            ->where('student_online_exams.student_id', '=', $studentId)
            ->where('student_online_exams.status', '!=', StudentOnlineExamStatusEnum::COMPLETE->value)
            ->get();
        $onlineExams = ProcessDataHelper::enumsConvertIdToName($onlineExams, [new EnumReplacement('course_part_name', CoursePartsEnum::class)]);

        return $onlineExams;
    }

}
