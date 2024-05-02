<?php

namespace App\Helpers;

use App\Models\Question;
use App\Models\RealExam;
use App\Models\QuestionUsage;
use App\Enums\RealExamTypeEnum;
use App\Enums\QuestionStatusEnum;
use Illuminate\Support\Facades\DB;
use App\Enums\AccessibilityStatusEnum;

class FadiHelper
{

    public static function getQuestionsChoicesCombinations($questions)
    {

        /**
         * in exam helper
         * used in
         *      lecturer online exam: add 
         *      paper exam: add
         *      practice exam: add
         * questions [question_id]
         * steps of function 
         *   اختيار الاسئلة التي نوعها اختيار من متعدد
         *   اختيار احد التوزيعات التي يمتلكها السؤال بشكل عشوائي
         *   يتم اضافة رقم التوزيعة المختارة الي السؤال 
         */

        return $questions;
    }

    public static function getAlgorithmData($request)
    {
        /**
         * in real exam helper
         * used in
         *      lecturer online exam: add 
         *      paper exam: add
         * 
         */
        $algorithmData = [
            'duration' => $request->duration,
            'language_id' => $request->language_id,
            'difficulty_level_id' => $request->difficulty_level_id,
            'forms_count' => $request->forms_count,
            'form_configuration_method_id' => $request->form_configuration_method_id,
            'questions_types' => $request->questions_types,
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

    public static function updateOnlineExamUsingQuestions($realExamId)
    {
        /**
         * in question usage helper
         * used in lecturer online exam: add 
         * 
         */
        $realExamQuestions =  DB::table('real_exams')
            ->join('forms', 'real_exams.id', '=', 'forms.real_exam_id')
            ->join('form_questions', 'forms.id', '=', 'form_questions.form_id')
            ->select('form_questions.question_id')
            ->where('real_exams.id', '=', $realExamId)
            ->distinct()
            ->get();

        foreach ($realExamQuestions as $question) {
            $questionUsage = QuestionUsage::findOrFail($question->question_id);
            $questionUsage->update([
                'online_exam_last_selection_datetime' => now(), // now or exam create date
                'online_exam_selection_times_count' => $questionUsage->online_exam_selection_times_count + 1
            ]);
        }
    }

    public static function updatePaperExamUsingQuestions($realExamId)
    {
        /**
         * in question usage helper
         * used in paper  exam: add 
         * 
         */
        $realExamQuestions =  DB::table('real_exams')
            ->join('forms', 'real_exams.id', '=', 'forms.real_exam_id')
            ->join('form_questions', 'forms.id', '=', 'form_questions.form_id')
            ->select('form_questions.question_id')
            ->where('real_exams.id', '=', $realExamId)
            ->distinct()
            ->get();

        foreach ($realExamQuestions as $question) {
            $questionUsage = QuestionUsage::findOrFail($question->question_id);
            $questionUsage->update([
                'paper_exam_last_selection_datetime' => now(), // now or exam create date
                'paper_exam_selection_times_count' => $questionUsage->paper_exam_selection_times_count + 1
            ]);
        }
    }

    // دراسة امكانية جعل الدالة تستقبل كائن من الكلاس اختبار تجريبي
    public static function updatePracticeExamUsingQuestions($practiceExamId)
    {
        /**
         * in question usage helper
         * used in paper  exam: add 
         * 
         */
        $practiceExamQuestions =  DB::table('practice_exams')
            ->join('practice_exam_questions', 'practice_exams.id', '=', 'practice_exam_questions.practice_exam_id')
            ->select('practice_exam_questions.question_id')
            ->where('practice_exams.id', '=', $practiceExamId)
            ->distinct()
            ->get();

        foreach ($practiceExamQuestions as $question) {
            $questionUsage = QuestionUsage::findOrFail($question->question_id);
            $questionUsage->update([
                'practice_exam_last_selection_datetime' => now(), // now or exam create date
                'practice_exam_selection_times_count' => $questionUsage->practice_exam_selection_times_count + 1
            ]);
        }
    }

    public static function increaseOnlineExamCorrectAnswersCount(Question $question)
    {
        /**
         * in question usage helper
         * used in student online exam: add 
         * 
         */
        $questionUsage = $question->question_usages();
        $questionUsage->update([
            'online_exam_correct_answers_count' => $questionUsage->online_exam_correct_answers_count + 1
        ]);
    }
    public static function decreaseOnlineExamCorrectAnswersCount(Question $question)
    {
        /**
         * in question usage helper
         * used in student online exam: add 
         * 
         */
        $questionUsage = $question->question_usages();
        $questionUsage->update([
            'online_exam_correct_answers_count' => $questionUsage->online_exam_correct_answers_count - 1
        ]);
    }

    public static function increaseOnlineExamIncorrectAnswersCount(Question $question)
    {
        /**
         * in question usage helper
         * used in student online exam: add 
         * 
         */
        $questionUsage = $question->question_usages();
        $questionUsage->update([
            'online_exam_incorrect_answers_count' => $questionUsage->online_exam_incorrect_answers_count + 1
        ]);
    }
    public static function decreaseOnlineExamIncorrectAnswersCount(Question $question)
    {
        /**
         * in question usage helper
         * used in student online exam: add 
         * 
         */
        $questionUsage = $question->question_usages();
        $questionUsage->update([
            'online_exam_incorrect_answers_count' => $questionUsage->online_exam_incorrect_answers_count - 1
        ]);
    }

    public static function increasePracticeExamCorrectAnswersCount(Question $question)
    {
        /**
         * in question usage helper
         * used in practice exam: add 
         * 
         */
        $questionUsage = $question->question_usages();
        $questionUsage->update([
            'practice_exam_correct_answers_count' => $questionUsage->practice_exam_correct_answers_count + 1
        ]);
    }
    public static function decreasePracticeExamCorrectAnswersCount(Question $question)
    {
        /**
         * in question usage helper
         * used in practice exam: add 
         * 
         */
        $questionUsage = $question->question_usages();
        $questionUsage->update([
            'practice_exam_correct_answers_count' => $questionUsage->practice_exam_correct_answers_count - 1
        ]);
    }
    public static function increasePracticeExamIncorrectAnswersCount(Question $question)
    {
        /**
         * in question usage helper
         * used in practice exam: add 
         * 
         */
        $questionUsage = $question->question_usages();
        $questionUsage->update([
            'practice_exam_incorrect_answers_count' => $questionUsage->practice_exam_incorrect_answers_count + 1
        ]);
    }
    public static function decreasePracticeExamIncorrectAnswersCount(Question $question)
    {
        /**
         * in question usage helper
         * used in practice exam: add 
         * 
         */
        $questionUsage = $question->question_usages();
        $questionUsage->update([
            'practice_exam_incorrect_answers_count' => $questionUsage->practice_exam_incorrect_answers_count - 1
        ]);
    }

    /**
     * delete real (paper, online) exam by id
     */
    public static function deleteRealExam($realExamId)
    {
          /**
           * in real exam helper
           * now existing in exam helper
           *  used in 
           * 
           */

        /**
         * using: delete paper or online exam
         * parameters: 
         *      realExamId: need deleting real exam id
         * 
         * return: ResponseHelper 
         */

         try{
            $realExam = RealExam::findOrFail($realExamId);
            $realExam->real_exam_question_types()->delete();
            $realExamForms = $realExam->forms();
            foreach ($realExamForms as $realExamForm) {
                $realExamForm->form_questions()->delete();
            }
            $realExam->forms()->delete();
            if ($realExam->exam_type === RealExamTypeEnum::PAPER->value) {

                $realExam->paper_exam()->delete();
            } else {
                $realExam->online_exam()->delete();
            }
            $realExam->delete();
            return ResponseHelper::success();
         } catch (\Exception $e) {
            return ResponseHelper::serverError();
            // return ResponseHelper::serverError('An error occurred while deleting models.');
        }
    }
    
    public static function getRealExamsScore($realExamId)
    {
          /**
           * in real exam helper
           * now existing in exam helper and online exam helper
           *  used in 
           * 
           */

        /**
         * using: calculate paper or online exam(s????) score 
         * parameters: 
         * 
         * return: ResponseHelper 
         */
    }

    public static function retrieveRealExamChapters($realExamId)
    {
          /**
           * in real exam helper
           * now existing in exam helper and 
           *  used in 
           * 
           */

        /**
         * using: calculate paper or online exam(s????) score 
         * parameters: 
         *      realExamId : 
         * 
         * return: ResponseHelper 
         */
    }

    public static function retrieveRealExamChapterTopics($realExamId, $chapterId)
    {
          /**
           * in real exam helper
           * now existing in exam helper and 
           *  used in 
           * 
           */

        /**
         * using: calculate paper or online exam(s????) score 
         * parameters: 
         *      realExamId : 
         *      chapterId : 
         * 
         * return: ResponseHelper 
         */
    }

    public static function retrieveRealExamForms($realExamId)
    {
          /**
           * in real exam helper
           * now existing in exam helper and 
           *  used in 
           * 
           */

        /**
         * using: calculate paper or online exam(s????) score 
         * parameters: 
         *      realExamId : 
         * 
         * return: ResponseHelper 
         */
    }
    public static function checkTrueFalseQuestionAnswer(Question $qeustion, $answer): bool
    {
          /**
           * in  exam helper
           * now existing in exam helper and 
           *  used in 
           * 
           */

        /**
         * using: calculate paper or online exam(s????) score 
         * parameters: 
         *      realExamId : 
         * 
         * return: ResponseHelper 
         */
        return true;
    }

    public static function checkChoicesQuestionAnswer(Question $qeustion, $answer, $combinationId): bool
    {
          /**
           * in  exam helper
           * now existing in exam helper and 
           *  used in 
           * 
           */

        /**
         * using: calculate paper or online exam(s????) score 
         * parameters: 
         *      realExamId : 
         * 
         * return: ResponseHelper 
         */
        return true;
    }
    public static function getExamResultAppreciation($scoreRate)
    {
          /**
           * in  exam helper
           * now existing in exam helper and 
           *  used in 
           * 
           */

        /**
         * using: calculate paper or online exam(s????) score 
         * parameters: 
         *      realExamId : 
         * 
         * return: ResponseHelper 
         */
    }

    

}
