<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Helpers\Param;
use App\Models\Question;
use App\Enums\LevelsEnum;
use App\Enums\LanguageEnum;
use App\Enums\SemesterEnum;
use App\Helpers\ExamHelper;
use App\Helpers\NullHelper;
use App\Helpers\ParamHelper;
use App\Models\PracticeExam;
use Illuminate\Http\Request;
use App\Helpers\DeleteHelper;
use App\Enums\CoursePartsEnum;
use App\Enums\QuestionTypeEnum;
use App\Helpers\DatetimeHelper;
use App\Helpers\LanguageHelper;
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
use Illuminate\Support\Facades\Gate;
use App\Enums\PracticeExamStatusEnum;
use Illuminate\Validation\Rules\Enum;
use App\Enums\AccessibilityStatusEnum;
use App\Enums\ExamDifficultyLevelEnum;
use App\Enums\FormConfigurationMethodEnum;

class PracticeExamController extends Controller
{
    public function addPracticeExam(Request $request)
    {
        Gate::authorize('addPracticeExam', PracticeExamController::class);

        if (ValidateHelper::validateData($request, $this->rules($request))) {
            return  ResponseHelper::clientError();
        }
        try {
            $algorithmData = $this->getAlgorithmData($request);

            $examQuestions = (new GenerateExam())->execute($algorithmData);

            if (is_null($examQuestions)) { // modify to use has function

                $user = User::findOrFail(auth()->user()->id);
                DB::beginTransaction();
                // return $user;
                $practiceExam = $user->practice_exams()->create([
                    'department_course_part_id' => $request->department_course_part_id,
                    'title' => $request->title ?? null,
                    'language' => $request->language_id,
                    'datetime' => now()->getTimestamp(), // can make defult value in migration 
                    'duration' => $request->duration,
                    'difficulty_level' => $request->difficulty_level_id,
                    'conduct_method' => $request->conduct_method_id,
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

                $practiceExam->practice_exam_usage()->create([
                    'remaining_duration' => $practiceExam->duration
                ]);

                DB::commit();
                return ResponseHelper::successWithData(['id' => $practiceExam->id]);
            } else {
                DB::rollBack();
                return ResponseHelper::serverError();
            }
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function modifyPracticeExam(Request $request)
    {
        Gate::authorize('modifyPracticeExam', PracticeExamController::class);

        try {
            $params = ParamHelper::getParams(
                $request,
                [
                    new Param('title'),
                ]
            );

            PracticeExam::findOrFail($request->id)
                ->update($params);

            return ResponseHelper::success();
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function deletePracticeExam(Request $request)
    {
        Gate::authorize('deletePracticeExam', PracticeExamController::class);

        try {
            $practiceExam = PracticeExam::findOrFail($request->id);
            if ((intval($practiceExam->status) != PracticeExamStatusEnum::SUSPENDED->value) || (intval($practiceExam->status) != PracticeExamStatusEnum::NEW->value)) {
                return ResponseHelper::clientError(401);
                // لا يمكن حذف الاختبار اذا كانت حالته غير معلق او جديد 
            }
            $practiceExam->delete();
            return ResponseHelper::success();
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function retrievePracticeExams(Request $request)
    {
        Gate::authorize('retrievePracticeExams', PracticeExamController::class);
        /**
         * parameters:  
         * request {department_course_part_id, status_id? }
         * return: [id, course name, course part name, title, datetime, status name?, appreciation?, score rate?]
         */
        $userId = auth()->user()->id;
        try {
            $practiceExams =  DB::table('practice_exams')
                ->join('department_course_parts', 'practice_exams.department_course_part_id', '=', 'department_course_parts.id')
                ->join('department_courses', 'department_course_parts.department_course_id', '=', 'department_courses.id')
                ->join('courses', 'department_courses.course_id', '=', 'courses.id')
                ->join('course_parts', 'department_course_parts.course_part_id', '=', 'course_parts.id')
                ->select(
                    LanguageHelper::getNameColumnName('courses', 'course_name'),
                    'course_parts.part_id as course_part_name',
                    'practice_exams.id',
                    'practice_exams.title',
                    'practice_exams.datetime'
                )
                ->when(isset($request->status_id), function ($query) use ($request) {
                    return  $query->where('practice_exams.status', '=', $request->status_id);
                })
                // ->when($request->status_id === null, function ($query) {
                ->when(is_null($request->status_id), function ($query) {
                    return  $query->selectRaw('practice_exams.status as status_name');
                })
                ->where('practice_exams.department_course_part_id', '=', $request->department_course_part_id)
                ->where('practice_exams.user_id', '=', $userId)
                ->get();

            $enumReplacements = [
                new EnumReplacement('course_part_name', CoursePartsEnum::class),
            ];


            if (is_null($request->status_id)) {
                array_push($enumReplacements, new EnumReplacement('status_name', PracticeExamStatusEnum::class));
            }

            $practiceExams = ProcessDataHelper::enumsConvertIdToName($practiceExams, $enumReplacements);

            foreach ($practiceExams as $practiceExam) {
                $examResult = $this->getPracticeExamResult($practiceExam->id);

                if ($examResult !== null) {
                    $practiceExam->score_rate = $examResult['score_rate'];
                    $practiceExam->appreciation = $examResult['appreciation'];
                }
            }

            $practiceExams = NullHelper::filter($practiceExams);

            return ResponseHelper::successWithData($practiceExams);
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function retrievePracticeExamsAndroid(Request $request)
    {
        Gate::authorize('retrievePracticeExamsAndroid', PracticeExamController::class);

        /**
         * parameters:  
         * request {department_course_part_id?, status_id? }
         * return: [id, course name, course part name, title, datetime, status name, language name, appreciation?, score rate?]
         */

        $userId = auth()->user()->id;
        try {
            $practiceExams =  DB::table('practice_exams')
                ->join('department_course_parts', 'practice_exams.department_course_part_id', '=', 'department_course_parts.id')
                ->join('department_courses', 'department_course_parts.department_course_id', '=', 'department_courses.id')
                ->join('courses', 'department_courses.course_id', '=', 'courses.id')
                ->join('course_parts', 'department_course_parts.course_part_id', '=', 'course_parts.id')
                ->select(
                    LanguageHelper::getNameColumnName('courses', 'course_name'),
                    'course_parts.part_id as course_part_name',
                    'practice_exams.id',
                    'practice_exams.title',
                    'practice_exams.language as language_name',
                    'practice_exams.datetime'
                )
                ->when(isset($request->status_id), function ($query) use ($request) {
                    return  $query->where('practice_exams.status', '=', $request->status_id);
                })
                ->when(is_null($request->status_id), function ($query) {
                    return  $query->selectRaw('practice_exams.status as status_name');
                })
                ->when(isset($request->department_course_part_id), function ($query) use ($request) {
                    return  $query->where('practice_exams.department_course_part_id', '=', $request->department_course_part_id);
                })
                ->where('practice_exams.user_id', '=', $userId)
                ->get();


            $enumReplacements = [
                new EnumReplacement('course_part_name', CoursePartsEnum::class),
                new EnumReplacement('language_name', LanguageEnum::class),
            ];

            if (is_null($request->status_id)) {
                array_push($enumReplacements, new EnumReplacement('status_name', PracticeExamStatusEnum::class));
            }

            $practiceExams = ProcessDataHelper::enumsConvertIdToName($practiceExams, $enumReplacements);


            foreach ($practiceExams as $practiceExam) {
                $examResult = $this->getPracticeExamResult($practiceExam->id);

                if ($examResult !== null) {
                    $practiceExam->score_rate = $examResult['score_rate'];
                    $practiceExam->appreciation = $examResult['appreciation'];
                }
            }

            $practiceExams = NullHelper::filter($practiceExams);

            return ResponseHelper::successWithData($practiceExams);
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function retrievePracticeExamQuestions(Request $request)
    {
        Gate::authorize('retrievePracticeExamQuestions', PracticeExamController::class);

        try {
            $practiceExam = PracticeExam::findOrFail($request->exam_id);

            $is_complete = (intval($practiceExam->status) === PracticeExamStatusEnum::COMPLETE->value) ? true : false;

            $questions = $this->getQuestions($request->exam_id, $is_complete);

            return ResponseHelper::successWithData($questions);
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    private function getQuestions($examId, $withAnswer = false)
    {
        // return questoin as [content, attachment, is_true, choices[content, attachment, is_true]]
        $questions = [];

        try {
            $examQuestions = PracticeExamQuestion::where('practice_exam_id', '=', $examId)->get();

            foreach ($examQuestions as $examQuestion) {
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

            if ($withAnswer) {
                $questions = array_map(function ($question) {
                    unset($question['id']);
                    return $question;
                }, $questions);
            }

            return $questions;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function retrievePracticeExamsResult(Request $request)
    {
        Gate::authorize('retrievePracticeExamsResult', PracticeExamController::class);
        try {
            $practiceExam = PracticeExam::findOrFail($request->id);
            if ($practiceExam->status === PracticeExamStatusEnum::COMPLETE->value) {
                //time spent, question average answer time, appreciation, score rate, correct answer count, incorrect answer count
            }
            return [];
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function retrievePracticeExam(Request $request)
    {
        Gate::authorize('retrievePracticeExam', PracticeExamController::class);

        try {
            $practiceExam = PracticeExam::findOrFail($request->id, [
                // 'datetime'
                'id', 'title', 'duration', 'language as language_name',
                'conduct_method as is_mandatory_question_sequence', 'status', 'department_course_part_id'
            ]);

            $practiceExam = ProcessDataHelper::enumsConvertIdToName($practiceExam, [
                new EnumReplacement('language_name', LanguageEnum::class)
            ]);

            $practiceExam->is_started = (intval($practiceExam->status) === PracticeExamStatusEnum::ACTIVE->value) ? true : false;
            $practiceExam->is_suspended = (intval($practiceExam->status) === PracticeExamStatusEnum::SUSPENDED->value) ? true : false;
            $practiceExam->is_complete = (intval($practiceExam->status) === PracticeExamStatusEnum::COMPLETE->value) ? true : false;

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

            $department = $departmentCourse->department()->first([LanguageHelper::getNameColumnName(null, 'department_name'), 'college_id']);

            $college = $department->college()->first([LanguageHelper::getNameColumnName(null, 'college_name')]);

            $course = $departmentCourse->course()->first([LanguageHelper::getNameColumnName(null, 'course_name')]);

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
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function retrieveEditablePracticeExam(Request $request)
    {
        Gate::authorize('retrieveEditablePracticeExam', PracticeExamController::class);

        try {
            $practiceExam = PracticeExam::findOrFail($request->id, ['title']);

            // $practiceExam = NullHelper::filter($practiceExam);

            return ResponseHelper::successWithData($practiceExam);
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function savePracticeExamQuestionAnswer(Request $request)
    {
        Gate::authorize('savePracticeExamQuestionAnswer', PracticeExamController::class);

        try {
            $questionType = Question::findOrFail($request->question_id, ['type']);

            $answerId = null;
            if (intval($questionType->type) === QuestionTypeEnum::TRUE_FALSE->value) {
                $answerId = ($request->is_true) ? TrueFalseAnswerEnum::TRUE->value : TrueFalseAnswerEnum::FALSE->value;
            } else {
                $answerId =  intval($request->choice_id);
            }

            $practiceExamQuestion = PracticeExamQuestion::where('practice_exam_id', $request->exam_id)
                ->where('question_id', $request->question_id)
                ->update([
                    'answer' =>  $request->is_true,
                    // 'answer_duration' => $request->answer_duration ?? null,
                ]);

            return ResponseHelper::success();
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function finishPracticeExam(Request $request)
    {
        Gate::authorize('finishPracticeExam', PracticeExamController::class);
        try {
            $practiceExam = PracticeExam::findOrFail($request->id);
            $practiceExam->update([
                'status' => PracticeExamStatusEnum::COMPLETE->value,
                // 'end_datetime' => now(),
            ]);
            return ResponseHelper::success();
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function suspendPracticeExam(Request $request)
    {
        Gate::authorize('suspendPracticeExam', PracticeExamController::class);
        try {
            $practiceExam = PracticeExam::findOrFail($request->id);

            if (intval($practiceExam->status) === PracticeExamStatusEnum::ACTIVE->value) {
                $practiceExam->update([
                    'status' => PracticeExamStatusEnum::SUSPENDED->value,
                ]);
                return $practiceExam;
                return ResponseHelper::success();
            } else {
                return ResponseHelper::clientError(401);
            }
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    /**
     ***** job: 
     * this function using for retrieve data that will use in algorithm 
     ***** parameters: 
     * request: Request 
     * 
     ***** return: 
     * algorithmData = { estimated_time, difficulty_level, forms_count,  
     *      question_types_and_questions_count [id, count], 
     *      question [id, type_id, difficulty_level, answer_time, topic_id, last_selection, selection_times]
     *  }
     */
    private function getAlgorithmData($request)
    {
        try {
            $accessabilityStatusIds = [
                AccessibilityStatusEnum::REALEXAM->value,
                AccessibilityStatusEnum::PRACTICE_REALEXAM->value,
            ];
            $algorithmData = ExamHelper::getAlgorithmData($request, $accessabilityStatusIds);
            return $algorithmData;
        } catch (\Exception $e) {
            throw $e;
        }
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
        try {
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
        } catch (\Exception $e) {
            throw $e;
        }
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
        try {
            $practiceExam = PracticeExam::findOrFail($practiceExamId);

            if ($practiceExam->status === PracticeExamStatusEnum::COMPLETE->value) {
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
                            $StudentScore++;
                        }
                    } else {
                        if (ExamHelper::checkChoicesQuestionAnswer($question, $examQuestion->answer, $examQuestion->combination_id)) {
                            $StudentScore++;
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
                return ResponseHelper::clientError(401);
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    private function getPracticeExamResult($practiceExamId)
    {
        try {
            $practiceExam = PracticeExam::findOrFail($practiceExamId);

            if ($practiceExam->status === PracticeExamStatusEnum::COMPLETE->value) {
                $examQuestions = $practiceExam->practice_exam_question()
                    ->get(['question_id', 'answer', 'combination_id']);

                $totalScoure = $examQuestions->count();

                $StudentScore = 0;

                foreach ($examQuestions as $examQuestion) {
                    $question = Question::findOrFail($examQuestion->question_id);

                    if (intval($question->type) === QuestionTypeEnum::TRUE_FALSE->value) {
                        if (ExamHelper::checkTrueFalseQuestionAnswer($question, $examQuestion->answer)) {
                            $StudentScore++;
                        }
                    } else {
                        if (ExamHelper::checkChoicesQuestionAnswer($question, $examQuestion->answer, $examQuestion->combination_id)) {
                            $StudentScore++;
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
                return null;
            }
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function rules(Request $request): array
    {
        /*
- practice exam table
	- title
	- language
	- duration
	- difficulty_level
	- conduct_method
	- status
	- department_course_part_id
	- user_id
- practice exam question
	- combination_id
	- answer
	- answer_duration
	- practice_exam_id
	- question_id

        */
        $rules = [
            // practice exam table
            'title' => 'nullable|string',
            'language_id' => ['required', new Enum(LanguageEnum::class)], // Assuming LanguageEnum holds valid values
            'duration' => 'required|integer',
            'difficulty_level_id' => ['required', new Enum(ExamDifficultyLevelEnum::class)],
            'conduct_method_id' => ['required', new Enum(ExamConductMethodEnum::class)],
            'department_course_part_id' => 'required|exists:department_course_parts,id',

            // practice exam questions
            'answer' => 'nullable|integer',
            'question_id' => 'nullable|exists:questions,id',

            // exam_question_types
            'questions_types' => 'required|array|min:1',
            'questions_types.*.type_id' => ['required', new Enum(QuestionTypeEnum::class)],
            'questions_types.*.questions_count' => 'required|integer',

            // topice
            'topics_ids'                => 'required|array|min:1',
            'topics_ids.*'              => 'required|integer|exists:topics,id'
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
