<?php

namespace App\Http\Controllers;

use App\Models\Form;
use App\Models\User;
use App\Models\Topic;
use App\Models\Employee;
use App\Models\RealExam;
use App\Enums\LevelsEnum;
use App\Helpers\AddHelper;
use App\Models\OnlineExam;
use App\Enums\ExamTypeEnum;
use App\Enums\LanguageEnum;
use App\Enums\SemesterEnum;
use App\Helpers\ExamHelper;
use Illuminate\Http\Request;
use App\Enums\ExamStatusEnum;
use App\Helpers\DeleteHelper;
use App\Enums\CoursePartsEnum;
use App\Models\CourseLecturer;
use App\Enums\QuestionTypeEnum;
use App\Enums\RealExamTypeEnum;
use App\Helpers\DatetimeHelper;
use App\Helpers\QuestionHelper;
use App\Helpers\ResponseHelper;
use App\Helpers\ValidateHelper;
use App\Helpers\EnumReplacement;
use App\Helpers\OnlinExamHelper;
use App\Enums\FormNameMethodEnum;
use App\Enums\QuestionStatusEnum;
use App\Helpers\EnumReplacement1;
use App\AlgorithmAPI\GenerateExam;
use App\Helpers\ColumnReplacement;
use App\Helpers\ProcessDataHelper;
use Illuminate\Support\Facades\DB;
use App\Enums\ExamConductMethodEnum;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rules\Enum;
use App\Enums\AccessibilityStatusEnum;
use App\Enums\ExamDifficultyLevelEnum;
use App\Enums\FormConfigurationMethodEnum;
use App\Models\Question;

class LecturerOnlineExamController extends Controller
{
    public function addOnlineExam(Request $request)
    {

        if ( ValidateHelper::validateData($request, $this->rules($request))) {
            return ResponseHelper::clientError(401);
        }

        $algorithmData = $this->getAlgorithmData($request);
        $examFormsQuestions = (new GenerateExam())->execute($algorithmData);

        if ($examFormsQuestions->data) { // modify to use has function

            $user = User::findOrFail(auth()->user()->id);

        $employee = Employee::where('user_id',  $user->id )->first();
        $courseLecturer = CourseLecturer::where('department_course_part_id', '=', $request->department_course_part_id)
            ->where('lecturer_id', $employee->id)
            // // ->where('academic_year', now()->format('Y'))
            ->first();


        $realExam = $courseLecturer->real_exams()->create([
        // $realExam = RealExam::create([
            'type' => $request->type_id,
            'datetime' => $request->datetime,
            'duration' => $request->duration,
            'language' => $request->language_id,
            'note' => $request->special_note ?? null,
            'difficulty_level' => $request->difficulty_level_id,
            'forms_count' => $request->forms_count,
            'form_configuration_method' => $request->form_configuration_method_id,
            'form_name_method' => $request->form_name_method_id,
            'exam_type' => RealExamTypeEnum::ONLINE->value,
            // 'course_lecturer_id' => $courseLecturer->id,
        ]);

            OnlineExam::create([
                'conduct_method' => $request->conduct_method_id,
                'exam_datetime_notification_datetime' => $request->exam_datetime_notification_datetime,
                'result_notification_datetime'  => $request->result_notification_datetime,
                'proctor_id' => $request->proctor_id ?? null,
                'status' => ExamStatusEnum::ACTIVE->value,
                'id' => $realExam->id,
            ]);

            foreach ($request->questions_types as $question_type) {
                $realExam->real_exam_question_types()->create([
                    'question_type' => $question_type['type_id'],
                    'questions_count' => $question_type['questions_count'],
                    'question_score' => $question_type['question_score'],
                ]);
            }

            //////////add Topics of exam

            foreach ($examFormsQuestions as $questionsIds) {
                $formQuestions = $this->getQuestionsChoicesCombinations($questionsIds);
                $form = $realExam->forms()->create();
                foreach ($formQuestions as $question) {
                    $form->form_questions()->create([
                        'question_id' => $question->question_id,
                        'combination_id' => $question->combination_id?? null,
                    ]);
                }
            }
            ////////// modify question usage table يفضل ان يتم عمل دالة مشتركة حتى يتم استخدامها في الاختبار الورقي


            return ResponseHelper::successWithData($realExam->id);
        }else{
            return ResponseHelper::serverError();
        }
    }

    public function modifyRealExam(Request $request)
    {

        $realExam = RealExam::findOrFail($request->id);
        $realExam->update([
            'type' => $request->type_id ?? $realExam->type,
            'datetime' => $request->datetime ?? $realExam->datetime,
            'note' => $request->special_note ?? $realExam->note,
            'form_name_method' => $request->form_name_method ?? $realExam->form_name_method,
        ]);

        $onlinExam = OnlineExam::findOrFail($realExam->id);
        $onlinExam->update([
            'conduct_method' =>  $request->conduct_method_id ??  $onlinExam->conduct_method,
            'exam_datetime_notification_datetime' => $request->datetime_notification_datetime ?? $onlinExam->exam_datetime_notification_datetime,
            'result_notification_datetime'  => $request->result_notification_datetime ?? $onlinExam->result_notification_datetime,
            'proctor_id' => $request->proctor_id ?? $onlinExam->proctor_id,
        ]);

        return ResponseHelper::success();
    }

    public function deleteOnlineExam(Request $request)
    {
        // يتم حذف كل ما يتعلق بالاختبار وايضا اسئلة الاختبار التي قد تم توليدها
        // دراسة كيفية امكانية انقاص بيانات استخدام الاسئلة
        return ExamHelper::deleteRealExam($request->id);
    }

    public function retrieveOnlineExams(Request $request) ////**** يتم اضافة شرط ان يتم ارجاع الاختبارات التي تنتمي الى المستخدم الحالي
    {

        $enumReplacements  = [];

        $onlineExams =  DB::table('real_exams')
            ->join('online_exams', 'real_exams.id', '=', 'online_exams.id')
            ->join('course_lecturers', 'real_exams.course_lecturer_id', '=', 'course_lecturers.id')
            ->join('department_course_parts', 'course_lecturers.department_course_part_id', '=', 'department_course_parts.id')
            ->select(
                'real_exams.id',
                'real_exams.datetime',
                'real_exams.forms_count',
            )
            ->when($request->status_id, function ($query) use ($request) {
                return $query->where('online_exams.status', '=', $request->status_id);
            })
            ->when($request->type_id, function ($query) use ($request) {
                return $query->where('real_exams.type', '=', $request->type_id);
            })
            ->when($request->type_id === null, function ($query) use ($request) {
                return $query->selectRaw('real_exams.type as type_name');
            })
            ->when($request->status_id === null, function ($query) use ($request) {
                return $query->selectRaw('online_exams.status as status_name');
            })
            ->where('department_course_parts.id', '=', $request->department_course_part_id)
            ->get();

        if (!$request->status_id && !$request->type_id) {

            array_push($enumReplacements,  new EnumReplacement('type_name', ExamTypeEnum::class));
            array_push($enumReplacements,  new EnumReplacement('status_name', ExamStatusEnum::class));
        } elseif ($request->status_id && !$request->type_id) {
            array_push($enumReplacements,  new EnumReplacement('type_name', ExamTypeEnum::class));
        } else {
            array_push($enumReplacements,  new EnumReplacement('status_name', ExamStatusEnum::class));
        }

        $onlineExams = ProcessDataHelper::enumsConvertIdToName($onlineExams, $enumReplacements);
        $onlineExams =  ExamHelper::getRealExamsScore($onlineExams); // sum score of
        return ResponseHelper::successWithData($onlineExams);
    }

    public function retrieveOnlineExamsAndroid(Request $request) ////////** this attribute department_course_part_id can be null
    {

        $enumReplacements  = [
            new EnumReplacement('type_name', ExamTypeEnum::class),
            new EnumReplacement('status_name', ExamStatusEnum::class),
            new EnumReplacement('course_part_name', CoursePartsEnum::class),
            new EnumReplacement('language_name', LanguageEnum::class),
        ];

        $onlineExams =  DB::table('real_exams')
            ->join('online_exams', 'real_exams.id', '=', 'online_exams.id')
            ->join('course_lecturers', 'real_exams.course_lecturer_id', '=', 'course_lecturers.id')
            ->join('department_course_parts', 'course_lecturers.department_course_part_id', '=', 'department_course_parts.id')
            ->join('department_courses', 'department_course_parts.department_course_id', '=', 'department_courses.id')
            ->join('courses', 'department_courses.course_id', '=', 'courses.id')
            ->join('course_parts', 'department_course_parts.course_part_id', '=', 'course_parts.id')
            ->select(
                'courses.arabic_name as course_name ',
                'course_parts.part_id as course_part_name ',
                'real_exams.id',
                'real_exams.datetime',
                'real_exams.language as language_name',
                'real_exams.type as type_name',
                'online_exams.status as status_name',
            )
            ->when($request->department_course_part_id, function ($query) use ($request) {
                return $query->where('department_course_parts.id', '=', $request->department_course_part_id);
            })
            ->when($request->status_id, function ($query) use ($request) {
                return $query->where('online_exams.status', '=', $request->stsatus_id);
            })
            ->when($request->type_id, function ($query) use ($request) {
                return $query->where('real_exams.type', '=', $request->type_id);
            })
            ->get();

        $onlineExams = ProcessDataHelper::enumsConvertIdToName($onlineExams, $enumReplacements);

        return ResponseHelper::successWithData($onlineExams);
    }

    public function retrieveOnlineExam(Request $request)
    {
        $realExam = RealExam::findOrFail($request->id, [
            'id','language as language_name', 'difficulty_level as defficulty_level_name',
            'forms_count', 'form_configuration_method as form_configuration_method_name', 'form_name_method as form_name_method_name',
            'datetime', 'duration', 'type as type_name', 'note as special_note', 'course_lecturer_id'
        ]);
        $realExam = ProcessDataHelper::enumsConvertIdToName($realExam, [
            new EnumReplacement('language_name', LanguageEnum::class),
            new EnumReplacement('defficulty_level_name', ExamDifficultyLevelEnum::class),
            new EnumReplacement('form_configuration_method_name', FormConfigurationMethodEnum::class),
            new EnumReplacement('form_name_method_name', FormNameMethodEnum::class),
            new EnumReplacement('type_name', ExamTypeEnum::class),
        ]);

        $onlineExam = OnlineExam::where('id', $realExam->id)->first(['conduct_method as conduct_method_name', 'status as status_name', 'proctor_id as proctor_name', 'exam_datetime_notification_datetime as datetime_notification_datetime', 'result_notification_datetime']);
        $onlineExam = ProcessDataHelper::enumsConvertIdToName($onlineExam, [
            new EnumReplacement('status_name', ExamStatusEnum::class),
            new EnumReplacement('conduct_method_name', ExamStatusEnum::class),
        ]);
        $onlineExam = ProcessDataHelper::columnConvertIdToName($onlineExam, [ // need to fix columnConvertIdToName method
            new ColumnReplacement('proctor_name', 'arabic_name', Employee::class),
        ]);


        $courseLecturer = $realExam->course_lecturer()->first();
        $departmentCoursePart = $courseLecturer->department_course_part()->first();
        $coursePart = $departmentCoursePart->course_part()->first(['part_id as course_part_name']);
        $coursePart = ProcessDataHelper::enumsConvertIdToName($coursePart, [
            new EnumReplacement('course_part_name', CoursePartsEnum::class),
        ]);
        $departmentCourse = $departmentCoursePart->department_course()->first(['level as level_name', 'semester as semester_name', 'department_id', 'course_id']);
        $departmentCourse = ProcessDataHelper::enumsConvertIdToName($departmentCourse, [
            new EnumReplacement('level_name', LevelsEnum::class),
            new EnumReplacement('semester_name', SemesterEnum::class),
        ]);
        $department = $departmentCourse->department()->first(['arabic_name as department_name','college_id']);
        $college = $department->college()->first(['arabic_name as college_name']);
        $course = $departmentCourse->course()->first(['arabic_name as course_name']);
        $questionTypes = $realExam->real_exam_question_types()->get(['question_type as type_name', 'questions_count', 'question_score']);
        $questionTypes = ProcessDataHelper::enumsConvertIdToName($questionTypes, [
            new EnumReplacement('type_name', QuestionTypeEnum::class),
        ]);
        //*** make unset to : 'department_id', 'course_id', 'college_id', 'course_lecturer_id'
        $departmentCourse = $departmentCourse->toArray();
        unset($departmentCourse['department_id']);
        unset($departmentCourse['course_id']);

        $department = $department->toArray();
        unset($department['college_id']);

        $realExam = $realExam->toArray();
        unset($realExam['course_lecturer_id']);
        unset($realExam['id']);

        $realExam =
        $realExam +
        $onlineExam ->toArray()+
        $coursePart->toArray() +
        $departmentCourse  +
        $department +
        $college->toArray() +
        $course->toArray();

        $realExam['questionTypes'] = $questionTypes;

        return ResponseHelper::successWithData($realExam);
    }

    public function retrieveEditableOnlineExam(Request $request)
    {
        $realExam = RealExam::findOrFail($request->id, [
            'form_name_method as form_name_method_id',
            'datetime', 'type as type_id', 'note as special_note'
        ]);

        $onlinExam = $realExam->online_exam()->get([
            'conduct_method as conduct_method_id',
            'exam_datetime_notification_datetime as datetime_notification_datetime',
            'proctor_id', 'result_notification_datetime'
        ]);

        return ResponseHelper::successWithData( $realExam->toArray()+ $onlinExam->toArray());
    }

    public function retrieveOnlineExamChapters(Request $request)
    {
        $onlineExamChapters = ExamHelper::retrieveRealExamChapters($request->exam_id);
        return ResponseHelper::successWithData($onlineExamChapters);
    }

    public function retrieveOnlineExamChapterTopics(Request $request)
    {
        $onlineExamChapterTopics = ExamHelper::retrieveRealExamChapterTopics($request->exam_id, $request->chapter_id);
        return ResponseHelper::successWithData($onlineExamChapterTopics);
    }


    public function retrieveOnlineExamForms(Request $request)
    {
        $onlineExamForms = ExamHelper::retrieveRealExamForms($request->exam_id);
        return ResponseHelper::successWithData($onlineExamForms);
    }


    public function retrieveOnlineExamFormQuestions(Request $request)
    {
        $onlineExamFormQuestions = ExamHelper::retrieveRealExamFormQuestions($request->form_id);
        return ResponseHelper::successWithData($onlineExamFormQuestions);
    }

    public function changeOnlineExamStatus(Request $request)
    {
        $onlineExam = OnlineExam::findOrFail($request->id);
        if (!(intval($onlineExam->status) === ExamStatusEnum::COMPLETE->value)) {

            if (intval($onlineExam->status) === ExamStatusEnum::SUSPENDED->value) {

                $onlineExam->update([
                    'status' => ExamStatusEnum::ACTIVE->value,
                ]);
            } else {

                $onlineExam->update([
                    'status' => ExamStatusEnum::SUSPENDED->value,
                ]);
            }
        } else {
            return ResponseHelper::clientError('this exam is completed, you cant chande its status');
        }
        return ResponseHelper::success();
    }

    private function getAlgorithmData($request)
    {
        // دالة مشتركة للاختبار الالكتروني والورقي
        $algorithmData = [
            'estimated_time' => $request->duration,
            'difficulty_level' => $request->difficulty_level_id,
            'forms_count' => ($request->form_configuration_method_id === FormConfigurationMethodEnum::DIFFERENT_FORMS->value)? $request->forms_count : 1,
            'question_types_and_questions_count' => [
                'id' => $request->questions_types['type_id'],
                'count' => $request->questions_types['questions_count']
            ],
        ];


        $questionTypesIds = $request->questions_types['type_id']; // التحقق من ان نحصل على مصفوفه
        $accessabilityStatusIds = [
            AccessibilityStatusEnum::REALEXAM->value,
            AccessibilityStatusEnum::PRACTICE_REALEXAM->value,
        ];
        $questions =  DB::table('questions')
            ->join('question_usages', 'questions.id', '=', 'question_usages.question_id')
            ->join('topics', 'questions.topic_id', '=', 'topics.id')
            ->select(
                'questions.id',
                'questions.type as type_id',
                'questions.difficulty_level',
                'questions.estimated_answer_time as answer_time',
                'question_usages.online_exam_last_selection_datetime',
                'question_usages.practice_exam_last_selection_datetime',
                'question_usages.paper_exam_last_selection_datetime',
                'question_usages.online_exam_selection_times_count',
                'question_usages.practice_exam_selection_times_count',
                'question_usages.paper_exam_selection_times_count',
                'topics.id as topic_id',
            )
            ->where('questions.status', '=', QuestionStatusEnum::ACCEPTED->value)
            ->where('questions.language', '=', $request->language_id)
            ->whereIn('questions.accessability_status', $accessabilityStatusIds)
            ->whereIn('questions.type', $questionTypesIds)
            ->whereIn('topics.id', $request->topicsIds)
            ->get();
            foreach ($questions as $question) {
                // يجب ان يتم تحديد اوزان هذه المتغيرات لضبط مقدار تاثير كل متغير على حل خوارزمية التوليد

                $question['last_selection'] = DatetimeHelper::convertSecondsToDays(
                    DatetimeHelper::getDifferenceInSeconds(now(), $question->online_exam_last_selection_datetime) + 
                    DatetimeHelper::getDifferenceInSeconds(now(), $question->practice_exam_last_selection_datetime) + 
                    DatetimeHelper::getDifferenceInSeconds(now(), $question->paper_exam_last_selection_datetime)
                    ) / 3;
                $question['selection_times'] = (
                    $question->online_exam_selection_times_count + 
                    $question->practice_exam_selection_times_count + 
                    $question->paper_exam_selection_times_count
            ) / 3;
            // حذف الاعمدة التي تم تحويلها الي عمودين فقط من الاسئلة 
            unset($question['online_exam_last_selection_datetime']);
            unset($question['practice_exam_last_selection_datetime']);
            unset($question['paper_exam_last_selection_datetime']);
            unset($question['online_exam_selection_times_count']);
            unset($question['practice_exam_selection_times_count']);
            unset($question['paper_exam_selection_times_count']);
            }

        $algorithmData['questions'] = $questions;
        return $algorithmData;
    }

    private function getQuestionsChoicesCombinations($questionsIds)
    {
        // يفضل ان يتم عملها مشترك ليتم استخداما في الاختبار الورقي والتجريبي
        // تقوم هذه الدالة باختيار توزيعة الاختيارات للاسئلة من نوع اختيار من متعدد
       /**
        * steps of function
        *   اختيار الاسئلة التي نوعها اختيار من متعدد
        *   اختيار احد التوزيعات التي يمتلكها السؤال بشكل عشوائي
        *   يتم اضافة رقم التوزيعة المختارة الي السؤال
        */
        $formQuestions = [];
        foreach ($questionsIds as $questionId) {
            $question = Question::findOrFail($questionId);
            $combination_id = null;
            if($question->type === QuestionTypeEnum::MULTIPLE_CHOICE->value){
                $combination_id = $this->selectQuestionsChoicesCombination($question);
            }
            array_push($formQuestions, [
                'question_id' => $questionId,
                'combination_id' => $combination_id
            ]);
        }
        return $formQuestions;
    }

    private function selectQuestionsChoicesCombination(Question $question): int
    {
        $qestioChoicesCombinations = $question->question_choices_combinations()->get(['combination_id']);
        $selectedIndex = array_rand($qestioChoicesCombinations->combination_id);
        return $qestioChoicesCombinations->combination_id[$selectedIndex];
    }

    public function rules(Request $request): array
    {
        // need to make rules
        $rules = [
            'title' => 'nullable|string',
            'language_id' => ['required', new Enum(LanguageEnum::class)],
            'duration' => 'required|integer',
            'difficulty_level_id' => ['required', new Enum(ExamDifficultyLevelEnum::class)],
            'conduct_method_id' => ['required', new Enum(ExamConductMethodEnum::class)],
            'department_course_part_id' => 'required|exists:department_course_parts,id',
            'proctor_id' => 'required|exists:employees,id',
            'status_id' => ['required', new Enum(ExamStatusEnum::class)],
            'exam_datetime_notification_datetime' => 'required|date',
            'result_notification_datetime' => 'required|date',
            'form_configuration_method_id' => ['required', new Enum(FormConfigurationMethodEnum::class)],
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
