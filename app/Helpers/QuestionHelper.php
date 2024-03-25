<?php

namespace App\Helpers;

use App\Models\RealExam;
use Illuminate\Http\Request;
use App\Enums\QuestionTypeEnum;
use App\Models\TrueFalseQuestion;
use Illuminate\Http\UploadedFile;
use App\Enums\TrueFalseAnswerEnum;
use App\Models\Question;
use App\Models\QuestionChoiceCombination;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Storage;

class QuestionHelper
{
    /**
     * summation the score of exams .
     */
    public static function modifyQuestionStatus($question_id ,$status_id ){
        $question = Question::findOrFail($question_id);
        $question::update([
            'status' => $status_id
        ]);
        return ResponseHelper::success();
    }

    /**
     * summation the score of exams .
     */
    public static function retrieveQuestionsAnswer($questions, $questionTypeId){

        foreach ($questions as $question) {
            if($questionTypeId === QuestionTypeEnum::TRUE_FALSE->value){
                $answer = TrueFalseQuestion::find($question->id, ['answer as is_true']);
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
    ->where('combination_id', '=', $combinationId)->get(['combination_choices']);
    // convert combinationChoices from string to list, ','
    
        return [];
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
        return [];
    }
}
