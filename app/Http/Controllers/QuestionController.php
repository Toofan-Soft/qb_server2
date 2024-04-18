<?php

namespace App\Http\Controllers;

use App\Models\Topic;
use App\Models\Choice;
use App\Models\College;
use App\Models\Question;
use App\Helpers\AddHelper;
use App\Helpers\GetHelper;
use App\Enums\LanguageEnum;
use App\Helpers\ImageHelper;
use Illuminate\Http\Request;
use App\Helpers\DeleteHelper;
use App\Helpers\ModifyHelper;
use App\Enums\CoursePartsEnum;
use App\Enums\LevelsCountEnum;
use App\Enums\ChoiceStatusEnum;
use App\Enums\QuestionTypeEnum;
use App\Helpers\QuestionHelper;
use App\Helpers\ResponseHelper;
use App\Helpers\ValidateHelper;
use App\Helpers\EnumReplacement;
use App\Enums\QuestionStatusEnum;
use App\Models\TrueFalseQuestion;
use App\Enums\TrueFalseAnswerEnum;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rules\Enum;
use App\Enums\AccessibilityStatusEnum;
use App\Enums\ExamDifficultyLevelEnum;
use App\Helpers\ProcessDataHelper;

class QuestionController extends Controller
{
    public function addQuestion(Request $request)
    {
        if($failed = ValidateHelper::validateData($request, $this->rules($request))){
            return  ResponseHelper::clientError($failed);
        }
        $topic = Topic::findOrFail($request->topic_id);
       $question =  $topic->questions()->create([
            'type' => $request->type_id,
            'difficulty_level' => $request->difficulty_level_id,
            'accessbility_status' => $request->accessbility_status_id,
            'language' => $request->language_id,
            'estimated_answer_time' => $request->estimated_answer_time,
            'content' => $request->content,
            'title' => $request->title ?? null,
            'attachment' => ImageHelper::uploadImage($request->attachment) ,
        ]);

        if($question->type === QuestionTypeEnum::TRUE_FALSE->value){
            $question->true_false_question()->create([
                'answer' => ($request->is_true ) ? TrueFalseAnswerEnum::TRUE->value : TrueFalseAnswerEnum::FALSE->value,
            ]);
        }
       return ResponseHelper::success();
    }

    public function modifyQuestion(Request $request, Question $question)
    {
        if($failed = ValidateHelper::validateData($request, $this->rules($request))){
            return  ResponseHelper::clientError($failed);
        }
        $question = Question::findOrFail($request->id);
        $question->update([
            'difficulty_level' => $request->difficulty_level_id ?? $question->difficulty_level,
            'accessbility_status' => $request->accessbility_status_id ?? $question->accessbility_status,
            'language' => $request->language_id ?? $question->language,
            'estimated_answer_time' => $request->estimated_answer_time ?? $question->estimated_answer_time,
            'content' => $request->content ?? $question->content,
            'title' => $request->title ?? $question->title,
            'attachment' => ImageHelper::updateImage($request->attachment, $question->attachment) ,
        ]);

        if($question->type === QuestionTypeEnum::TRUE_FALSE->value){
            if($request->has('is_true')){
                $question->true_false_question()->update([
                    'answer' => ($request->is_true) ? TrueFalseAnswerEnum::TRUE->value : TrueFalseAnswerEnum::FALSE->value,
                ]);
            }
        }
       return ResponseHelper::success();
    }

    public function deleteQuestion(Request $request)
    {
        // اذا كان نوع السؤال اختيارات يتم حذف التوزيعات التابعة له
        // مهما كان نوع السؤال يتم حذف جدول تاريخ استخدام السؤال

       $question = Question::findeOrFail( $request->id);
       if($question->type === TrueFalseAnswerEnum::TRUE->value ){
       return DeleteHelper::deleteModel($question->true_false_question());
       }else {
        $choices = $question->choices()->get(['id']);
         return DeleteHelper::deleteModels(Choice::class, $choices);
       }
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
            array_push($attributes, 'type as type_name');
            $conditionAttribute['status'] =  $request->status_id ;
            array_push($enumReplacements,  new EnumReplacement('status_name', QuestionStatusEnum::class));
        }
        if (!$request->status_id && $request->type_id) {
            array_push($attributes, 'status_name ');
            $conditionAttribute['type'] =  $request->type_id ;
            array_push($enumReplacements,  new EnumReplacement('type_name', QuestionTypeEnum::class));
        }
        if (!$request->status_id && !$request->type_id) {
            array_push($attributes, 'status status_name');
            array_push($attributes, 'type type_name');
            array_push($enumReplacements,  new EnumReplacement('status_name', QuestionStatusEnum::class));
            array_push($enumReplacements,  new EnumReplacement('type_name', QuestionTypeEnum::class));
        }

        return GetHelper::retrieveModels3(Question::class, $attributes, $conditionAttribute, $enumReplacements );
    }


    public function retrieveQuestion(Request $request)
    {
          //
        $attributes = [ 'type', 'difficulty_level as difficulty_level_name', 'status',
                       'accessibility_status as accessibility_status_name',
                       'language as language_name', 'estimated_answer_time', 'content',
                       'attachment as attachment_url', 'title'];
        $question = Question::findOrFail($request->id, $attributes);

        if($question->type === QuestionTypeEnum::TRUE_FALSE->value){
            $trueFalseQuestion = $question->true_false_question()->get(['answer']);
            if($trueFalseQuestion->answer === TrueFalseAnswerEnum::TRUE->value){
                $question['is_true'] = true;
            }else {
                $question['is_true'] = false;
            }

        }else {
            $choices = $question->choices()->get( ['id', 'content', 'attachment as attachment_url', 'status as is_true']);
            foreach ($choices as $choice) {
                if($choice->is_true === ChoiceStatusEnum::CORRECT_ANSWER->value){
                    $choice['is_true'] = true;
                }else {
                    $choice['is_true'] = false;
                }
            }
            $question['choices'] = $choices;
        }
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
        $enumReplacements = [
            new EnumReplacement('difficulty_level_name', ExamDifficultyLevelEnum::class),
            new EnumReplacement('accessibility_status_name', AccessibilityStatusEnum::class),
            new EnumReplacement('language_name', LanguageEnum::class),
        ];
        $question = ProcessDataHelper::enumsConvertIdToName($question, $enumReplacements);
        return ResponseHelper::successWithData($question);

    }

    public function retrieveEditableQuestion(Request $request)
    {
          // 
        $attributes = [ 'type', 'difficulty_level as difficulty_level_id',
                       'accessibility_status as accessibility_status_id',
                       'language as language_id', 'estimated_answer_time', 'content',
                       'attachment as attachment_url', 'title'];
        $question = Question::findOrFail($request->id, $attributes);

        if($question->type === QuestionTypeEnum::TRUE_FALSE->value){
            $trueFalseQuestion = $question->true_false_question()->get(['answer']);
            if($trueFalseQuestion->answer === TrueFalseAnswerEnum::TRUE->value){
                $question['is_true'] = true;
            }else {
                $question['is_true'] = false;
            }

        }
        unset($question['type']);
        return ResponseHelper::successWithData($question);
    }

    public function submitQuestionReviewRequest(Request $request)
    {
        return QuestionHelper::modifyQuestionStatus($request->id, QuestionStatusEnum::REQUESTED->value);
    }
    public function acceptQuestion(Request $request)
    {
        // يتم انشاء التوزيعات للسؤال اذا كان نوعه اختيارات 
        // يتم انشاء جدول تاريخ استخدام السؤال 
        return QuestionHelper::modifyQuestionStatus($request->id, QuestionStatusEnum::ACCEPTED->value);
    }
    public function rejectQuestion(Request $request)
    {
        return QuestionHelper::modifyQuestionStatus($request->id, QuestionStatusEnum::REJECTED->value);
    }


    public function rules(Request $request): array
    {
        $rules = [
            'content' => 'required|string',
            'attachment' => 'nullable|string',
            'title' => 'nullable|string',
            'type_id' => new Enum(QuestionTypeEnum::class), // Assuming QuestionTypeEnum holds valid values
            'difficulty_level_id' => 'required|float',
            'status' => new Enum(QuestionStatusEnum::class), // Assuming QuestionStatusEnum holds valid values
            'accessbility_status_id' => new Enum(AccessibilityStatusEnum::class), // Assuming AccessibilityStatusEnum holds valid values
            'estimated_answer_time' => 'required|integer',
            'language_id' => new Enum(LanguageEnum::class),
            'is_true' => 'nullable',
            //'topic_id' => 'required|exists:topics,id',
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
