<?php

namespace App\Http\Controllers;

use App\Algorithm\QuestionChoices;
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
        if(ValidateHelper::validateData($request, $this->rules($request))){
            return  ResponseHelper::clientError(401);
        }
        $question = Question::findOrFail($request->question_id);
        $question =  $question->question_choices()->create([
            'content' => $request->content,
            // this answer or status ????????? answer not found in db
            'status' => ($request->is_true ) ? ChoiceStatusEnum::CORRECT_ANSWER->value : ChoiceStatusEnum::INCORRECT_ANSWER->value,
            'attachment' => ImageHelper::uploadImage($request->attachment) ,
        ]);

        QuestionChoices::regenerateQuestionChoicesCombination($question->id);

       return ResponseHelper::success();
    }

    public function modifyQuestionChoice(Request $request)
    {
        if(ValidateHelper::validateData($request, $this->rules($request))){
            return  ResponseHelper::clientError(401);
        }

        $choice = Choice::findOrFail($request->id);
        if ($request->has('is_true')) {
            $choice->update([
                'content' => $request->content ??  $choice->content,
                'status' => ($request->is_true) ? ChoiceStatusEnum::CORRECT_ANSWER->value : ChoiceStatusEnum::INCORRECT_ANSWER->value,
                'attachment' => ImageHelper::updateImage($request->attachment, $choice->attachment),
            ]);
            QuestionChoices::regenerateQuestionChoicesCombination($choice->question_id);
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
        $questionId = $choice->question_id;
        $choice->delete();
        QuestionChoices::regenerateQuestionChoicesCombination($questionId);
        return ResponseHelper::success();
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

    public function rules(Request $request): array
    {
        $rules = [
            'question_id' => 'required|exists:topics,id',
            'content' => 'required|string',
            'attachment' => 'nullable|string',
            'is_true' => 'required',
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
