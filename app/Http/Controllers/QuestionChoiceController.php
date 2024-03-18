<?php

namespace App\Http\Controllers;

use App\Enums\QuestionStatusEnum;
use App\Models\Choice;
use App\Models\Quesion;
use App\Models\Question;
use App\Helpers\AddHelper;
use App\Helpers\GetHelper;
use Illuminate\Http\Request;
use App\Helpers\DeleteHelper;
use App\Helpers\ModifyHelper;

class QuestionChoiceController extends Controller
{

    public function addQuestionChoice(Request $request)
    {
        return AddHelper::addModel($request, Question::class,  $this->rules($request), 'choices', $request->question_id);
    }

    public function modifyQuestionChoice(Request $request, Choice $choice)
    {
        return ModifyHelper::modifyModel($request, $choice,  $this->rules($request));
    }

    public function deleteQuestionChoice(Choice $choice)
    {
       return DeleteHelper::deleteModel($choice);
    }

    // public function retrieveQuestionChoices(Request $request)
    // {
    //     $attributes = ['id', 'content', 'attachment', 'status'];
    //     $conditionAttribute = ['id' => $request->id];
    //     return GetHelper::retrieveModels(Choice::class, $attributes, $conditionAttribute);
    // }

}
