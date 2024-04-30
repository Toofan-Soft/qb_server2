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
            $onlineExams = ExamHelper::retrieveCompleteStudentOnlineExams($student);

        }else{
            $onlineExams = ExamHelper::retrieveIncompleteStudentOnlineExams($student);
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
            'datetime', 'duration', 'type as type_name', 'note as special_note' , 'course_lecturer_id']);

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


        //    array_merge($realExam->toArray(),
        //     $onlineExam->toArray(),
        //     $lecturer->toArray(),
        //      $coursePart->toArray(),
        //      $departmentCourse->toArray(),
        //       $department->toArray(),
        //       $college->toArray(),
        //       $course->toArray()); // merge all with realExam

        //    array_merge($realExam, $onlineExam, $lecturer, $coursePart,$departmentCourse, $department, $college, $course); // merge all with realExam
        $mergedData = [
            'student_online_exam' => $studentonlinExam,
            'real_exam' => $realExam,
            'is_complete' => $isComplete,
            'online_exam' => $onlineExam,
            'course_lecturer' => $courselecturer,
            'lecturer' => $lecturer,
            'department_course_part' => $departmentCoursePart,
            'course_part' => $coursePart,
            'department_course' => $departmentCourse,
            'department' => $department,
            'college' => $college,
            'course' => $course,
        ];

        }
        return ResponseHelper::successWithData($mergedData);

    }

    public function retrieveOnlineExamQuestions(Request $request)
    {
        $realExam = RealExam::findOrFail($request->id);
        $form = ExamHelper::getStudentForm($realExam);
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
        return ResponseHelper::successWithData($formQuestions);

    }

    public function finishOnlineExam (Request $request){
        $student = Student::where('user_id', auth()->user()->id)->first();

        $studentonlinExam = StudentOnlineExam::where('student_id', $student->id )
                                              ->where('online_exam_id', $request->id)->firstOrFail();
        $studentonlinExam->update([
            'status' => StudentOnlineExamStatusEnum::COMPLETE->value,
            'end_datetime' => now(),
        ]);
        return ResponseHelper::success();
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


}
