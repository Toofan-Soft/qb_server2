<?php

namespace App\Helpers;

use App\Models\Choice;
use App\Models\Question;
use App\Models\PracticeExam;
use App\Models\QuestionUsage;
use App\Enums\ChoiceStatusEnum;
use App\Enums\QuestionTypeEnum;
use App\Models\StudentOnlineExam;
use App\Models\TrueFalseQuestion;
use App\Enums\TrueFalseAnswerEnum;
use Illuminate\Support\Facades\DB;
use App\Models\QuestionChoicesCombination;
use App\AlgorithmAPI\GenerateQuestionChoicesCombination;
use App\Models\StudentAnswer;

class QuestionUsageHelper
{
    /**
     * using: 
     * parameters:
     *      
     * return:
     */
    public static function updateOnlineExamQuestionsUsage($examId)
    {
        try {
            DB::beginTransaction();

            $onlineExamQuestions =  DB::table('forms')
            ->join('form_questions', 'forms.id', '=', 'form_questions.form_id')
            ->select(
                'form_questions.combination_id',
                'form_questions.question_id'
            )
            ->where('forms.real_exam_id', '=', $examId)
            ->get();


            foreach ($onlineExamQuestions as $onlineExamQuestion) {
                $questionUsage = QuestionUsage::where('question_id', '=', $onlineExamQuestion->question_id);
                $questionUsage->update([
                    'online_exam_last_selection_datetime' => DatetimeHelper::now(),
                    'online_exam_selection_times_count' => $questionUsage->online_exam_selection_times_count + 1
                ]);
            }
            DB::commit();
        } catch (\Exception $e) {
            // return $e->getMessage();
            DB::rollBack();
            throw $e;
        }
    }

    public static function updatePaperExamQuestionUsage($questionId)
    {
        try {
            $questionUsage = QuestionUsage::where('question_id', '=', $questionId);
            $questionUsage->update([
                'paper_exam_last_selection_datetime' => DatetimeHelper::now(),
                'paper_exam_selection_times_count' => $questionUsage->paper_exam_selection_times_count + 1
            ]);
        } catch (\Exception $e) {
            // return $e->getMessage();
            throw $e;
        }
    }

    public static function updatePracticeExamQuestionsUsage(PracticeExam $practiceExam)
    {
        try {
            DB::beginTransaction();
            $practiceExamQuestions = $practiceExam->practice_exam_question();
            foreach ($practiceExamQuestions as $practiceExamQuestion) {
                $questionUsage = QuestionUsage::where('question_id', '=', $practiceExamQuestion->question_id);
                $questionUsage->update([
                    'practice_exam_last_selection_datetime' => DatetimeHelper::now(),
                    'practice_exam_selection_times_count' => $questionUsage->practice_exam_selection_times_count + 1
                ]);
            }
            DB::commit();
        } catch (\Exception $e) {
            // return $e->getMessage();
            DB::rollBack();
            throw $e;
        }
    }

    public static function updatePracticeExamQuestionsAnswerUsage(PracticeExam $practiceExam)
    {
        try {
            DB::beginTransaction();
            $practiceExamQuestions = $practiceExam->practice_exam_question();
            foreach ($practiceExamQuestions as $practiceExamQuestion) {
                $answer = ExamHelper::checkQuestionAnswer(
                    $practiceExamQuestion->question_id,
                    $practiceExamQuestion->answer,
                    $practiceExamQuestion->combination_id
                );
                $questionUsage = QuestionUsage::where('question_id', '=', $practiceExamQuestion->question_id);
                if ($answer) {
                    $questionUsage->update([
                        'practice_exam_correct_answers_count' => $questionUsage->practice_exam_correct_answers_count + 1
                    ]);
                } else {
                    $questionUsage->update([
                        'practice_exam_incorrect_answers_count' => $questionUsage->practice_exam_incorrect_answers_count + 1
                    ]);
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            // return $e->getMessage();
            DB::rollBack();
            throw $e;
        }
    }

    public static function updateOnlineExamQuestionsAnswerUsage(StudentOnlineExam $studentOnlineExam)
    {
        try {
            DB::beginTransaction();
            $studentQuestionAnswers = StudentAnswer::where('student_id', '=', $studentOnlineExam->student_id)
                ->where('form_id', '=', $studentOnlineExam->form_id)
                ->get();

            foreach ($studentQuestionAnswers as $studentQuestionAnswer) {
                $combination_choices = QuestionChoicesCombination::where('question_id', '=', $studentQuestionAnswer->student_id)
                    ->where('question_id', '=', $studentQuestionAnswer->form_question()->first(['combination_id'])['combination_id']);

                $answer = ExamHelper::checkQuestionAnswer(
                    $studentQuestionAnswer->question_id,
                    $studentQuestionAnswer->answer,
                    $combination_choices
                );

                $questionUsage = QuestionUsage::where('question_id', '=', $studentQuestionAnswer->question_id);
                if ($answer) {
                    $questionUsage->update([
                        'online_exam_correct_answers_count' => $questionUsage->online_exam_correct_answers_count + 1
                    ]);
                } else {
                    $questionUsage->update([
                        'online_exam_incorrect_answers_count' => $questionUsage->online_exam_incorrect_answers_count + 1
                    ]);
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            // return $e->getMessage();
            DB::rollBack();
            throw $e;
        }
    }
}
