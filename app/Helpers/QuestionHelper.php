<?php

namespace App\Helpers;

use App\Models\Choice;
use App\Models\Question;
use App\Enums\ChoiceStatusEnum;
use App\Enums\QuestionTypeEnum;
use App\Models\TrueFalseQuestion;
use App\Enums\TrueFalseAnswerEnum;
use App\Models\QuestionChoicesCombination;
use App\AlgorithmAPI\GenerateQuestionChoicesCombination;

class QuestionHelper
{
    /**
     * using: generate question choices combination by call algorithm api, and store output of algorithm in database
     * parameters:
     *      question : object from Question model class
     * return:
     */
    public static function generateQuestionChoicesCombination(Question $question){
        $algorithmData = $question->choices()->get(['id', 'status as isCorrect']);
        foreach ($algorithmData as $choice) {
            $choice['isCorrect'] = (intval($choice->isCorrect) === ChoiceStatusEnum::CORRECT_ANSWER->value) ? true : false;
        }
        $questionChoicesCombination = (new GenerateQuestionChoicesCombination())->execute($algorithmData);

        // // add question Choices Combination
        // foreach ($questionChoicesCombination as $choiceCombination) {
        //     $question->question_choices_combinations()->create([
        //         'combination_choices' => $choiceCombination
        //     ]);
        // }
        try {
            // $questionChoicesCombination = (new GenerateQuestionChoicesCombination())->execute($algorithmData);
            // add question Choices Combination
            // $i = 1;
            foreach ($questionChoicesCombination as $choiceCombination) {
                $question->question_choices_combinations()->create([
                    // 'combination_id' => $i,
                    'combination_choices' => $choiceCombination
                ]);
                // $i++;
            }
            // return ResponseHelper::success();
            return true;
        } catch (\Exception $e) {
            return $e->getMessage();
            // throw $e;
        }
    }

    /**
     * summation the score of exams .
     */
    public static function retrieveQuestionsAnswer($questions, $questionTypeId){

        foreach ($questions as $question) {
            if(intval($questionTypeId) === QuestionTypeEnum::TRUE_FALSE->value){
                $answer = TrueFalseQuestion::where('question_id', $question->id )->first(['answer as is_true']);
                $question->is_true = (intval($answer->is_true) === TrueFalseAnswerEnum::TRUE->value) ? true: false; //add element is_true
            }elseif (intval($questionTypeId) === QuestionTypeEnum::MULTIPLE_CHOICE->value) {
                $question->choices = self::retrieveCombinationChoices($question->id, $question->combination_id); //add element choices
            }
        }
        return $questions;
    }

    private static function retrieveCombinationChoices($questionId, $combinationId)
    {
        /// id, content, attachment_url, is_true
        $combinationChoices = QuestionChoicesCombination::where('question_id', $questionId)
            ->where('combination_id', $combinationId)
            ->first(['combination_choices']);
        
        return [$questionId, $combinationId];
        return $combinationChoices;

        $combinationChoicesAsList = array_map('intval', str_split($combinationChoices->combination_choices));
        $choices = [];
        foreach ($combinationChoicesAsList as $choiceId) {
            $choice = Choice::find($choiceId, ['id', 'content', 'attachment', 'status as is_true']);
            if ($choice) {
                // $choice->is_true =
                $choices = $choice;
            }
        }
        return  $choices;
    }

    public static function retrieveStudentExamQuestions($questions, $questionTypeId){

        foreach ($questions as $question) {
            if($questionTypeId === QuestionTypeEnum::MULTIPLE_CHOICE->value){
                $question['choices'] = self::retrieveStudentExamCombinationChoices($question->id, $question->combination_id);
            }
        }
        return $questions;
    }



    private static function retrieveStudentExamCombinationChoices($questionId, $combinationId){
    /// id, content, attachment_url
    $combinationChoices = QuestionChoicesCombination::where('question_id', '=', $questionId)
                          ->where('combination_id', '=', $combinationId)
                          ->get(['combination_choices']);
    // convert combinationChoices from string to list, ','
    $combinationChoicesAsList = explode(',', $combinationChoices->combination_choices);
    $choices = [];
    foreach ($combinationChoicesAsList as $choiceId) {
        $choice = Choice::find($choiceId, ['id', 'content', 'attachment_url']);
        if ($choice) {
            $choices = $choice;
        }
    }
        return  $choices;
    }
}
