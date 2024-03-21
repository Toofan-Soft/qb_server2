<?php

namespace App\Http\Controllers;

use App\Models\Form;
use App\Models\User;
use App\Models\Employee;
use App\Models\RealExam;
use App\Enums\LevelsEnum;
use App\Models\OnlineExam;
use App\Enums\ExamTypeEnum;
use App\Enums\LanguageEnum;
use App\Enums\SemesterEnum;
use Illuminate\Http\Request;
use App\Enums\ExamStatusEnum;
use App\Helpers\DeleteHelper;
use App\Enums\CoursePartsEnum;
use App\Models\CourseLecturer;
use App\Enums\QuestionTypeEnum;
use App\Enums\RealExamTypeEnum;
use App\Helpers\EnumReplacement;
use App\Helpers\OnlinExamHelper;
use App\Enums\QuestionStatusEnum;
use App\Helpers\EnumReplacement1;
use App\Helpers\ProcessDataHelper;
use Illuminate\Support\Facades\DB;
use App\Enums\ExamDifficultyLevelEnum;
use App\Enums\FormConfigurationMethodEnum;
use App\Helpers\QuestionHelper;

class LecturereOnlinExamController extends Controller
{
    public function addOnlineExam(Request $request)
    {
        $user = User::findOrFail(auth()->user()->id);
        $courseLecturer = CourseLecturer::where('department_course_part_id', $request->department_course_part_id)
        ->where('lecturer_id',$user->employee()->id )
        ->where('academic_year', now()->format('Y'));
        $realExam = $courseLecturer->real_exams()->create([
            'type' => $request->type_id,
            'datetime' => $request->datetime,
            'duration' => $request->duration,
            'language' => $request->language_id,
            'note' => $request->special_note ?? null,
            'difficulty_level' => $request->difficulty_level_id,
            'forms_count' => $request->forms_count,
            'form_configuration_method' => $request->form_configuration_method,
            'form_name_method' => $request->form_name_method,
            'exam_type' => RealExamTypeEnum::ONLINE->value,
        ]);

         $realExam->online_exam()->create([
            'conduct_method' => $request->conduct_method_id,
            'exam_datetime_notification_datetime' => $request->datetime_notification_datetime,
            'result_notification_datetime'  => $request->result_notification_datetime,
            'proctor_id' => $request->proctor_id ?? null,
            'status' => ExamStatusEnum::ACTIVE->value,
        ]);

        foreach ($request->question_types as $question_type ) {
             $realExam->real_exam_question_types()->create([
                'question_type' => $question_type->type_id,  ///// ensure
                'questions_count' => $question_type->questions_count,
                'question_score'  => $question_type->question_score,
            ]);
        }

        if($request->form_configuration_method === FormConfigurationMethodEnum::SIMILAR_FORMS->value){
            $realExam->forms()->create();
        }else {
            foreach ($request->forms_count as $form ) {
                $realExam->forms()->create();
            }
        }

        //////////add Topics of exam

        return $realExam->id;

       // return AddHelper::addModel($request, Topic::class,  $this->rules($request), 'questions', $request->topic_id);
    }

    public function modifyRealExam (Request $request)
    {

        $realExam = RealExam::findOrFail($request->id);
        $realExam->update([
            'type' => $request->type_id ?? $realExam->type ,
            'datetime' => $request->datetime ?? $realExam->datetime ,
            'note' => $request->special_note ?? $realExam->note,
            'form_name_method' => $request->form_name_method ?? $realExam->form_name_method,
        ]);

        $onlinExam = $realExam->online_exam();
        $onlinExam->update([
            'conduct_method' =>  $request->conduct_method_id ??  $onlinExam->conduct_method ,
            'exam_datetime_notification_datetime' => $request->datetime_notification_datetime ?? $onlinExam->exam_datetime_notification_datetime ,
            'result_notification_datetime'  => $request->result_notification_datetime ?? $onlinExam->result_notification_datetime,
            'proctor_id' => $request->proctor_id ?? $onlinExam->proctor_id,
        ]);



    }

    public function deleteOnlineExam (OnlineExam $onlineExam)
    {
        return DeleteHelper::deleteModel($onlineExam);
    }

    public function retrieveOnlineExams(Request $request) ////**** يتم اضافة شرط ان يتم ارجاع الاختبارات التي تنتمي الى المستخدم الحالي
    {
        $onlineExams = [];

        $enumReplacements  =[];
        if ($request->status_id && $request->type_id) {
            $onlineExams =  DB::table('real_exams')
            ->join('online_exams', 'real_exams.id', '=', 'online_exams.id')
            ->join('course_lucturers', 'real_exams.course_lucturer_id', '=', 'course_lucturers.id')
            ->join('department_course_parts', 'course_lucturers.department_course_part_id', '=', 'department_course_parts.id')
            ->select(
             'real_exams.id ','real_exams.datetime','real_exams.forms_count',
             )
            ->where('department_course_parts.id', '=', $request->department_course_part_id)
            ->where('real_exams.type', '=', $request->type_id)
            ->where('online_exams.status', '=', $request->stsatus_id)
            ->get();
        }
        elseif (!$request->status_id && !$request->type_id) {
            $onlineExams =  DB::table('real_exams')
            ->join('online_exams', 'real_exams.id', '=', 'online_exams.id')
            ->join('course_lucturers', 'real_exams.course_lucturer_id', '=', 'course_lucturers.id')
            ->join('department_course_parts', 'course_lucturers.department_course_part_id', '=', 'department_course_parts.id')
            ->select(
             'real_exams.id ','real_exams.datetime', 'real_exams.type as type_name', 'real_exams.forms_count',
             'online_exams.status as status_name',
             )
            ->where('department_course_parts.id', '=', $request->department_course_part_id)
            ->get();
            array_push($enumReplacements,  new EnumReplacement1('type_name', ExamTypeEnum::class));
            array_push($enumReplacements,  new EnumReplacement1('status_name', ExamStatusEnum::class));
        }
        elseif($request->status_id && !$request->type_id)  {
            $onlineExams =  DB::table('real_exams')
            ->join('online_exams', 'real_exams.id', '=', 'online_exams.id')
            ->join('course_lucturers', 'real_exams.course_lucturer_id', '=', 'course_lucturers.id')
            ->join('department_course_parts', 'course_lucturers.department_course_part_id', '=', 'department_course_parts.id')
            ->select(
                'real_exams.id ',
                'real_exams.datetime',
                'real_exams.type as type_name',
                'real_exams.forms_count',
            )
                ->where('department_course_parts.id', '=', $request->department_course_part_id)
                ->where('online_exams.status', '=', $request->stsatus_id)
                ->get();
                array_push($enumReplacements,  new EnumReplacement1('type_name', ExamTypeEnum::class));
            }else{
            $onlineExams =  DB::table('real_exams')
            ->join('online_exams', 'real_exams.id', '=', 'online_exams.id')
            ->join('course_lucturers', 'real_exams.course_lucturer_id', '=', 'course_lucturers.id')
            ->join('department_course_parts', 'course_lucturers.department_course_part_id', '=', 'department_course_parts.id')
            ->select(
             'real_exams.id ','real_exams.datetime',  'real_exams.forms_count',
             'online_exams.status as status_name',
             )
            ->where('department_course_parts.id', '=', $request->department_course_part_id)
            ->where('real_exams.type', '=', $request->type_id)
            ->get();
            array_push($enumReplacements,  new EnumReplacement1('status_name', ExamStatusEnum::class));
        }

        $onlineExams = ProcessDataHelper::enumsConvertIdToName($onlineExams, $enumReplacements);
        return OnlinExamHelper::getExamsScore($onlineExams); // sum score of
    }
    public function retrieveOnlineExamsAndroid(Request $request) ////////** this attribute department_course_part_id can be null
    {

        $onlineExams = [];

        $enumReplacements  =[
            new EnumReplacement1('type_name', ExamTypeEnum::class),
            new EnumReplacement1('status_name', ExamStatusEnum::class),
            new EnumReplacement1('course_part_name', CoursePartsEnum::class),
            new EnumReplacement1('language_name', LanguageEnum::class),
        ];
        if ($request->status_id && $request->type_id) {
            $onlineExams =  DB::table('real_exams')
            ->join('online_exams', 'real_exams.id', '=', 'online_exams.id')
            ->join('course_lucturers', 'real_exams.course_lucturer_id', '=', 'course_lucturers.id')
            ->join('department_course_parts', 'course_lucturers.department_course_part_id', '=', 'department_course_parts.id')
            ->join('department_courses', 'department_course_parts.department_course_id', '=', 'department_courses.id')
            ->join('courses', 'department_courses.course_id', '=', 'courses.id')
            ->join('course_parts', 'department_course_parts.course_part_id', '=', 'course_parts.id')

            ->select(
             'courses.arabic_name as course_name ',
             'course_parts.part_id as course_part_name ',
             'real_exams.id','real_exams.datetime','real_exams.language as language_name','real_exams.type as type_name',
             'online_exams.status as status_name',

             )
            ->where('department_course_parts.id', '=', $request->department_course_part_id)
            ->where('real_exams.type', '=', $request->type_id)
            ->where('online_exams.status', '=', $request->stsatus_id)
            ->get();
        }
        elseif (!$request->status_id && !$request->type_id) {
            $onlineExams =  DB::table('real_exams')
            ->join('online_exams', 'real_exams.id', '=', 'online_exams.id')
            ->join('course_lucturers', 'real_exams.course_lucturer_id', '=', 'course_lucturers.id')
            ->join('department_course_parts', 'course_lucturers.department_course_part_id', '=', 'department_course_parts.id')
            ->join('department_courses', 'department_course_parts.department_course_id', '=', 'department_courses.id')
            ->join('courses', 'department_courses.course_id', '=', 'courses.id')
            ->join('course_parts', 'department_course_parts.course_part_id', '=', 'course_parts.id')

            ->select(
             'courses.arabic_name as course_name ',
             'course_parts.part_id as course_part_name ',
             'real_exams.id','real_exams.datetime','real_exams.language as language_name','real_exams.type as type_name',
             'online_exams.status as status_name',

             )
            ->where('department_course_parts.id', '=', $request->department_course_part_id)
            ->get();
        }
        elseif($request->status_id && !$request->type_id)  {
            $onlineExams =  DB::table('real_exams')
            ->join('online_exams', 'real_exams.id', '=', 'online_exams.id')
            ->join('course_lucturers', 'real_exams.course_lucturer_id', '=', 'course_lucturers.id')
            ->join('department_course_parts', 'course_lucturers.department_course_part_id', '=', 'department_course_parts.id')
            ->join('department_courses', 'department_course_parts.department_course_id', '=', 'department_courses.id')
            ->join('courses', 'department_courses.course_id', '=', 'courses.id')
            ->join('course_parts', 'department_course_parts.course_part_id', '=', 'course_parts.id')

            ->select(
             'courses.arabic_name as course_name ',
             'course_parts.part_id as course_part_name ',
             'real_exams.id','real_exams.datetime','real_exams.language as language_name','real_exams.type as type_name',
             'online_exams.status as status_name',

             )
                ->where('department_course_parts.id', '=', $request->department_course_part_id)
                ->where('online_exams.status', '=', $request->stsatus_id)
                ->get();
            }else{
                $onlineExams =  DB::table('real_exams')
                ->join('online_exams', 'real_exams.id', '=', 'online_exams.id')
                ->join('course_lucturers', 'real_exams.course_lucturer_id', '=', 'course_lucturers.id')
                ->join('department_course_parts', 'course_lucturers.department_course_part_id', '=', 'department_course_parts.id')
                ->join('department_courses', 'department_course_parts.department_course_id', '=', 'department_courses.id')
                ->join('courses', 'department_courses.course_id', '=', 'courses.id')
                ->join('course_parts', 'department_course_parts.course_part_id', '=', 'course_parts.id')

                ->select(
                 'courses.arabic_name as course_name ',
                 'course_parts.part_id as course_part_name ',
                 'real_exams.id','real_exams.datetime','real_exams.language as language_name','real_exams.type as type_name',
                 'online_exams.status as status_name',

                 )
                ->where('department_course_parts.id', '=', $request->department_course_part_id)
            ->where('real_exams.type', '=', $request->type_id)
            ->get();
        }

        $onlineExams = ProcessDataHelper::enumsConvertIdToName($onlineExams, $enumReplacements);

        return $onlineExams;
    }


    public function retrieveOnlineExam(Request $request)
    {
        $realExam = RealExam::findOrFail($request->id)->get(['language as language_name', 'difficulty_level as defficulty_level_name' ,
        'forms_count','form_configuration_method as form_configuration_method_name', 'form_name_method as form_name_method_id' ,
         'datetime', 'duration', 'type as type_id', 'note as special_note']);
         $realExam = ProcessDataHelper::enumsConvertIdToName($realExam, [
            new EnumReplacement1('language_name', LanguageEnum::class),
            new EnumReplacement1('defficulty_level_name', ExamDifficultyLevelEnum::class),
            new EnumReplacement1('form_configuration_method_name', FormConfigurationMethodEnum::class),
         ]);
        $onlinExam = $realExam->online_exam()->get(['conduct_method as conduct_method_id','status as status_name','proctor_id','exam_datetime_notification_datetime as datetime_notification_datetime','result_notification_datetime']);
        $onlinExam = ProcessDataHelper::enumsConvertIdToName($onlinExam, [
            new EnumReplacement1('status_name', ExamStatusEnum::class),
         ]);
        $departmentCoursePart = $realExam->lecturer_course()->department_course_part();
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
        $questionTypes = $realExam->real_exam_question_types()->get(['question_type as type_name','questions_count','question_score']);
        $questionTypes = ProcessDataHelper::enumsConvertIdToName($questionTypes, [
            new EnumReplacement1('type_name', QuestionTypeEnum::class),
         ]);

        array_merge($realExam, $onlinExam, $coursePart,$departmentCourse, $department, $college, $course); // merge all with realExam
        $realExam['questionTypes'] = $questionTypes;

        return $realExam;
    }

    public function retrieveOnlineExamChapters(Request $request)
    {

        $result = DB::table('real_exams')
        ->join('forms', 'real_exams.id', '=', 'forms.real_exam_id')
        ->join('form_questions', 'forms.id', '=', 'form_questions.form_id')
        ->join('questions', 'form_questions.question_id', '=', 'questions.id')
        ->join('topics', 'questions.topic_id', '=', 'topics.id')
        ->join('chapters', 'topics.chapter_id', '=', 'chapters.id')
        ->select('chapters.id', 'chapters.arabic_title as title')
        ->where('real_exams.id', '=', $request->exam_id)
        ->distinct()
        ->get();

        return $result;
    }

    public function retrieveOnlineExamChapterTopics(Request $request)
    {
        $result = DB::table('real_exams')
        ->join('forms', 'real_exams.id', '=', 'forms.real_exam_id')
        ->join('form_questions', 'forms.id', '=', 'form_questions.form_id')
        ->join('questions', 'form_questions.question_id', '=', 'questions.id')
        ->join('topics', 'questions.topic_id', '=', 'topics.id')
        ->select('topics.arabic_title as title')
        ->where('real_exams.id', '=', $request->exam_id)
        ->where('topics.chapter_id', '=', $request->chapter_id)
        ->distinct()
        ->get();
        return $result;
    }


    public function retrieveOnlineExamForms(Request $request)
    {
       $realExam = RealExam::findOrFail($request->exam_id);
       $forms = $realExam->forms()->get(['id']);
       $formsNames = OnlinExamHelper::getExamFormsNames ($realExam->form_name_method, $realExam->forms_count);
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


    public function retrieveOnlineExamFormQuestions(Request $request) //////////////////////*********** More condition needed
    {
        $form = Form::findOrFail($request->form_id);
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

    public function changeOnlineExamStatus(Request $request){
        $onlineExam = OnlineExam::findOrFail($request->id);
        if(!$onlineExam->status === ExamStatusEnum::COMPLETE->value){

            if($onlineExam->status === ExamStatusEnum::SUSPENDED->value){

                $onlineExam->update([
                    'status' => ExamStatusEnum::ACTIVE->value,
                ]);
            }else{

                $onlineExam->update([
                    'status' => ExamStatusEnum::SUSPENDED->value,
                ]);
            }
        }else{
            return response()->json(['error_message' => 'this exam is completed, you cant chande its status'], 400);
        }
        return response()->json(['message' => 'succesful'], 200);
    }
}
