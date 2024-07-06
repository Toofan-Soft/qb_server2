<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Helpers\Param;
use App\Models\Question;
use App\Enums\LevelsEnum;
use App\Models\PaperExam;
use App\Enums\LanguageEnum;
use App\Enums\SemesterEnum;
use App\Helpers\ExamHelper;
use App\Helpers\NullHelper;
use App\Helpers\ParamHelper;
use App\Models\PracticeExam;
use Illuminate\Http\Request;
use App\Enums\ExamStatusEnum;
use App\Helpers\DeleteHelper;
use App\Enums\CoursePartsEnum;
use App\Enums\QuestionTypeEnum;
use App\Helpers\DatetimeHelper;
use App\Helpers\ResponseHelper;
use App\Helpers\ValidateHelper;
use App\Helpers\EnumReplacement;
use App\Helpers\OnlinExamHelper;
use App\Enums\QuestionStatusEnum;
use App\Helpers\EnumReplacement1;
use App\Models\TrueFalseQuestion;
use App\AlgorithmAPI\GenerateExam;
use App\Enums\TrueFalseAnswerEnum;
use App\Helpers\ProcessDataHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Enums\ExamConductMethodEnum;
use App\Models\DepartmentCoursePart;
use App\Models\PracticeExamQuestion;
use Illuminate\Validation\Rules\Enum;
use App\Enums\AccessibilityStatusEnum;
use App\Enums\ExamDifficultyLevelEnum;
use App\Enums\FormConfigurationMethodEnum;

class PracticeExamController extends Controller
{
    public function addPracticeExam(Request $request)
    {
        if (ValidateHelper::validateData($request, $this->rules($request))) {
            return  ResponseHelper::clientError(401);
        }

        $algorithmData = $this->getAlgorithmData($request);

        $examQuestions = (new GenerateExam())->execute($algorithmData);
        
        if ($examQuestions) { // modify to use has function

            $user = User::findOrFail(auth()->user()->id);

            // return $user;
            $practiceExam = $user->practice_exams()->create([
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

            $examQuestions = $this->getQuestionsChoicesCombinations($examQuestions[0]);
            foreach ($examQuestions as $question) {
                $practiceExam->practice_exam_question()->create([
                    'question_id' => $question['question_id'],
                    'combination_id' => $question['combination_id'] ?? null,
                ]);
            }
            ////////// modify question usage table يفضل ان يتم عمل دالة مشتركة حتى يتم استخدامها في الاختبار الورقي

            return ResponseHelper::successWithData(['id' => $practiceExam->id]);
        } else {
            return ResponseHelper::serverError();
        }
    }

    public function modifyPracticeExam(Request $request)
    {
        $params = ParamHelper::getParams(
            $request,
            [
                new Param('title'),
            ]
        );

        PracticeExam::findOrFail($request->id)
            ->update($params);

        return ResponseHelper::success();
    }

    public function deletePracticeExam(Request $request)
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
            ->when(isset($request->status_id), function ($query) use ($request) {
                return  $query ->where('practice_exams.status', '=', $request->status_id);
            })
            ->when($request->status_id === null, function ($query)  {
                return  $query ->selectRaw('practice_exams.status as status_name');
            })
            ->where('practice_exams.department_course_part_id', '=', $request->department_course_part_id)
            ->where('practice_exams.user_id', '=', $userId)
            ->get();
        
        $enumReplacement = [
            new EnumReplacement('course_part_name', CoursePartsEnum::class),
        ];

        if (!isset($request->status_id)) {
            array_push($enumReplacement, new EnumReplacement('status_name', ExamStatusEnum::class));
        }

        $practiceExams = ProcessDataHelper::enumsConvertIdToName($practiceExams, $enumReplacement);

        foreach ($practiceExams as $practiceExam) {
            $examResult = $this->getPracticeExamResult($practiceExam->id);

            if ($examResult !== null) {
                $practiceExam->score_rate = $examResult['score_rate'];
                $practiceExam->appreciation = $examResult['appreciation'];
            }
        }

        $practiceExams = NullHelper::filter($practiceExams);

        return ResponseHelper::successWithData($practiceExams);
    }

    public function retrievePracticeExamsAndroid(Request $request)
    {
        // $user = User::findOrFail(auth()->user()->id)->first();
        // $practiceExams = [];
        // // هذه معقد، يجب ان يتم توحيد البيانات المراد ارجاعها مع الدالة السابق حتى تتسهل
        // if ($request->department_course_part_id) {
        // }
        // return $practiceExams;

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
                'practice_exams.language as language_name',
                // practice_exams.datetime,
            )
            ->when(isset($request->status_id), function ($query) use ($request) {
                return  $query ->where('practice_exams.status', '=', $request->status_id);
            })
            ->when($request->status_id === null, function ($query)  {
                return  $query ->selectRaw('practice_exams.status as status_name');
            })
            ->when(isset($request->department_course_part_id), function ($query) use ($request) {
                return  $query ->where('practice_exams.department_course_part_id', '=', $request->department_course_part_id);
            })
            ->where('practice_exams.user_id', '=', $userId)
            ->get();
        

        $enumReplacement = [
            new EnumReplacement('course_part_name', CoursePartsEnum::class),
            new EnumReplacement('language_name', LanguageEnum::class),
            new EnumReplacement('status_name', ExamStatusEnum::class)
        ];
        
        $practiceExams = ProcessDataHelper::enumsConvertIdToName($practiceExams, $enumReplacement);

        foreach ($practiceExams as $practiceExam) {
            $examResult = $this->getPracticeExamResult($practiceExam->id);

            if ($examResult !== null) {
                $practiceExam->score_rate = $examResult['score_rate'];
                $practiceExam->appreciation = $examResult['appreciation'];
            }
        }

        $practiceExams = NullHelper::filter($practiceExams);

        return ResponseHelper::successWithData($practiceExams);
    }

    public function retrievePracticeExamQuestions(Request $request)
    {
        $exam = PracticeExam::findOrFail($request->exam_id);

        $is_complete = ($exam->is_complete === ExamStatusEnum::COMPLETE->value) ? true : false;

        $questions = $this->getQuestions($request->exam_id, $is_complete);

        return ResponseHelper::successWithData($questions);
    }

    private function getQuestions($examId, $withAnswer=false)
    {
        // return questoin as [content, attachment, is_true, choices[content, attachment, is_true]]
        $questions = [];
        
        $examQuestions = PracticeExamQuestion::where('practice_exam_id', '=', $examId)->get();

        foreach ($examQuestions as $examQuestion) {
            // $question = $examQuestion->question()->first(['id', 'content', 'attachment as attachment_url']);
            $question = $examQuestion->question()->first(['id', 'content', 'attachment as attachment_url']);
            $answer = $examQuestion->answer;
            
            if ($examQuestion->combination_id) {
                if ($withAnswer) {
                    $question['choices'] = ExamHelper::retrieveCombinationChoices($examQuestion->question_id, $examQuestion->combination_id, true, true);
                } else {
                    $question['choices'] = ExamHelper::retrieveCombinationChoices($examQuestion->question_id, $examQuestion->combination_id, true, false);
                }

                $question->choices = collect($question->choices)->map(function ($choice) use ($answer) {
                    if ($choice['id'] === $answer) {
                        $choice['is_selected'] = true;
                    }

                    return $choice;
                });
            } else {
                if ($withAnswer) {
                    $trueFalseQuestion = TrueFalseQuestion::findOrFail($examQuestion->question_id)->first(['answer']);
                    if (intval($trueFalseQuestion->answer) === TrueFalseAnswerEnum::TRUE->value) {
                        $question['is_true'] = true;
                    } else {
                        $question['is_true'] = false;
                    }

                    if ($answer === TrueFalseAnswerEnum::TRUE->value) {
                        $question->user_answer = true;
                    } elseif ($answer === TrueFalseAnswerEnum::FALSE->value) {
                        $question->user_answer = false;
                    }
                }
            }
            array_push($questions, $question);
        }
            
        return $questions;
    }

    public function retrievePracticeExamsResult(Request $request)
    {
        $practiceExam = PracticeExam::findOrFail($request->id);
        if ($practiceExam->status === ExamStatusEnum::COMPLETE->value) {
            //time spent, question average answer time, appreciation, score rate, correct answer count, incorrect answer count

        }
        return [];
    }

    public function retrievePracticeExam(Request $request)
    {
        $practiceExam = PracticeExam::findOrFail($request->id, [
            // 'datetime'
            'id','title', 'duration', 'language as language_name',
            'conduct_method as is_mandatory_question_sequence', 'status as is_complete','department_course_part_id'
        ]);

        $practiceExam = ProcessDataHelper::enumsConvertIdToName($practiceExam, [
            new EnumReplacement('language_name', LanguageEnum::class)
        ]);

        $practiceExam->is_complete = ($practiceExam->is_complete === ExamStatusEnum::COMPLETE->value) ? true : false;
        $practiceExam->is_mandatory_question_sequence = ($practiceExam->is_mandatory_question_sequence === ExamConductMethodEnum::MANDATORY->value) ? true : false;

        $departmentCoursePart = DepartmentCoursePart::findOrFail($practiceExam->department_course_part_id);

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

        $practiceExam = $practiceExam->toArray();
        unset($practiceExam['department_course_part_id']);

        $practiceExam = $practiceExam +
            $coursePart->toArray() +
            $departmentCourse +
            $department +
            $college->toArray() +
            $course->toArray();
        
        $practiceExam = NullHelper::filter($practiceExam);
        
        return ResponseHelper::successWithData($practiceExam);
    }

    public function retrieveEditablePracticeExam(Request $request)
    {
        $practiceExam = PracticeExam::findOrFail($request->id, ['title']);

        // $practiceExam = NullHelper::filter($practiceExam);

        return ResponseHelper::successWithData($practiceExam);
    }

    public function savePracticeExamQuestionAnswer(Request $request)
    {
        $questionType = Question::findOrFail($request->question_id, ['type']);

        $answerId = null;
        if (intval($questionType->type) === QuestionTypeEnum::TRUE_FALSE->value) {
            $answerId = ($request->is_true) ? TrueFalseAnswerEnum::TRUE->value : TrueFalseAnswerEnum::FALSE->value;
        } else {
            $answerId =  intval($request->choice_id);
        }

        $practiceExamQuestion = PracticeExamQuestion::where('practice_exam_id', $request->exam_id)
            ->where('question_id', $request->question_id)
            ->first();
            
        // if ($practiceExamQuestion) {
        //     $practiceExamQuestion->update([
        //         'answer' => $answerId
        //     ]);
        // } else {
        //     // Handle the case where the PracticeExamQuestion was not found
        //     throw new \Exception('PracticeExamQuestion not found');
        // }
        
        $practiceExamQuestion->update([
            'answer' =>  $answerId,
            // 'answer_duration' => $request->answer_duration ?? null,
        ]);

        return ResponseHelper::success();
    }

    public function finishPracticeExam(Request $request)
    {
        $practiceExam = PracticeExam::findOrFail($request->id);
        $practiceExam->update([
            'status' => ExamStatusEnum::COMPLETE->value,
            // 'end_datetime' => now(),
        ]);
        return ResponseHelper::success();
    }

    public function suspendPracticeExam(Request $request)
    {
        $practiceExam = PracticeExam::findOrFail($request->id);

        if (intval($practiceExam->status) === ExamStatusEnum::ACTIVE->value) {
            $practiceExam->update([
                'status' => ExamStatusEnum::SUSPENDED->value,
            ]);
            return $practiceExam;
            return ResponseHelper::success();
        } else {
            return abort(404);
        }
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

    private function retrievePracticeExamResult($practiceExamId)
    {
        $practiceExam = PracticeExam::findOrFail($practiceExamId);
        
        if ($practiceExam->status === ExamStatusEnum::COMPLETE->value) {
            $examQuestions = $practiceExam->practice_exam_question()
                ->get(['question_id', 'answer', 'answer_duration', 'combination_id']);
            
            $totalScoure = $examQuestions->count();

            $StudentScore = 0;
            $timeSpent = 0;
            
            foreach ($examQuestions as $examQuestion) {
                $timeSpent = $timeSpent + $examQuestion->answer_duration;

                $question = Question::findOrFail($examQuestion->question_id);
    
                if (intval($question->type) === QuestionTypeEnum::TRUE_FALSE->value) {
                    if (ExamHelper::checkTrueFalseQuestionAnswer($question, $examQuestion->answer)) {
                        $StudentScore ++;
                    }
    
                } else {
                    if (ExamHelper::checkChoicesQuestionAnswer($question, $examQuestion->answer, $examQuestion->combination_id )) {
                        $StudentScore ++;
                    }
                }
            }
    
            $scoreRate = $StudentScore / $totalScoure * 100;
            $appreciation = ExamHelper::getExamResultAppreciation($scoreRate);
            $questionAverageAnswerTime = $timeSpent / $examQuestions->count();
            $incorrectAnswerCount = $examQuestions->count() - $scoreRate;
            
            $examResult = [
                'time_spent' => $timeSpent,
                'question_average_answer_time' => $questionAverageAnswerTime,
                'correct_answer_count' => $scoreRate,
                'incorrect_answer_count' => $incorrectAnswerCount,
                'score_rate' => $scoreRate,
                'appreciation' => $appreciation
            ];

            return $examResult;
        } else {
            // handle error...
        }
    }

    private function getPracticeExamResult($practiceExamId)
    {
        $practiceExam = PracticeExam::findOrFail($practiceExamId);
        
        if ($practiceExam->status === ExamStatusEnum::COMPLETE->value) {
            $examQuestions = $practiceExam->practice_exam_question()
                ->get(['question_id', 'answer', 'combination_id']);
            
            $totalScoure = $examQuestions->count();

            $StudentScore = 0;
            
            foreach ($examQuestions as $examQuestion) {
                $question = Question::findOrFail($examQuestion->question_id);
    
                if (intval($question->type) === QuestionTypeEnum::TRUE_FALSE->value) {
                    if (ExamHelper::checkTrueFalseQuestionAnswer($question, $examQuestion->answer)) {
                        $StudentScore ++;
                    }
                } else {
                    if (ExamHelper::checkChoicesQuestionAnswer($question, $examQuestion->answer, $examQuestion->combination_id )) {
                        $StudentScore ++;
                    }
                }
            }
            
            $scoreRate = $StudentScore / $totalScoure * 100;
            $appreciation = ExamHelper::getExamResultAppreciation($scoreRate);
            
            $examResult = [
                'score_rate' => $scoreRate,
                'appreciation' => $appreciation
            ];

            return $examResult;
        } else {
            // handle error...
            return null;
        }
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
