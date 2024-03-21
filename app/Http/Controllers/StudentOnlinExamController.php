<?php

namespace App\Http\Controllers;

use App\Models\Form;
use App\Models\Student;
use App\Models\Question;
use App\Models\RealExam;
use App\Enums\LevelsEnum;
use App\Models\OnlineExam;
use App\Enums\ExamTypeEnum;
use App\Enums\LanguageEnum;
use App\Enums\SemesterEnum;
use Illuminate\Http\Request;
use App\Enums\ExamStatusEnum;
use App\Models\StudentAnswer;
use App\Enums\CoursePartsEnum;
use App\Enums\QuestionTypeEnum;
use App\Helpers\QuestionHelper;
use App\Helpers\OnlinExamHelper;
use App\Helpers\EnumReplacement1;
use App\Models\StudentOnlineExam;
use App\Enums\TrueFalseAnswerEnum;
use App\Helpers\ProcessDataHelper;
use Illuminate\Support\Facades\DB;
use App\Enums\ExamConductMethodEnum;
use App\Http\Controllers\Controller;
use App\Enums\ExamDifficultyLevelEnum;
use App\Enums\OnlineExamTakingStatusEnum;
use App\Enums\FormConfigurationMethodEnum;
use App\Enums\StudentOnlineExamStatusEnum;

class StudentOnlinExamController extends Controller
{
    public function retrieveOnlineExams(Request $request) ////**** يتم اضافة شرط ان يتم ارجاع الاختبارات التي تنتمي الى المستخدم الحالي
    {
        $student = Student::where('user_id', auth()->user()->id)->first();
        $onlineExams =[];
        if($request->status_id === OnlineExamTakingStatusEnum::COMPLETE->value){

            $onlineExams = OnlinExamHelper::retrieveCompleteStudentOnlineExams($student);
        }else{
            $onlineExams = OnlinExamHelper::retrieveIncompleteStudentOnlineExams($student);
        }
        return $onlineExams;
    }


    public function retrieveOnlineExam(Request $request)
    {
        $studentonlinExam = StudentOnlineExam::find($request->id);
        if(!$studentonlinExam->status === StudentOnlineExamStatusEnum::COMPLETE->value ){
            $realExam = RealExam::findOrFail($studentonlinExam->onlin_exam_id)->get(['language as language_name' ,
            'datetime', 'duration', 'type as type_name', 'note as special_note']);
            $realExam = ProcessDataHelper::enumsConvertIdToName($realExam, [
               new EnumReplacement1('language_name', LanguageEnum::class),
               new EnumReplacement1('type_name', ExamTypeEnum::class),
            ]);
            // $realExam['general_note'] = getGeneralNotes();        //// need add   general_note from json file

            $realExam = OnlinExamHelper::getExamsScore($realExam);
           $onlineExam = $realExam->online_exam()->get(['conduct_method as is_mandatory_question_sequense']);
           $onlineExam = ($onlineExam->is_mandatory_question_sequense === ExamConductMethodEnum::MANDATORY->value) ? true : false;
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

           array_merge($realExam, $onlineExam, $lecturer, $coursePart,$departmentCourse, $department, $college, $course); // merge all with realExam
        }
        return $realExam;
    }

    public function retrieveOnlineExamQuestions(Request $request)
    {
        $realExam = RealExam::findOrFail($request->id);
        $form = OnlinExamHelper::getStudentForm($realExam);
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
            $formQuestions[QuestionTypeEnum::getNameByNumber($type->type_name)] = $questions;
        }
        return $formQuestions;
    }

    public function finishOnlineExam (Request $request){
        $student = Student::where('user_id', auth()->user()->id)->first();
        $studentonlinExam = StudentOnlineExam::find([$request->id, $student->id]);
        $studentonlinExam->update([
            'status' => StudentOnlineExamStatusEnum::COMPLETE->value,
            'end_datetime' => now(),
        ]);
        return response()->json(['message' => 'succesful'], 200);
    }


    public function saveOnlineExamQuestionAnswer (Request $request){
        $student = Student::where('user_id', auth()->user()->id)->first();
        $studentAnswer = StudentAnswer::where('student_id',$student->id)->where('form_id', $request->form_id)->where('question_id', $request->question_id);
        $questionType = Question::findOrFail($request->question_id, ['type']);
        $answerId = null;
        if($questionType->type === QuestionTypeEnum::TRUE_FALSE->value){

            $answerId = ($request->is_true) ? TrueFalseAnswerEnum::TRUE->value : TrueFalseAnswerEnum::FALSE->value ;
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

        return response()->json(['message' => 'succesful'], 200);
    }
}
