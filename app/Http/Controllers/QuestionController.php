<?php

namespace App\Http\Controllers;

use App\Enums\ChoiceStatusEnum;
use App\Models\Topic;
use App\Models\Question;
use App\Helpers\AddHelper;
use App\Helpers\GetHelper;
use Illuminate\Http\Request;
use App\Helpers\DeleteHelper;
use App\Helpers\ModifyHelper;
use App\Enums\CoursePartsEnum;
use App\Enums\QuestionTypeEnum;
use App\Helpers\EnumReplacement;
use App\Enums\QuestionStatusEnum;
use App\Enums\TrueFalseAnswerEnum;
use App\Models\Choice;
use App\Models\TrueFalseQuestion;

class QuestionController extends Controller
{
    public function addQuestion(Request $request)
    {
        return AddHelper::addModel($request, Topic::class,  $this->rules($request), 'questions', $request->topic_id);
    }

    public function modifyQuestion(Request $request, Question $question)
    {
        return ModifyHelper::modifyModel($request, $question,  $this->rules($request));
    }

    public function deleteQuestion(Question $question)
    {
       return DeleteHelper::deleteModel($question);
    }

    public function retrieveQuestions(Request $request) //////////////////////*********** More condition needed
    {
        $attributes = ['id', 'content'];
        $conditionAttribute = [
            'topic_id' => $request->topic_id,
        ];
        $enumReplacements  =[];
        if ($request->status_id && !$request->type_id) {
            array_push($attributes, 'type');
            $conditionAttribute['status'] =  $request->status_id ;
            array_push($enumReplacements,  new EnumReplacement('status', 'status_name', QuestionStatusEnum::class));
        }
        if (!$request->status_id && $request->type_id) {
            array_push($attributes, 'status');
            $conditionAttribute['type'] =  $request->type_id ;
            array_push($enumReplacements,  new EnumReplacement('type', 'type_name', QuestionTypeEnum::class));
        }
        if (!$request->status_id && !$request->type_id) {
            array_push($attributes, 'status');
            array_push($attributes, 'type');
            array_push($enumReplacements,  new EnumReplacement('status', 'status_name', QuestionStatusEnum::class));
            array_push($enumReplacements,  new EnumReplacement('type', 'type_name', QuestionTypeEnum::class));
        }

        // not completed
        return GetHelper::retrieveModelsWithEnum(Question::class, $attributes, $conditionAttribute, $enumReplacements );
    }


    public function retrieveQuestion(Request $request)
    {
          //
       // $attributes = [ 'difficulty_level', 'status', 'accessibility_status', 'language', 'estimated_answer_time', 'content', 'attachment', 'title', 'answer'];
        $question = Question::findOrFail($request->id);
        if($question->type === QuestionTypeEnum::TRUE_FALSE->value){
            $trueFalseQuestion = TrueFalseQuestion::Where('question_id', $request->id)->get(['answer']);
            if($trueFalseQuestion->answer === TrueFalseAnswerEnum::TRUE->value){
                $question['is_true'] = true;
            }else {
                $question['is_true'] = false;
            }
        }else {
            $choices = $question->choices()->get( ['id', 'content', 'attachment', 'status']);
            foreach ($choices as $choice) {
                if($choices->status === ChoiceStatusEnum::CORRECT_ANSWER->value){
                    $choices['is_true'] = true;
                }else {
                    $choices['is_true'] = false;
                }
            }
            unset($choices['status']);
            $question['choices'] = $choices;
        }
        unset($question['id']);
        unset($question['type']);
        $status=[];
        if ($question->status === QuestionStatusEnum::NEW->value) {
           $status  = [
                  'is_accept' => null,
                  'is_request' => false,
            ];
        } elseif ($question->status === QuestionStatusEnum::REQUESTED->value) {
            $status  = [
                   'is_accept' => null,
                   'is_request' => true,
             ];
         }
        elseif ($question->status === QuestionStatusEnum::ACCEPTED->value) {
           $status  = [
                  'is_accept' => true,
                  'is_request' => true,
            ];
        }else {
            $status  = [
                'is_accept' => false,
                'is_request' => true,
          ];
        }
        $question['status'] = $status;
        return $question;

        // $conditionAttribute = ['id' => $request->id];
        // return GetHelper::retrieveModels(Question::class, $attributes, $conditionAttribute);
    }


    public function submitQuestionReviewRequest(Question $question)
    {
        return ModifyHelper::modifyAttribute($question, 'status', QuestionStatusEnum::REQUESTED->value);
    }
    public function acceptQuestion (Question $question)
    {
        return ModifyHelper::modifyAttribute($question, 'status', QuestionStatusEnum::ACCEPTED->value);
    }
    public function rejectQuestion (Question $question)
    {
        return ModifyHelper::modifyAttribute($question, 'status', QuestionStatusEnum::REJECTED->value);
    }

    public function rules(Request $request): array
    {
        $rules = [
            // 'arabic_title' => 'required|string|max:255',
            // 'english_title' => 'required|string|max:255',
            // 'logo_url' =>  'image|mimes:jpeg,png,jpg,gif|max:2048',
            // 'levels_count' =>  new Enum(LevelsCountEnum::class),
            // 'description' => 'nullable|string',
            // 'college_id' => 'required',
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
