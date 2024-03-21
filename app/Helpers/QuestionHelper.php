<?php

namespace App\Helpers;

use App\Models\RealExam;
use Illuminate\Http\Request;
use App\Enums\QuestionTypeEnum;
use App\Models\TrueFalseQuestion;
use Illuminate\Http\UploadedFile;
use App\Enums\TrueFalseAnswerEnum;
use Illuminate\Support\Facades\Storage;

class QuestionHelper
{
    /**
     * sumation the score of exams .
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
    private static function retrieveCombinationChoices($id, $combination_id){
/// id, content, attachment_url, is_true
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
    private static function retrieveStudentExamCombinationChoices($id, $combination_id){
/// id, content, attachment_url
        return [];
    }
}
