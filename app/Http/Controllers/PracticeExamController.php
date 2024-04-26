<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Question;
use App\Enums\LevelsEnum;
use App\Models\PaperExam;
use App\Enums\LanguageEnum;
use App\Enums\SemesterEnum;
use App\Helpers\ExamHelper;
use App\Models\PracticeExam;
use Illuminate\Http\Request;
use App\Enums\ExamStatusEnum;
use App\Helpers\DeleteHelper;
use App\Enums\CoursePartsEnum;
use App\Enums\QuestionTypeEnum;
use App\Helpers\ResponseHelper;
use App\Helpers\ValidateHelper;
use App\Helpers\EnumReplacement;
use App\Helpers\OnlinExamHelper;
use App\Enums\QuestionStatusEnum;
use App\Helpers\EnumReplacement1;
use App\Enums\TrueFalseAnswerEnum;
use App\Helpers\ProcessDataHelper;
use Illuminate\Support\Facades\DB;
use App\Enums\ExamConductMethodEnum;
use App\Models\PracticeExamQuestion;
use Illuminate\Validation\Rules\Enum;
use App\Enums\AccessibilityStatusEnum;
use App\Enums\ExamDifficultyLevelEnum;
use App\AlgorithmAPI\GeneratePractiseExam;
use App\Enums\FormConfigurationMethodEnum;

class PracticeExamController extends Controller
{
    public function addPracticeExam(Request $request)
    {
        if (ValidateHelper::validateData($request, $this->rules($request))) {
            return  ResponseHelper::clientError(401);
        }

        $algorithmData = $this->getAlgorithmData($request);
        $examQuestions = (new GeneratePractiseExam())->execute($algorithmData);

        if ($examQuestions->data) { // modify to use has function

            $user = User::findOrFail(auth()->user()->id);
            $practiceExam = $user->practise_exams()->create([
                'department_course_part_id' => $request->department_course_part_id,
                'title' => $request->title ?? null,
                'language' => $request->language_id,
                'duration' => $request->duration,
                'difficulty_level' => $request->difficulty_level_id,
                'conduct_method' => $request->conduct_method_id,
                'status' => ExamStatusEnum::ACTIVE->value,
            ]);
            // قاعدة البيانات لا توفر امكانية اضافة انواع الاسئلة وعددهم
            // add type_id, questions_count
            // foreach ($request->question_types as $question_type ) {
            //      $realExam->real_exam_question_types()->create([
            //         'question_type' => $question_type->type_id,  ///// ensure
            //         'questions_count' => $question_type->questions_count,
            //     ]);
            // }

            //////////add Topics of exam

            $examQuestions = $this->getQuestionsChoicesCombinations($examQuestions);
            foreach ($examQuestions as $examQuestion) {
                $practiceExam->practice_exam_question()->create([
                    'question_id' => $examQuestion->question_id,
                    'combination_id' => $examQuestion->combination_id ?? null,
                ]);
            }
            ////////// modify question usage table يفضل ان يتم عمل دالة مشتركة حتى يتم استخدامها في الاختبار الورقي

            return ResponseHelper::successWithData($practiceExam->id);
        } else {
            return ResponseHelper::serverError();
        }
    }

    public function modifyPractiseExam(Request $request)
    {
        $practiseExam = PracticeExam::findOrFail($request->id);
        $practiseExam->update([
            'title' => $request->title ?? null
        ]);
    }

    public function deletePractiseExam(PaperExam $paperExam)
    {
        // حذف الاختبارات المعلقة فقط
        return DeleteHelper::deleteModel($paperExam);
    }

    public function retrievePractiseExams(Request $request)
    {
        $user = User::findOrFail(auth()->user()->id)->first();
        $practiseExams = [];

        if ($request->status_id) {

            if ($request->status_id === ExamStatusEnum::COMPLETE->value) {

                $practiseExams = ExamHelper::retrieveCompletePractiseExams($user->id, $request->department_course_part_id);
            } elseif ($request->status_id === ExamStatusEnum::SUSPENDED->value) {
                $practiseExams = ExamHelper::retrieveSuspendedPractiseExams($user->id, $request->department_course_part_id);
            }
        } else {
            $practiseExams = ExamHelper::retrievePractiseExams($user->id, $request->department_course_part_id);
        }
        return $practiseExams;
    }

    public function retrievePractiseExamsAndroid(Request $request)
    {
        $user = User::findOrFail(auth()->user()->id)->first();
        $practiseExams = [];
        // هذه معقد، يجب ان يتم توحيد البيانات المراد ارجاعها مع الدالة السابق حتى تتسهل
        if ($request->department_course_part_id) {
        }
        return $practiseExams;
    }

    public function retrievePractiseExamsQuestions(Request $request)
    {
        $practiseExamQuestions = [];

        foreach ($practiseExamQuestions as $practiseExamQuestion) {
            $questions = DB::table('practise_exams')
                ->join('practise_exam_questions', 'practise_exams.id', '=', 'practise_exam_questions.practise_exam_id')
                ->join('questions', 'practise_exam_questions.question_id', '=', 'questions.id')
                ->select(
                    'questions.id ',
                    'questions.type as type_name ',
                    'questions.content',
                    'questions.attachment_url',
                    'form_questions.combination_id',
                )
                ->where('practise_exams.id', '=', $request->exam_id)
                ->get();
            // $questions = QuestionHelper::retrieveQuestionsAnswer($questions, $type->type_name);
            // $examQuestions[QuestionTypeEnum::getNameByNumber($type->type_name)] = $questions;
        }
        return $practiseExamQuestions;
    }

    public function retrievePractiseExamsResult(Request $request)
    {
        $practiseExam = PracticeExam::findOrFail($request->id);
        if ($practiseExam->status === ExamStatusEnum::COMPLETE->value) {
            //time spent, question average answer time, appreciation, score rate, correct answer count, incorrect answer count

        }
        return [];
    }

    public function retrievePractiseExam(Request $request)
    {
        $practiseExam = PracticeExam::findOrFail($request->id, [
            // 'datetime'
            'title', 'duration', 'language as language_name',
            'conduct_method as is_mandatory_question_sequence', 'status as is_complete'
        ]);
        $practiseExam = ProcessDataHelper::enumsConvertIdToName($practiseExam, [
            new EnumReplacement('language_name', LanguageEnum::class)
        ]);

        $departmentCoursePart = $practiseExam->department_course_part();

        $coursePart = $departmentCoursePart->course_part(['part_id as course_part_name']);
        $coursePart = ProcessDataHelper::enumsConvertIdToName($coursePart, [
            new EnumReplacement('course_part_name', CoursePartsEnum::class)
        ]);

        $departmentCourse = $departmentCoursePart->department_course()->get(['level as level_name', 'semester as semester_name']);
        $departmentCourse = ProcessDataHelper::enumsConvertIdToName($departmentCourse, [
            new EnumReplacement('level_name', LevelsEnum::class),
            new EnumReplacement('semester_name', SemesterEnum::class)
        ]);

        $department = $departmentCourse->department()->get(['arabic_name as department_name']);

        $college = $department->college()->get(['arabic_name as college_name']);

        $course = $departmentCourse->course()->get(['arabic_name as course_name']);


        array_merge($practiseExam, $coursePart, $departmentCourse, $department, $college, $course); // merge all with realExam

        return ResponseHelper::successWithData($practiseExam);
    }

    public function retrieveEditablePractiseExam(Request $request)
    {
        $practiseExam = PracticeExam::findOrFail($request->id, ['title']);

        return ResponseHelper::successWithData($practiseExam);
    }

    public function savePractiseExamQuestionAnswer(Request $request)
    {

        $questionType = Question::findOrFail($request->question_id, ['type']);
        $answerId = null;
        if ($questionType->type === QuestionTypeEnum::TRUE_FALSE->value) {

            $answerId = ($request->is_true) ? TrueFalseAnswerEnum::TRUE->value : TrueFalseAnswerEnum::FALSE->value;
        } else {
            $answerId =  $request->choice_id;
        }
        $practiseExamQuestion = PracticeExamQuestion::findOrFail($request->exma_id, $request->question_id);
        $practiseExamQuestion->update([
            'answer' => $answerId,
            'answer_duration' => $request->answer_duration ?? null,
        ]);

        return response()->json(['message' => 'succesful'], 200);
    }

    public function finishPractiseExam(Request $request)
    {
        $practiseExam = PracticeExam::findOrFail($request->id);
        $practiseExam->update([
            'status' => ExamStatusEnum::COMPLETE->value,
            // 'end_datetime' => now(),
        ]);
        return response()->json(['message' => 'succesful'], 200);
    }

    public function suspendPractiseExam(Request $request)
    {
        $practiseExam = PracticeExam::findOrFail($request->id);
        if ($practiseExam->status === ExamStatusEnum::ACTIVE->value) {
            $practiseExam->update([
                'status' => ExamStatusEnum::SUSPENDED->value,
            ]);
            return response()->json(['message' => 'succesful'], 200);
        } else {
            // return response()->json(['message' => 'failed'], 200);
        }
    }


    private function getAlgorithmData($request)
    {
        // دالة مشتركة للاختبار الالكتروني والورقي
        $algorithmData = [
            'duration' => $request->duration,
            'language_id' => $request->language_id,
            'difficulty_level_id' => $request->difficulty_level_id,
            // هل امرر قيم افتراضية لبيانات النماذج او هو بيعرف
            // 'forms_count' => 1,
            // 'form_configuration_method_id' => FormConfigurationMethodEnum::DIFFERENT_FORMS->value,
            // 'questions_types' => $request->questions_types,
        ];

        $questionTypesIds = $request->questions_types['type_id']; // التحقق من ان نحصل على مصفوفه 
        $accessabilityStatusIds = [
            AccessibilityStatusEnum::PRACTICE_EXAM->value,
            AccessibilityStatusEnum::PRACTICE_REALEXAM->value,
        ];
        $questions =  DB::table('questions')
            ->join('question_usages', 'questions.id', '=', 'question_usages.question_id')
            ->join('topics', 'questions.topic_id', '=', 'topics.id')
            ->select(
                'questions.id',
                'questions.type',
                'questions.difficulty_level',
                'questions.estimated_answer_time',
                'question_usages.online_exam_last_selection_datetime',
                'question_usages.practice_exam_last_selection_datetime',
                'question_usages.paper_exam_last_selection_datetime',
                'question_usages.online_exam_selection_times_count',
                'question_usages.practice_exam_selection_times_count',
                'question_usages.paper_exam_selection_times_count',
                'topics.id',
                'topics.chapter_id'
            )
            ->where('questions.status', '=', QuestionStatusEnum::ACCEPTED->value)
            ->where('questions.language', '=', $request->language_id)
            ->whereIn('questions.accessability_status', $accessabilityStatusIds)
            ->whereIn('questions.type', $questionTypesIds)
            ->whereIn('topics.id', $request->topicsIds)
            ->get();
        $algorithmData['questions'] = $questions;
        return $algorithmData;
    }

    private function getQuestionsChoicesCombinations($examQuestions)
    {
        // يفضل ان يتم عملها مشترك ليتم استخداما في الاختبار الورقي والتجريبي
        // تقوم هذه الدالة باختيار توزيعة الاختيارات للاسئلة من نوع اختيار من متعدد
        /**
         * steps of function 
         *   اختيار الاسئلة التي نوعها اختيار من متعدد
         *   اختيار احد التوزيعات التي يمتلكها السؤال بشكل عشوائي
         *   يتم اضافة رقم التوزيعة المختارة الي السؤال 
         */
        return $examQuestions;
    }



    public function rules(Request $request): array
    {
        $rules = [
            'title' => 'nullable|string',
            'language_id' => ['required', new Enum(LanguageEnum::class)], // Assuming LanguageEnum holds valid values
            'duration' => 'required|integer',
            'difficulty_level_id' => ['required', ExamDifficultyLevelEnum::class], // Assuming ExamDifficultyLevelEnum holds valid values
            'conduct_method_id' => ['required', ExamConductMethodEnum::class], // Assuming ExamConductMethodEnum holds valid values
            //'status' => new Enum(ExamStatusEnum::class), // Assuming ExamStatusEnum holds valid values
            'department_course_part_id' => 'required|exists:department_course_parts,id',
            //'user_id' => 'required|uuid',
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
