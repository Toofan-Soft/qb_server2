<?php

namespace App\Helpers;

use App\Models\RealExam;
use App\Models\PracticeExam;
use App\Models\QuestionUsage;
use App\Models\StudentAnswer;
use Illuminate\Support\Facades\DB;

class QuestionUsageHelper
{
    /**
     * using:
     * parameters:
     *
     * return:
     */
    public static function updateOnlineExamQuestionsUsageAndAnswer($examId)
    {
        DB::beginTransaction();
        try {
            $realExam = RealExam::findOrFail($examId);
            $forms =  $realExam->forms()->get();
            foreach ($forms as $form) {
                $formQuestions = $form->form_questions()->get();
                foreach ($formQuestions as $formQuestion) {
                    $questionUsage = QuestionUsage::where('question_id', '=', $formQuestion->question_id);
                    $questionUsage->update([
                        'online_exam_last_selection_datetime' => DatetimeHelper::now(),
                        'online_exam_selection_times_count' => $questionUsage->first()->online_exam_selection_times_count + 1
                    ]);
                    // $studentAnswers = $formQuestion->student_answers()->get();
                    $studentAnswers = StudentAnswer::where('form_id', $formQuestion->form_id)
                    ->where('question_id', $formQuestion->question_id)
                    ->get();
                    foreach ($studentAnswers as $studentAnswer) {
                        $answer = ExamHelper::checkQuestionAnswer(
                            $studentAnswer->question_id,
                            $studentAnswer->answer,
                            $formQuestion->combination_id,
                        );

                        if ($answer) {
                            $questionUsage->update([
                                'online_exam_correct_answers_count' => $questionUsage->first()->online_exam_correct_answers_count + 1
                            ]);
                        } else {
                            $questionUsage->update([
                                'online_exam_incorrect_answers_count' => $questionUsage->first()->online_exam_incorrect_answers_count + 1
                            ]);
                        }
                    }
                }
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
                'paper_exam_selection_times_count' => $questionUsage->first()->paper_exam_selection_times_count + 1
            ]);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public static function updatePracticeExamQuestionsUsage(PracticeExam $practiceExam)
    {
        DB::beginTransaction();
        try {
            $practiceExamQuestions = $practiceExam->practice_exam_question()->get();
            foreach ($practiceExamQuestions as $practiceExamQuestion) {
                $questionUsage = QuestionUsage::where('question_id', '=', $practiceExamQuestion->question_id);
                $questionUsage->update([
                    'practice_exam_last_selection_datetime' => DatetimeHelper::now(),
                    'practice_exam_selection_times_count' => $questionUsage->first()->practice_exam_selection_times_count + 1
                ]);
            }
            DB::commit();
        } catch (\Exception $e) {
            // return $e->getMessage();
            DB::rollBack();
            throw $e;
        }
    }

    public static function updatePracticeExamQuestionsUsageAndAnswer(PracticeExam $practiceExam)
    {
        // التحقق من ان الاجابة لا تحتوي على نل، وهذا يعني ان الطالب جاوب السؤال، مش خلي السؤال فاضي
        DB::beginTransaction();
        try {
            $practiceExamQuestions = $practiceExam->practice_exam_question()->get();
            foreach ($practiceExamQuestions as $practiceExamQuestion) {
                $answer = ExamHelper::checkQuestionAnswer(
                    $practiceExamQuestion->question_id,
                    $practiceExamQuestion->answer,
                    $practiceExamQuestion->combination_id
                );
                $questionUsage = QuestionUsage::where('question_id', '=', $practiceExamQuestion->question_id);
                if ($answer) {
                    $questionUsage->update([
                        'practice_exam_correct_answers_count' => $questionUsage->first()->practice_exam_correct_answers_count + 1
                    ]);
                } else {
                    $questionUsage->update([
                        'practice_exam_incorrect_answers_count' => $questionUsage->first()->practice_exam_incorrect_answers_count + 1
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
