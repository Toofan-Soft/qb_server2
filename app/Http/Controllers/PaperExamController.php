<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Form;
use App\Models\User;
use App\Helpers\Param;
use App\Models\Employee;
use App\Models\Question;
use App\Models\RealExam;
use App\Enums\LevelsEnum;
use App\Models\PaperExam;
use App\Enums\ExamTypeEnum;
use App\Enums\LanguageEnum;
use App\Enums\SemesterEnum;
use App\Helpers\ExamHelper;
use App\Helpers\NullHelper;
use App\Helpers\ParamHelper;
use App\Models\FormQuestion;
use Illuminate\Http\Request;
use App\Helpers\DeleteHelper;
use App\Enums\CoursePartsEnum;
use App\Models\CourseLecturer;
use App\Enums\QuestionTypeEnum;
use App\Enums\RealExamTypeEnum;
use App\Helpers\DatetimeHelper;
use App\Helpers\ResponseHelper;
use App\Helpers\ValidateHelper;
use App\Helpers\EnumReplacement;
use App\Helpers\OnlinExamHelper;
use App\Enums\FormNameMethodEnum;
use App\Enums\QuestionStatusEnum;
use App\Models\TrueFalseQuestion;
use App\AlgorithmAPI\GenerateExam;
use App\Enums\TrueFalseAnswerEnum;
use App\Helpers\ProcessDataHelper;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\DepartmentCoursePart;
use Illuminate\Validation\Rules\Enum;
use App\Enums\AccessibilityStatusEnum;
use App\Enums\ExamDifficultyLevelEnum;
use Illuminate\Support\Facades\Storage;
use App\Enums\FormConfigurationMethodEnum;

class PaperExamController extends Controller
{
    public function addPaperExam(Request $request)
    {        
        if ($x=ValidateHelper::validateData($request, $this->rules($request))) {
            return  ResponseHelper::clientError1($x);
        }

        $algorithmData = $this->getAlgorithmData($request);

        $examFormsQuestions = (new GenerateExam())->execute($algorithmData);

        if ($examFormsQuestions) { // modify to use has function
            $employee = Employee::where('user_id',  auth()->user()->id)->first();

            $courseLecturer = CourseLecturer::where('department_course_part_id', '=', $request->department_course_part_id)
                ->where('lecturer_id', $employee->id)
                ->where('academic_year', now()->format('Y'))
                ->first();

            $realExam = $courseLecturer->real_exams()->create([
                'type' => $request->type_id,
                'datetime' => $request->datetime,
                'duration' => $request->duration,
                'language' => $request->language_id,
                'note' => $request->special_note ?? null,
                'difficulty_level' => $request->difficulty_level_id,
                'forms_count' => $request->forms_count,
                'form_configuration_method' => $request->form_configuration_method_id,
                'form_name_method' => $request->form_name_method_id,
                'exam_type' => RealExamTypeEnum::PAPER->value,
            ]);

            $paperExam = PaperExam::create([
                'id' => $realExam->id,
                'course_lecturer_name' => $request->lecturer_name ?? $employee->arabic_name,
            ]);

            foreach ($request->questions_types as $question_type) {
                $realExam->real_exam_question_types()->create([
                    'question_type' => $question_type['type_id'],
                    'questions_count' => $question_type['questions_count'],
                    'question_score' => $question_type['question_score'],
                ]);
            }

            //////////add Topics of exam

            // if (intval($request->form_configuration_method) === FormConfigurationMethodEnum::SIMILAR_FORMS->value) {
            //     $realExam->forms()->create();
            // } else {
            //     for ($i = 0; $i <= $request->forms_count; $i++ ) {
            //         $realExam->forms()->create();
            //     }

            // }

            foreach ($examFormsQuestions as $questionsIds) {
                $formQuestions = $this->getQuestionsChoicesCombinations($questionsIds);
                $form = $realExam->forms()->create();
                foreach ($formQuestions as $question) {
                    $form->form_questions()->create([
                        'question_id' => $question['question_id'],
                        'combination_id' => $question['combination_id'] ?? null,
                    ]);
                }
            }
            ////////// modify question usage table يفضل ان يتم عمل دالة مشتركة حتى يتم استخدامها في الاختبار الورقي

            return ResponseHelper::successWithData(['id' => $realExam->id]);
        }else{
            return ResponseHelper::serverError();
        }
    }

    public function modifyPaperExam(Request $request)
    {
        $params = ParamHelper::getParams(
            $request,
            [
                new Param('type_id', 'type'),
                new Param('datetime'),
                new Param('form_name_method_id', 'form_name_method'),
                new Param('special_note', 'note')
            ]
        );
        
        RealExam::findOrFail($request->id)
            ->update($params);
        
        $params = ParamHelper::getParams(
            $request,
            [
                new Param('lecturer_name', 'course_lecturer_name')
            ]
        );

        PaperExam::findOrFail($request->id)
            ->update($params);
        
        return ResponseHelper::success();
    }

    public function deletePaperExam(Request $request)
    {
        return ExamHelper::deleteRealExam($request->id);
    }

    public function retrievePaperExams(Request $request) ////**** يتم اضافة شرط ان يتم ارجاع الاختبارات التي تنتمي الى المستخدم الحالي
    {
        $enumReplacements  = [];

        $lecturer_id = Employee::where('user_id', '=', auth()->user()->id)->first(['id'])['id'];

        $paperExams =  DB::table('real_exams')
            ->join('paper_exams', 'real_exams.id', '=', 'paper_exams.id')
            ->join('course_lecturers', 'real_exams.course_lecturer_id', '=', 'course_lecturers.id')
            ->join('department_course_parts', 'course_lecturers.department_course_part_id', '=', 'department_course_parts.id')
            ->select(
                'real_exams.id',
                'real_exams.datetime',
                'real_exams.forms_count',
                'paper_exams.course_lecturer_name as lecturer_name'
            )
            ->when(isset($request->type_id), function ($query) use ($request) {
                return $query->where('real_exams.type', '=', $request->type_id);
            })
            ->when($request->type_id === null, function ($query) use ($request) {
                return $query->selectRaw('real_exams.type as type_name');
            })
            ->where('department_course_parts.id', '=', $request->department_course_part_id)
            ->where('course_lecturers.lecturer_id', '=', $lecturer_id)
            ->get()
            ->map(function ($exam) {
                $exam->datetime = DatetimeHelper::convertTimestampToMilliseconds($exam->datetime);
                return $exam;
            });
        
        if (!isset($request->type_id)) {
            array_push($enumReplacements,  new EnumReplacement('type_name', ExamTypeEnum::class));
        }

        $paperExams = ProcessDataHelper::enumsConvertIdToName($paperExams, $enumReplacements);

        $paperExams =  ExamHelper::getRealExamsScore($paperExams); // sum score of

        return ResponseHelper::successWithData($paperExams);
    }

    public function retrievePaperExamsAndroid(Request $request) ////////** this attribute department_course_part_id can be null
    {
        $enumReplacements  = [
            new EnumReplacement('type_name', ExamTypeEnum::class),
            new EnumReplacement('course_part_name', CoursePartsEnum::class),
            new EnumReplacement('language_name', LanguageEnum::class),
        ];

        $lecturer_id = Employee::where('user_id', '=', auth()->user()->id)->first(['id'])['id'];

        $paperExams =  DB::table('real_exams')
            ->join('paper_exams', 'real_exams.id', '=', 'paper_exams.id')
            ->join('course_lecturers', 'real_exams.course_lecturer_id', '=', 'course_lecturers.id')
            ->join('department_course_parts', 'course_lecturers.department_course_part_id', '=', 'department_course_parts.id')
            ->join('department_courses', 'department_course_parts.department_course_id', '=', 'department_courses.id')
            ->join('courses', 'department_courses.course_id', '=', 'courses.id')
            ->join('course_parts', 'department_course_parts.course_part_id', '=', 'course_parts.id')
            ->select(
                'courses.arabic_name as course_name',
                'course_parts.part_id as course_part_name',
                'real_exams.id',
                'real_exams.datetime',
                'real_exams.language as language_name',
                'real_exams.type as type_name',
                'paper_exams.course_lecturer_name as lecturer_name'
            )
            ->when(isset($request->department_course_part_id), function ($query) use ($request) {
                return $query->where('department_course_parts.id', '=', $request->department_course_part_id);
            })
            ->when(isset($request->type_id), function ($query) use ($request) {
                return $query->where('real_exams.type', '=', $request->type_id);
            })
            ->where('course_lecturers.lecturer_id', '=', $lecturer_id)
            ->get()
            ->map(function ($exam) {
                $exam->datetime = DatetimeHelper::convertTimestampToMilliseconds($exam->datetime);
                return $exam;
            });
                
        $paperExams = ProcessDataHelper::enumsConvertIdToName($paperExams, $enumReplacements);
        
        return ResponseHelper::successWithData($paperExams);
    }

    public function retrievePaperExam(Request $request)
    {
        $realExam = RealExam::findOrFail($request->id, [
            'id', 'language as language_name', 'difficulty_level as difficulty_level_name',
            'forms_count', 'form_configuration_method as form_configuration_method_name', 'form_name_method as form_name_method_name',
            'datetime', 'duration', 'type as type_name', 'note as special_note', 'course_lecturer_id'
        ]);

        $realExam->lecturer_name = PaperExam::findOrFail($request->id, ['course_lecturer_name'])->first()['course_lecturer_name'];
        
        $realExam = ProcessDataHelper::enumsConvertIdToName($realExam, [
            new EnumReplacement('language_name', LanguageEnum::class),
            new EnumReplacement('difficulty_level_name', ExamDifficultyLevelEnum::class),
            new EnumReplacement('form_configuration_method_name', FormConfigurationMethodEnum::class),
            new EnumReplacement('form_name_method_name', FormNameMethodEnum::class),
            new EnumReplacement('type_name', ExamTypeEnum::class),
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
        $department = $departmentCourse->department()->first(['arabic_name as department_name', 'college_id']);
        $college = $department->college()->first(['arabic_name as college_name']);
        $course = $departmentCourse->course()->first(['arabic_name as course_name']);
        $questionTypes = $realExam->real_exam_question_types()->get(['question_type as type_name', 'questions_count', 'question_score']);
        $questionTypes = ProcessDataHelper::enumsConvertIdToName($questionTypes, [
            new EnumReplacement('type_name', QuestionTypeEnum::class),
        ]);

        $questionTypes = collect($questionTypes)->map(function ($type) {
            return [
                'type_name' => $type->type_name,
                'questions_count' => $type->questions_count,
                'question_score' => (float) $type->question_score,
            ];
        })->toArray();

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
            $coursePart->toArray() +
            $departmentCourse  +
            $department +
            $college->toArray() +
            $course->toArray();

        $realExam['questions_types'] = $questionTypes;

        $realExam['datetime'] = DatetimeHelper::convertTimestampToMilliseconds($realExam['datetime']);

        $realExam = NullHelper::filter($realExam);

        return ResponseHelper::successWithData($realExam);
    }

    public function retrieveEditablePaperExam(Request $request)
    {
        $realExam = RealExam::findOrFail($request->id,[
            'id','form_name_method as form_name_method_id',
            'datetime', 'type as type_id', 'note as special_note'
        ]);

        $paperExam =  PaperExam::where('id', $realExam->id)->first(['course_lecturer_name as lecturer_name']);
        $realExam = $realExam->toArray();
        
        unset($realExam['id']);
        $realExam = $realExam + $paperExam->toArray();

        $realExam['datetime'] = DatetimeHelper::convertTimestampToMilliseconds($realExam['datetime']);

        $exam = NullHelper::filter($realExam);

        return ResponseHelper::successWithData($exam);
    }

    public function retrievePaperExamChapters(Request $request)
    {
        return ExamHelper::retrieveRealExamChapters($request->exam_id);
    }

    public function retrievePaperExamChapterTopics(Request $request)
    {
        return ExamHelper::retrieveRealExamChapterTopics($request->exam_id, $request->chapter_id);
    }

    public function retrievePaperExamForms(Request $request)
    {
        $forms = ExamHelper::retrieveRealExamForms($request->exam_id);
        return ResponseHelper::successWithData($forms);
    }

    public function retrievePaperExamFormQuestions(Request $request)
    {
        $questions = ExamHelper::getFormQuestionsWithDetails($request->form_id, false, false, true);
        return ResponseHelper::successWithData($questions);
    }

    public function exportPaperExamToPDF(Request $request)
    {
        // id, with mirror?, with answered mirror?
       /* Data:
        *   .
        *   . university *
        *   . college *
        *   . department *
        *   . level *
        *   .
        *   . course *
        *   . course part *
        *   . exam type *
        *   . date *
        *   . duration *
        *
        *   . lecturer *
        *   . score *
        *
        *   . forms
        *       . form name?
        *       . questions
        *           . content
        *           . attachment?
        *           . is true?
        *           . choices?
        *               . content
        *               . attachment?
        *               . is true?
        *   .
        * */
        // تبقى جزء فحص اذا كان يشتي مع اجابة او لا

        $realExam = RealExam::findOrFail($request->id,[
            'id', 'datetime', 'duration', 'type as type_name', 'course_lecturer_id', 
            'forms_count', 'form_name_method', 'form_configuration_method',
        ]);

        $realExam = ProcessDataHelper::enumsConvertIdToName($realExam, [
            new EnumReplacement('type_name', ExamTypeEnum::class),
        ]);

        $paperExam = PaperExam::where('id', $realExam->id)->first(['course_lecturer_name as lecturer_name']);
        // $paperExam = $realExam->paper_exam()->first(['course_lecturer_name as lecturer_name']);

        $courseLecturer = $realExam->course_lecturer()->first();
        $departmentCoursePart = $courseLecturer->department_course_part()->first();

        $coursePart = $departmentCoursePart->course_part()->first(['part_id as course_part_name']);
        $coursePart = ProcessDataHelper::enumsConvertIdToName($coursePart, [
            new EnumReplacement('course_part_name', CoursePartsEnum::class)
        ]);
        $departmentCourse = $departmentCoursePart->department_course()->first(['level as level_name', 'semester as semester_name', 'department_id', 'course_id']);
        $departmentCourse = ProcessDataHelper::enumsConvertIdToName($departmentCourse, [
            new EnumReplacement('level_name', LevelsEnum::class),
            new EnumReplacement('semester_name', SemesterEnum::class)
        ]);
        $department = $departmentCourse->department()->first(['arabic_name as department_name','college_id']);

        $college = $department->college()->first(['arabic_name as college_name']);

        $course = $departmentCourse->course()->first(['arabic_name as course_name']);
        // Total Score of exam 
        $questionsTypes = $realExam->real_exam_question_types()->get(['questions_count', 'question_score']);
        $totalScore = 0;
        foreach ($questionsTypes as $questionType) {
            $totalScore += ($questionType->questions_count * $questionType->question_score);
        }
        // university name 
        $jsonData = Storage::disk('local')->get('university.json');
        $universityData = json_decode($jsonData, true);
        $universityName = [
            'arabic_name' => $universityData['arabic_name'],
        ];

        // form and form questions 
        // as [formName, questions[], .....] or [formsName[name,...], questoins[]]
        $examFormsQuestions = [];
            $formsNames = ExamHelper::getRealExamFormsNames($realExam->form_name_method, $realExam->forms_count);
            $examForms = $realExam->forms()->get(['id']);
            if (intval($realExam->form_configuration_methode) === FormConfigurationMethodEnum::DIFFERENT_FORMS->value) {
                $i = 0;
                foreach ($examForms as $formId) {
                    $formQuestions = $this->getFormQuestions($formId->id, $request->with_answered_mirror);
                    array_push($examFormsQuestions, [$formsNames[$i++], $formQuestions]);
                }
            } else {
                $formQuestions = $this->getFormQuestions($examForms->id, $request->with_answered_mirror);
                array_push($examFormsQuestions, $formsNames);
                array_push($examFormsQuestions, $formQuestions);
            }

        $departmentCourse = $departmentCourse->toArray();
        unset($departmentCourse['department_id']);
        unset($departmentCourse['course_id']);

        $department = $department->toArray();
        unset($department['college_id']);

        $realExam = $realExam->toArray();
        unset($realExam['course_lecturer_id']);
        unset($realExam['id']);
        unset($realExam['form_name_method']);
        unset($realExam['forms_count']);
        unset($realExam['form_configuration_methode']);

        $realExam = $realExam +
        $paperExam->toArray() +
            $coursePart->toArray() +
            $departmentCourse +
            $department +
            $college->toArray() +
            $course->toArray();

        $realExam['score'] = $totalScore;
        $realExam['university_name'] = $universityName;
        $realExam['forms'] = $examFormsQuestions;

        return ResponseHelper::successWithData($realExam);
    }

    private function getFormQuestions ($formId, bool $withAnsweredMirror)
    {
        // return form questoin as [content, attachment, is_true, choices[content, attachment, is_true]]
        $questions = [];
        
        // $form = Form::findOrFail($formId);
        // $formQuestions = $form->form_questions()->get(['question_id', 'combination_id']);

        $formQuestions = FormQuestion::where('form_id', '=', $formId)->get();

        foreach ($formQuestions as $formQuestion) {
            $question = $formQuestion->question()->first(['content', 'attachment as attachment_url']);
            if ($formQuestion->combination_id) {
                if ($withAnsweredMirror) {
                    $question['choices'] = ExamHelper::retrieveCombinationChoices($formQuestion->question_id, $formQuestion->combination_id, false, true);
                } else {
                    $question['choices'] = ExamHelper::retrieveCombinationChoices($formQuestion->question_id, $formQuestion->combination_id, false, false);
                }
            } else {
                if ($withAnsweredMirror) {
                    $trueFalseQuestion = TrueFalseQuestion::findOrFail($formQuestion->question_id)->first(['answer']);
                    if (intval($trueFalseQuestion->answer) === TrueFalseAnswerEnum::TRUE->value) {
                        $question['is_true'] = true;
                    } else {
                        $question['is_true'] = false;
                    }
                }
            }
            array_push($questions, $question);
        }
            
        return $questions;
    }

    private function getAlgorithmData($request)
    {
        $types = [];
        foreach ($request->questions_types as $type) 
        {
            $t = [
                'id' => intval($type['type_id']),
                'count' => intval($type['questions_count'])
            ];

            array_push($types, $t);
        }

        // دالة مشتركة للاختبار الالكتروني والورقي
        $algorithmData = [
            'estimated_time' => intval($request->duration),
            // 'difficulty_level' => floatval($request->difficulty_level_id),
            'difficulty_level' => ExamDifficultyLevelEnum::toFloat($request->difficulty_level_id),
            'forms_count' => ($request->form_configuration_method_id === FormConfigurationMethodEnum::DIFFERENT_FORMS->value) ? $request->forms_count : 1,
            'question_types_and_questions_count' => $types
            // 'question_types_and_questions_count' => [
            //     // 'id' => $request->questions_types['type_id'],
            //     // 'count' => $request->questions_types['questions_count']
            //     'id' => $request->questions_types->type_id,
            //     'count' => $request->questions_types->questions_count
            // ],
        ];

        $questionTypesIds = [];
        foreach ($request->questions_types as $type) 
        {
            array_push($questionTypesIds, $type['type_id']);
        }

        // $questionTypesIds = $request->questions_types['type_id']; // التحقق من ان نحصل على مصفوفه
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
                'questions.topic_id',
                'topics.id as topic_id',
            )
            ->where('questions.status', '=', QuestionStatusEnum::ACCEPTED->value)
            ->where('questions.language', '=', $request->language_id)
            ->whereIn('questions.accessability_status', $accessabilityStatusIds)
            ->whereIn('questions.type', $questionTypesIds)
            ->whereIn('questions.topic_id', $request->topics_ids)
            // ->whereIn('topics.id', $request->topics_ids)
            // ->whereIn('topics.id', [3])
            ->get()
            ->toArray();
                
        foreach ($questions as $question) {
            // يجب ان يتم تحديد اوزان هذه المتغيرات لضبط مقدار تاثير كل متغير على حل خوارزمية التوليد

            $question->type_id = intval($question->type_id);
            $question->difficulty_level = floatval($question->difficulty_level);

            // $selections = [1, 2, 3, 4, 5];
            // $randomIndex = array_rand($selections);
            // $question['last_selection'] = $selections[$randomIndex];
            $question->last_selection = 3;
            // $question['last_selection'] = DatetimeHelper::convertSecondsToDays(
            //     DatetimeHelper::getDifferenceInSeconds(now(), $question->online_exam_last_selection_datetime) +
            //         DatetimeHelper::getDifferenceInSeconds(now(), $question->practice_exam_last_selection_datetime) +
            //         DatetimeHelper::getDifferenceInSeconds(now(), $question->paper_exam_last_selection_datetime)
            // ) / 3;

            $question->selection_times = 2;
            // $question['selection_times'] = (
            //     $question->online_exam_selection_times_count +
            //     $question->practice_exam_selection_times_count +
            //     $question->paper_exam_selection_times_count
            // ) / 3;
            // حذف الاعمدة التي تم تحويلها الي عمودين فقط من الاسئلة 
            unset($question->online_exam_last_selection_datetime);
            unset($question->practice_exam_last_selection_datetime);
            unset($question->paper_exam_last_selection_datetime);
            unset($question->online_exam_selection_times_count);
            unset($question->practice_exam_selection_times_count);
            unset($question->paper_exam_selection_times_count);
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

            if ($question->type == QuestionTypeEnum::MULTIPLE_CHOICE->value) {
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
        $qestionChoicesCombinationsIds = $question->question_choices_combinations()
            ->get(['combination_id'])
            ->map(function ($qestionChoicesCombination) {
                return $qestionChoicesCombination->combination_id;
            })->toArray();
        
        $selectedIndex = array_rand($qestionChoicesCombinationsIds);
        return $qestionChoicesCombinationsIds[$selectedIndex];
    }


    public function rules(Request $request): array
    {
        $rules = [
            'title' => 'nullable|string',
            'language_id' => ['required', new Enum(LanguageEnum::class)],
            'duration' => 'required|integer',
            'difficulty_level_id' => ['required', new Enum(ExamDifficultyLevelEnum::class)],
            'department_course_part_id' => 'required|exists:department_course_parts,id',
            'course_lecturer_name' => 'nullable|string',
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
