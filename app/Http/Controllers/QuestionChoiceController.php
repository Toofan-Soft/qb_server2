<?php

namespace App\Http\Controllers;

use App\Enums\ChoiceStatusEnum;
use App\Models\Choice;
use App\Models\Quesion;
use App\Models\Question;
use App\Helpers\AddHelper;
use App\Helpers\GetHelper;
use App\Helpers\ImageHelper;
use Illuminate\Http\Request;
use App\Helpers\DeleteHelper;
use App\Helpers\ModifyHelper;
use App\Enums\QuestionTypeEnum;
use App\Helpers\ResponseHelper;
use App\Helpers\ValidateHelper;
use App\Enums\QuestionStatusEnum;
use App\Enums\TrueFalseAnswerEnum;
use Illuminate\Routing\Controller;

class QuestionChoiceController extends Controller
{

    public function addQuestionChoice(Request $request)
    {
        if($failed = ValidateHelper::validateData($request, $this->rules($request))){
            return  ResponseHelper::clientError($failed);
        }
        $question = Question::findOrFail($request->question_id);
        $question =  $question->question_choices()->create([
            'content' => $request->content,
            'answer' => ($request->is_true ) ? ChoiceStatusEnum::CORRECT_ANSWER->value : ChoiceStatusEnum::INCORRECT_ANSWER->value,
            'attachment' => ImageHelper::uploadImage($request->attachment) ,
        ]);

       return ResponseHelper::success();
    }

    public function modifyQuestionChoice(Request $request)
    {
        if($failed = ValidateHelper::validateData($request, $this->rules($request))){
            return  ResponseHelper::clientError($failed);
        }

        $choice = Choice::findOrFail($request->id);
        if ($request->has('is_true')) {
            $choice->update([
                'content' => $request->content ??  $choice->content,
                'answer' => ($request->is_true) ? ChoiceStatusEnum::CORRECT_ANSWER->value : ChoiceStatusEnum::INCORRECT_ANSWER->value,
                'attachment' => ImageHelper::updateImage($request->attachment, $choice->attachment),
            ]);
        }else {
            $choice->update([
                'content' => $request->content ??  $choice->content,
                'attachment' => ImageHelper::updateImage($request->attachment, $choice->attachment),
            ]);
        }
        return ResponseHelper::success();
    }

    public function deleteQuestionChoice(Request $request)
    {
        $choice = Choice::findOrFail($request->id);
        return DeleteHelper::deleteModel($choice);
    }


    //****// rule ( name of status   convert into is_true in rule  )


    // public function retrieveQuestionChoices(Request $request)
    // {
    //     $attributes = ['id', 'content', 'attachment', 'status'];
    //     $conditionAttribute = ['id' => $request->id];
    //     return GetHelper::retrieveModels(Choice::class, $attributes, $conditionAttribute);
    // }

}
