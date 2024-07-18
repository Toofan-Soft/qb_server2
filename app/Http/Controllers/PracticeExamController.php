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
use App\Models\PracticeExamUsage;
use App\Models\TrueFalseQuestion;
use App\AlgorithmAPI\GenerateExam;
use App\Enums\TrueFalseAnswerEnum;
use App\Helpers\ProcessDataHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Enums\ExamConductMethodEnum;
use App\Helpers\QuestionUsageHelper;
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

            if (!is_null($examQuestions)) { // modify to use has function

                $user = User::findOrFail(auth()->user()->id);
                DB::beginTransaction();
                // return $user;
                $practiceExam = $user->practice_exams()->create([
                    'department_course_part_id' => $request->department_course_part_id,
                    'title' => $request->title ?? null,
                    'language' => $request->language_id,
                    'datetime' => DatetimeHelper::now(), // can make defult value in migration 
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
        // try {
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
                ->get()
                ->map(function ($exam) {
                    $exam->datetime = DatetimeHelper::convertDateTimeToLong($exam->datetime);
                    return $exam;
                });

            $enumReplacements = [
                new EnumReplacement('course_part_name', CoursePartsEnum::class),
            ];

            if (is_null($request->status_id)) {
                array_push($enumReplacements, new EnumReplacement('status_name', PracticeExamStatusEnum::class));
            }

            $practiceExams = ProcessDataHelper::enumsConvertIdToName($practiceExams, $enumReplacements);

            foreach ($practiceExams as $practiceExam) {
                $examResult = $this->getPracticeExamResult($practiceExam->id);

                if ($examResult != null) {
                    $practiceExam->score_rate = $examResult['score_rate'];
                    $practiceExam->appreciation = $examResult['appreciation'];
                }
            }

            $practiceExams = NullHelper::filter($practiceExams);

            return ResponseHelper::successWithData($practiceExams);
        // } catch (\Exception $e) {
        //     return ResponseHelper::serverError();
        // }
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
                    'practice_exams.datetime',
                    'practice_exams.status as status_name'
                )
                ->when(isset($request->status_id), function ($query) use ($request) {
                    return  $query->where('practice_exams.status', '=', $request->status_id);
                })
                // ->when(is_null($request->status_id), function ($query) {
                //     return  $query->selectRaw('practice_exams.status as status_name');
                // })
                ->when(isset($request->department_course_part_id), function ($query) use ($request) {
                    return  $query->where('practice_exams.department_course_part_id', '=', $request->department_course_part_id);
                })
                ->where('practice_exams.user_id', '=', $userId)
                ->get()
                ->map(function ($exam) {
                    $exam->datetime = DatetimeHelper::convertDateTimeToLong($exam->datetime);
                    return $exam;
                });

            $enumReplacements = [
                new EnumReplacement('course_part_name', CoursePartsEnum::class),
                new EnumReplacement('language_name', LanguageEnum::class),
                new EnumReplacement('status_name', PracticeExamStatusEnum::class)
            ];

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
            if ((intval($practiceExam->status) ===  PracticeExamStatusEnum::NEW->value) || (intval($practiceExam->status) ===  PracticeExamStatusEnum::SUSPENDED->value)) {
                return ResponseHelper::clientError();
            }
            $is_complete = (intval($practiceExam->status) === PracticeExamStatusEnum::COMPLETE->value) ? true : false;

            $questions = $this->getQuestions($request->exam_id, $is_complete, $practiceExam->language);

            // $questions = NullHelper::filter($questions);

            return ResponseHelper::successWithData($questions);
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    private function getQuestions($examId, $withAnswer = false, $language)
    {
        // return questoin as [content, attachment, is_true, choices[content, attachment, is_true]]
        $language = LanguageEnum::symbolOf($language);
        $questions = [];

        try {
            $examQuestions = PracticeExamQuestion::where('practice_exam_id', '=', $examId)->get();

            foreach ($examQuestions as $examQuestion) {
                $question = $examQuestion->question()->first(['id', 'content', 'attachment as attachment_url']);
                $answer = $examQuestion->answer;

                if ($examQuestion->combination_id) {
                    $question['choices'] = ExamHelper::retrieveCombinationChoices($examQuestion->question_id, $examQuestion->combination_id, true, $withAnswer, $language);
                    // if ($withAnswer) {
                    //     $question['choices'] = ExamHelper::retrieveCombinationChoices($examQuestion->question_id, $examQuestion->combination_id, true, true, $language);
                    // } else {
                    //     $question['choices'] = ExamHelper::retrieveCombinationChoices($examQuestion->question_id, $examQuestion->combination_id, true, false, $language);
                    // }

                    if (!is_null($answer)) {
                        $choices = $question->choices; // Retrieve the choices array

                        if (isset($choices['mixed'])) {
                            if ($choices['mixed']['id'] === $answer) {
                                $choices['mixed']['is_selected'] = true;
                            } else {
                                $choices['mixed']['choices'] = collect($choices['mixed']['choices'])->map(function ($choice) use ($answer) {
                                    if ($choice['id'] === $answer) {
                                        $choice['is_selected'] = true;
                                    }

                                    return $choice;
                                })->toArray();
                            }
                        }

                        if (isset($choices['unmixed'])) {
                            $choices['unmixed'] = collect($choices['unmixed'])->map(function ($choice) use ($answer) {
                                if ($choice['id'] === $answer) {
                                    $choice['is_selected'] = true;
                                }

                                return $choice;
                            })->toArray();
                        }

                        $question->choices = $choices; // Set the modified choices array back to the property
                    }
                } else {
                    if ($withAnswer) {
                        $trueFalseQuestion = TrueFalseQuestion::findOrFail($examQuestion->question_id)->first(['answer']);

                        if (intval($trueFalseQuestion->answer) === TrueFalseAnswerEnum::TRUE->value) {
                            $question['is_true'] = true;
                        } else {
                            $question['is_true'] = false;
                        }
                    }
                    if ($answer === TrueFalseAnswerEnum::TRUE->value) {
                        $question->user_answer = true;
                    } elseif ($answer === TrueFalseAnswerEnum::FALSE->value) {
                        $question->user_answer = false;
                    }
                }

                array_push($questions, $question);
            }

            if ($withAnswer) {
                $questions = array_map(function ($question) {
                    unset($question['id']);
                    return $question;
                }, $questions);

                $questions = collect($questions)->map(function ($question) {
                    $choices = $question->choices; // Retrieve the choices array

                    if (isset($choices['mixed'])) {
                        unset($choices['mixed']['id']);

                        $choices['mixed']['choices'] = collect($choices['mixed']['choices'])->map(function ($choice) {
                            unset($choice['id']);
                            return $choice;
                        })->toArray();
                    }

                    if (isset($choices['unmixed'])) {
                        $choices['unmixed'] = collect($choices['unmixed'])->map(function ($choice) {
                            unset($choice['id']);
                            return $choice;
                        })->toArray();
                    }

                    $question->choices = $choices; // Set the modified choices array back to the property
                    return $question;
                });
            }

            return $questions;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function retrievePracticeExamResult(Request $request)
    {
        //return : time spent, question average answer time, appreciation, score rate, correct answer count, incorrect answer count

        Gate::authorize('retrievePracticeExamsResult', PracticeExamController::class);
        try {
            $practiceExam = PracticeExam::findOrFail($request->id);

            if (intval($practiceExam->status) === PracticeExamStatusEnum::COMPLETE->value) {
                $examQuestions = $practiceExam->practice_exam_question()
                    ->get(['question_id', 'answer', 'combination_id']);
                // ->get(['question_id', 'answer', 'answer_duration', 'combination_id']);

                $totalScoure = $examQuestions->count();
                $timeSpent = $practiceExam->practice_exam_usage()->first(['remaining_duration'])->remaining_duration;

                $StudentScore = 0;

                foreach ($examQuestions as $examQuestion) {
                    // $timeSpent = $timeSpent + $examQuestion->answer_duration;

                    if (ExamHelper::checkQuestionAnswer($examQuestion->question_id, $examQuestion->answer, $examQuestion->combination_id)) {
                        $StudentScore++;
                    }
                }

                $scoreRate = $StudentScore / $totalScoure * 100;
                $appreciation = ExamHelper::getExamResultAppreciation($scoreRate);
                $questionAverageAnswerTime = $timeSpent / $totalScoure;
                // $incorrectAnswerCount = $examQuestions->count() - $StudentScore;
                $incorrectAnswerCount = $totalScoure - $StudentScore;

                $examResult = [
                    'time_spent' => $timeSpent,
                    'question_average_answer_time' => $questionAverageAnswerTime,
                    'correct_answer_count' => $StudentScore,
                    'incorrect_answer_count' => $incorrectAnswerCount,
                    'score_rate' => $scoreRate,
                    'appreciation' => $appreciation
                ];

                return $examResult;
            } else {
                return ResponseHelper::clientError();
            }
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function retrievePracticeExam(Request $request)
    {
        Gate::authorize('retrievePracticeExam', PracticeExamController::class);

        // try {
        $practiceExam = PracticeExam::findOrFail($request->id, [
            'datetime',
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

        if (intval($practiceExam->status) != PracticeExamStatusEnum::NEW->value) {
            $practiceExam->remaining_duration = $practiceExam->practice_exam_usage()->first(['remaining_duration']);
        }

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
        // } catch (\Exception $e) {
        //     return ResponseHelper::serverError();
        // }
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
            $practiceExam = PracticeExam::findOrFail($request->exam_id);
            if ((intval($practiceExam->status) ===  PracticeExamStatusEnum::NEW->value) || (intval($practiceExam->status) ===  PracticeExamStatusEnum::SUSPENDED->value)) {
                return ResponseHelper::clientError();
            }

            $questionType = Question::findOrFail($request->question_id, ['type']);

            $answerId = null;
            if (intval($questionType->type) === QuestionTypeEnum::TRUE_FALSE->value) {
                $answerId = ($request->is_true) ? TrueFalseAnswerEnum::TRUE->value : TrueFalseAnswerEnum::FALSE->value;
            } else {
                $answerId =  intval($request->choice_id);
            }

            // PracticeExamQuestion::where('practice_exam_id', $request->exam_id)
            //     ->where('question_id', $request->question_id)
            //     ->update([
            //         'answer' =>  $answerId,
            //         // 'answer' =>  $request->is_true,
            //         // 'answer_duration' => $request->answer_duration ?? null,
            //     ]);
            $practiceExam->practice_exam_question()->where('question_id', $request->question_id)
                ->update([
                    'answer' =>  $answerId,
                ]);

            return ResponseHelper::success();
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function startPracticeExam(Request $request)
    {
        Gate::authorize('startPracticeExam', PracticeExamController::class);

        try {
            $practiceExam = PracticeExam::findOrFail($request->id);
            if (intval($practiceExam->status) != PracticeExamStatusEnum::NEW->value) {
                return ResponseHelper::clientError();
            }

            DB::beginTransaction();
            $practiceExam->update([
                'status' => PracticeExamStatusEnum::ACTIVE->value
            ]);

            $practiceExam->practice_exam_usage()->create([
                'remaining_duration' => $practiceExam->duration,
                'start_datetime' => DatetimeHelper::now()
            ]);

            QuestionUsageHelper::updatePracticeExamQuestionsUsage($practiceExam);
            DB::commit();
            return ResponseHelper::success();
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseHelper::serverError();
        }
    }

    public function suspendPracticeExam(Request $request)
    {
        Gate::authorize('suspendPracticeExam', PracticeExamController::class);

        try {
            DB::beginTransaction();
            $practiceExam = PracticeExam::findOrFail($request->id);
            if ((intval($practiceExam->status) === PracticeExamStatusEnum::ACTIVE->value) && (intval($practiceExam->status) != PracticeExamStatusEnum::SUSPENDED->value)) {
                $practiceExam->update([
                    'status' => PracticeExamStatusEnum::SUSPENDED->value
                ]);

                $practiceExamUsage = $practiceExam->practice_exam_usage();

                $practiceExamUsage->update([
                    'remaining_duration' => $practiceExamUsage->first()->remaining_duration - DatetimeHelper::getDifferenceInSeconds(DatetimeHelper::now(), $practiceExamUsage->first()->start_datetime),
                    'last_suspended_datetime' => DatetimeHelper::now()
                ]);
                DB::commit();
                return ResponseHelper::success();
            } else {
                DB::rollBack();
                return ResponseHelper::clientError();
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseHelper::serverError();
        }
    }

    public function continuePracticeExam(Request $request)
    {
        Gate::authorize('continuePracticeExam', PracticeExamController::class);

        try {
            $practiceExam = PracticeExam::findOrFail($request->id);
            if (intval($practiceExam->status) != PracticeExamStatusEnum::SUSPENDED->value) {
                return ResponseHelper::clientError();
            }

            DB::beginTransaction();
            $practiceExam->update([
                'status' => PracticeExamStatusEnum::ACTIVE->value
            ]);

            $practiceExam->practice_exam_usage()->update([
                'start_datetime' => DatetimeHelper::now()
            ]);

            DB::commit();
            return ResponseHelper::success();
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseHelper::serverError();
        }
    }

    public function finishPracticeExam(Request $request)
    {
        Gate::authorize('finishPracticeExam', PracticeExamController::class);

        try {
            DB::beginTransaction();
            $practiceExam = PracticeExam::findOrFail($request->id);
            if ((intval($practiceExam->status) === PracticeExamStatusEnum::ACTIVE->value) && (intval($practiceExam->status) != PracticeExamStatusEnum::COMPLETE->value)) {
                $practiceExam->update([
                    'status' => PracticeExamStatusEnum::COMPLETE->value
                ]);

                $practiceExamUsage = $practiceExam->practice_exam_usage();

                $practiceExamUsage->update([
                    'remaining_duration' => $practiceExamUsage->first()->remaining_duration - DatetimeHelper::getDifferenceInSeconds(DatetimeHelper::now(), $practiceExamUsage->first()->start_datetime)
                ]);

                QuestionUsageHelper::updatePracticeExamQuestionsAnswerUsage($practiceExam);

                DB::commit();
                return ResponseHelper::success();
            } else {
                DB::rollBack();
                return ResponseHelper::clientError();
            }
        } catch (\Exception $e) {
            DB::rollBack();
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
                AccessibilityStatusEnum::PRACTICE_EXAM->value,
                AccessibilityStatusEnum::PRACTICE_AND_REAL_EXAM->value,
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


    private function getPracticeExamResult($practiceExamId)
    {
        try {
            $practiceExam = PracticeExam::findOrFail($practiceExamId);

            if (intval($practiceExam->status) === PracticeExamStatusEnum::COMPLETE->value) {
                $examQuestions = $practiceExam->practice_exam_question()
                    ->get(['question_id', 'answer', 'combination_id']);

                $totalScoure = $examQuestions->count();

                $StudentScore = 0;

                foreach ($examQuestions as $examQuestion) {
                    if (ExamHelper::checkQuestionAnswer($examQuestion->question_id, $examQuestion->answer, $examQuestion->combination_id)) {
                        $StudentScore++;
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
            throw $e;
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
