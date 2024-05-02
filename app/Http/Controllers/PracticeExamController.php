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
use App\Models\DepartmentCoursePart;

class PracticeExamController extends Controller
{
    public function addPracticeExam(Request $request)
    {
        if ( ValidateHelper::validateData($request, $this->rules($request))) {
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

    public function deletePractiseExam(Request $request)
    {
        // حذف الاختبارات المعلقة فقط
        return DeleteHelper::deleteModel($request->id);
    }

    public function retrievePracticeExams(Request $request)
    {
        $userId = auth()->user()->id;
        $practiceExams =  DB::table('practice_exams')
            ->join('department_course_parts', 'practice_exams.department_course_part_id', '=', 'department_course_parts.id')
            ->join('department_courses', 'department_course_parts.department_course_id', '=', 'department_courses.id')
            ->join('courses', 'department_courses.course_id', '=', 'courses.id')
            ->join('course_parts', 'department_course_parts.course_part_id', '=', 'course_parts.id')
            ->select(
                'courses.arabic_name as course_name',
                'course_parts.part_id as course_part_name',
                'practice_exams.id',
                'practice_exams.title',
                // practice_exams.datetime,
            )
            ->when($request->status_id === null, function ($query)  {
                return  $query ->selectRaw('practice_exams.status as status_name');
             })

            ->when($request->status_id , function ($query) use ($request) {
                return  $query ->where('practice_exams.status', '=', $request->status_id);
             })
             ->where('practice_exams.department_course_part_id', '=', $request->department_course_part_id)
             ->where('practice_exams.user_id', '=', $userId)
            ->get();
            $enumReplacement = [
                new EnumReplacement('course_part_name', CoursePartsEnum::class),
            ];
            if($request->status_id === null){
                array_push($enumReplacement, new EnumReplacement('status_name', ExamStatusEnum::class));
            }
        $practiceExams = ProcessDataHelper::enumsConvertIdToName($practiceExams, $enumReplacement );
        $practiceExams = self::retrievePractiseExamsResult($practiceExams);

        return ResponseHelper::successWithData($practiceExams);
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

    public function retrievePractiseExamsQuestions(Request $request) // *** stay not complete
    {
        $practiseExamQuestions = [];

        foreach ($practiseExamQuestions as $practiseExamQuestion) {
            $questions = DB::table('practice_exams')
                ->join('practise_exam_questions', 'practice_exams.id', '=', 'practice_exam_questions.practice_exam_id')
                ->join('questions', 'practice_exam_questions.question_id', '=', 'questions.id')
                ->select(
                    'questions.id ',
                    'questions.type as type_name ',
                    'questions.content',
                    'questions.attachment_url',
                    'form_questions.combination_id',
                )
                ->where('practice_exams.id', '=', $request->exam_id)
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
            'id','title', 'duration', 'language as language_name',
            'conduct_method as is_mandatory_question_sequence', 'status as is_complete','department_course_part_id'
        ]);
        $practiseExam = ProcessDataHelper::enumsConvertIdToName($practiseExam, [
            new EnumReplacement('language_name', LanguageEnum::class)
        ]);

        $departmentCoursePart = DepartmentCoursePart::findOrFail($practiseExam->department_course_part_id);

        $coursePart = $departmentCoursePart->course_part()->first(['part_id as course_part_name']);
        $coursePart = ProcessDataHelper::enumsConvertIdToName($coursePart, [
            new EnumReplacement('course_part_name', CoursePartsEnum::class)
        ]);

        $departmentCourse = $departmentCoursePart->department_course()->first(['level as level_name', 'semester as semester_name', 'department_id', 'course_id']);
        $departmentCourse = ProcessDataHelper::enumsConvertIdToName($departmentCourse, [
            new EnumReplacement('level_name', LevelsEnum::class),
            new EnumReplacement('semester_name', SemesterEnum::class)
        ]);

        $department = $departmentCourse->department()->first(['arabic_name as department_name', 'college_id']);

        $college = $department->college()->first(['arabic_name as college_name']);

        $course = $departmentCourse->course()->first(['arabic_name as course_name']);


        $departmentCourse = $departmentCourse->toArray();
        unset($departmentCourse['department_id']);
        unset($departmentCourse['course_id']);

        $department = $department->toArray();
        unset($department['college_id']);

        $practiseExam = $practiseExam->toArray();
        unset($practiseExam['department_course_part_id']);

        $practiseExam = $practiseExam +
            $coursePart->toArray() +
            $departmentCourse +
            $department +
            $college->toArray() +
            $course->toArray();

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
        if (intval($questionType->type) === QuestionTypeEnum::TRUE_FALSE->value) {

            $answerId = ($request->is_true) ? TrueFalseAnswerEnum::TRUE->value : TrueFalseAnswerEnum::FALSE->value;
        } else {
            $answerId =  intval($request->choice_id );
        }

        $practiseExamQuestion = PracticeExamQuestion::where('practice_exam_id', $request->exam_id)
                                                     ->where('question_id', $request->question_id)
                                                     ->first();
        $practiseExamQuestion->update([
            'answer' =>  $answerId,
            'answer_duration' => $request->answer_duration ?? null,
        ]);

        return ResponseHelper::success();
    }

    public function finishPractiseExam(Request $request)
    {
        $practiseExam = PracticeExam::findOrFail($request->id);
        $practiseExam->update([
            'status' => ExamStatusEnum::COMPLETE->value,
            // 'end_datetime' => now(),
        ]);
        return ResponseHelper::success();
    }

    public function suspendPractiseExam(Request $request)
    {
        $practiseExam = PracticeExam::findOrFail($request->id);

        if (intval($practiseExam->status) === ExamStatusEnum::ACTIVE->value) {
            $practiseExam->update([
                'status' => ExamStatusEnum::SUSPENDED->value,
            ]);
            return $practiseExam;
            return ResponseHelper::success();
        } else {
            return abort(404);
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
            'difficulty_level_id' => ['required', new Enum(ExamDifficultyLevelEnum::class)],
            'conduct_method_id' => ['required', new Enum(ExamConductMethodEnum::class)],
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
