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
            // this answer or status ????????? answer not found in db
            'status' => ($request->is_true ) ? ChoiceStatusEnum::CORRECT_ANSWER->value : ChoiceStatusEnum::INCORRECT_ANSWER->value,
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
                'status' => ($request->is_true) ? ChoiceStatusEnum::CORRECT_ANSWER->value : ChoiceStatusEnum::INCORRECT_ANSWER->value,
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

    public function retrieveEditableQuestionChoice(Request $request)
    {
        $attributes = ['content', 'attachment as attachment_url', 'status as is_true'];
        $choice = Choice::findOrFail($request->id, $attributes);
        if($choice->is_true === ChoiceStatusEnum::CORRECT_ANSWER->value){
            $choice['is_true'] = true;
        }else {
            $choice['is_true'] = false;
        }
        return ResponseHelper::successWithData($choice);
    }

    //****// rule ( name of status   convert into is_true in rule  )


    // public function retrieveQuestionChoices(Request $request)
    // {
    //     $attributes = ['id', 'content', 'attachment', 'status'];
    //     $conditionAttribute = ['id' => $request->id];
    //     return GetHelper::retrieveModels(Choice::class, $attributes, $conditionAttribute);
    // }

}
