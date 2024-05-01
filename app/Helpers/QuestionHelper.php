<?php

namespace App\Helpers;

use App\Models\Choice;
use App\Models\Question;
use App\Models\RealExam;
use Illuminate\Http\Request;
use GuzzleHttp\Psr7\Response;
use App\Enums\ChoiceStatusEnum;
use App\Enums\QuestionTypeEnum;
use App\Models\TrueFalseQuestion;
use Illuminate\Http\UploadedFile;
use App\Enums\TrueFalseAnswerEnum;
use Illuminate\Support\Facades\Storage;
use App\Models\QuestionChoiceCombination;
use App\AlgorithmAPI\GenerateQuestionChoicesCombination;

class QuestionHelper
{
    /**
     * generate question choices combination by call algorithm api .
     */
    public static function generateQuestionChoicesCombination(Question $question){
        $algorithmData = $question->choices()->get(['id', 'status as is_true']);
        foreach ($algorithmData as $choice) {
            $choice['is_true'] = ($choice->is_true === ChoiceStatusEnum::CORRECT_ANSWER->value) ? true : false;
        }
        $questionChoicesCombination = (new GenerateQuestionChoicesCombination())->execute($algorithmData);

        // add question Choices Combination
        foreach ($questionChoicesCombination as $choiceCombination) {
            $question->question_choices_combinations()->create([
                'combination_choices' => $choiceCombination
            ]);
        }
        
        // return ResponseHelper::success();
    }

    /**
     * summation the score of exams .
     */
    public static function retrieveQuestionsAnswer($questions, $questionTypeId){

        foreach ($questions as $question) {
            if($questionTypeId === QuestionTypeEnum::TRUE_FALSE->value){
                $answer = TrueFalseQuestion::findOrFail($question->id, ['answer as is_true']);
                $question['is_true'] = ($answer === TrueFalseAnswerEnum::TRUE->value)? true: false;
            }elseif ($questionTypeId === QuestionTypeEnum::MULTIPLE_CHOICE->value) {
                $question['choices'] = self::retrieveCombinationChoices($question->id, $question->combination_id);
            }
        }
        return $questions;
    }

    private static function retrieveCombinationChoices($questionId, $combinationId){
/// id, content, attachment_url, is_true
    $combinationChoices = QuestionChoiceCombination::where('question_id', '=', $questionId)
                          ->where('combination_id', '=', $combinationId)
                          ->get(['combination_choices']);

    // convert combinationChoices from string to list, ','
    $combinationChoicesAsList = explode(',', $combinationChoices->combination_choices);
    $choices = [];
    foreach ($combinationChoicesAsList as $choiceId) {
        $choice = Choice::find($choiceId, ['id', 'content', 'attachment_url', 'status as is_true']);
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
    $combinationChoices = QuestionChoiceCombination::where('question_id', '=', $questionId)
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
