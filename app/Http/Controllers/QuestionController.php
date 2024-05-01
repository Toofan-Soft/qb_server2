<?php

namespace App\Http\Controllers;

use App\Algorithm\QuestionChoices;
use App\AlgorithmAPI\GenerateQuestionChoicesCombination;
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
use App\Models\QuestionChoiceCombination;
use PhpParser\Node\Expr\Cast\String_;

class QuestionController extends Controller
{
    public function addQuestion(Request $request)
    {
        if (ValidateHelper::validateData($request, $this->rules($request))) {
            return  ResponseHelper::clientError(401);
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
            'attachment' => ImageHelper::uploadImage($request->attachment),
        ]);

        if ($question->type === QuestionTypeEnum::TRUE_FALSE->value) {
            $question->true_false_question()->create([
                'answer' => ($request->is_true) ? TrueFalseAnswerEnum::TRUE->value : TrueFalseAnswerEnum::FALSE->value,
            ]);
        }
        return ResponseHelper::success();
    }

    public function modifyQuestion(Request $request, Question $question)
    {
        if (ValidateHelper::validateData($request, $this->rules($request))) {
            return  ResponseHelper::clientError(401);
        }
        $question = Question::findOrFail($request->id);
        $question->update([
            'difficulty_level' => $request->difficulty_level_id ?? $question->difficulty_level,
            'accessbility_status' => $request->accessbility_status_id ?? $question->accessbility_status,
            'language' => $request->language_id ?? $question->language,
            'estimated_answer_time' => $request->estimated_answer_time ?? $question->estimated_answer_time,
            'content' => $request->content ?? $question->content,
            'title' => $request->title ?? $question->title,
            'attachment' => ImageHelper::updateImage($request->attachment, $question->attachment),
        ]);

        if ($question->type === QuestionTypeEnum::TRUE_FALSE->value) {
            if ($request->has('is_true')) {
                $question->true_false_question()->update([
                    'answer' => ($request->is_true) ? TrueFalseAnswerEnum::TRUE->value : TrueFalseAnswerEnum::FALSE->value,
                ]);
            }
        }
        return ResponseHelper::success();
    }

    public function deleteQuestion(Request $request)
    {
        $question = Question::findeOrFail($request->id);
        $question->question_usages()->delete();

        if ($question->type === TrueFalseAnswerEnum::TRUE->value) {
            $question->true_false_question()->delete();
        } else {
            QuestionChoiceCombination::where('question_id', '=', $question->id)->delete();
            Choice::where('question_id', '=', $question->id)->delete();
        }
        $question->delete();
        return ResponseHelper::success();

        //    $question = Question::findeOrFail( $request->id);

        //    if($question->type === TrueFalseAnswerEnum::TRUE->value ){
        //    return DeleteHelper::deleteModel($question->true_false_question());
        //    }else {
        //     $choices = $question->choices()->get(['id']);
        //      return DeleteHelper::deleteModels(Choice::class, $choices);
        //    }
        //    return DeleteHelper::deleteModel($question);

    }

    public function retrieveQuestions(Request $request)
    {
        $attributes = ['id', 'content'];
        $conditionAttribute = [
            'topic_id' => $request->topic_id,
        ];
        $enumReplacements  = [];
        if ($request->status_id && !$request->type_id) {
            array_push($attributes, 'type as type_name');
            $conditionAttribute['status'] =  $request->status_id;
            array_push($enumReplacements,  new EnumReplacement('type_name', QuestionTypeEnum::class));
        }
        if (!$request->status_id && $request->type_id) {
            array_push($attributes, 'status as status_name ');
            $conditionAttribute['type'] =  $request->type_id;
            array_push($enumReplacements,  new EnumReplacement('status_name', QuestionStatusEnum::class));
        }
        if (!$request->status_id && !$request->type_id) {
            array_push($attributes, 'status as status_name');
            array_push($attributes, 'type as type_name');
            array_push($enumReplacements,  new EnumReplacement('status_name', QuestionStatusEnum::class));
            array_push($enumReplacements,  new EnumReplacement('type_name', QuestionTypeEnum::class));
        }

        return GetHelper::retrieveModels(Question::class, $attributes, $conditionAttribute, $enumReplacements);
    }


    public function retrieveQuestion(Request $request)
    {
        //
        $attributes = [
            'type', 'difficulty_level as difficulty_level_name', 'status',
            'accessability_status as accessibility_status_name',
            'language as language_name', 'estimated_answer_time', 'content',
            'attachment as attachment_url', 'title'
        ];
        $question = Question::findOrFail($request->id, $attributes);

        if ($question->type === QuestionTypeEnum::TRUE_FALSE->value) {
            $trueFalseQuestion = $question->true_false_question()->get(['answer']);
            if ($trueFalseQuestion->answer === TrueFalseAnswerEnum::TRUE->value) {
                $question['is_true'] = true;
            } else {
                $question['is_true'] = false;
            }
        } else {
            $choices = $question->choices()->get(['id', 'content', 'attachment as attachment_url', 'status as is_true']);
            foreach ($choices as $choice) {
                if ($choice->is_true === ChoiceStatusEnum::CORRECT_ANSWER->value) {
                    $choice['is_true'] = true;
                } else {
                    $choice['is_true'] = false;
                }
            }
            $question['choices'] = $choices;
        }
        unset($question['type']);
        $status = [];

        if (intval($question->status) === QuestionStatusEnum::NEW->value) {
            $status  = [
                'is_accept' => null,
                'is_request' => false,
            ];
        } elseif (intval($question->status) ===  QuestionStatusEnum::REQUESTED->value) {
            $status  = [
                'is_accept' => null,
                'is_request' => true,
            ];
        } elseif (intval($question->status) === QuestionStatusEnum::ACCEPTED->value) {
            $status  = [
                'is_accept' => true,
                'is_request' => true,
            ];
        } else {
            $status  = [
                'is_accept' => false,
                'is_request' => true,
            ];
        }
        $enumReplacements = [
            new EnumReplacement('difficulty_level_name', ExamDifficultyLevelEnum::class),
            new EnumReplacement('accessibility_status_name', AccessibilityStatusEnum::class),
            new EnumReplacement('language_name', LanguageEnum::class),
        ];

        $question = ProcessDataHelper::enumsConvertIdToName($question, $enumReplacements);
        $question['status'] = $status;

        return ResponseHelper::successWithData($question);
    }

    public function retrieveEditableQuestion(Request $request)
    {
        $attributes = [
            'type', 'difficulty_level as difficulty_level_id',
            'accessability_status as accessibility_status_id',
            'language as language_id', 'estimated_answer_time', 'content',
            'attachment as attachment_url', 'title'
        ];
        $question = Question::findOrFail($request->id, $attributes);

        if (intval($question->type) === QuestionTypeEnum::TRUE_FALSE->value) {
            $trueFalseQuestion = $question->true_false_question()->get();
            if (intval($trueFalseQuestion->answer) === TrueFalseAnswerEnum::TRUE->value) {
                $question['is_true'] = true;
            } else {
                $question['is_true'] = false;
            }
        }
        unset($question['type']);
        return ResponseHelper::successWithData($question);
    }

    public function submitQuestionReviewRequest(Request $request)
    {
        self::modifyQuestionStatus($request->id, QuestionStatusEnum::REQUESTED->value);
        return ResponseHelper::success();
    }
    public function acceptQuestion(Request $request)
    {
        self::modifyQuestionStatus($request->id, QuestionStatusEnum::ACCEPTED->value);

        $question = Question::findOrFail($request->id);
        $question->question_usage()->create();

        if ($question->type === QuestionTypeEnum::MULTIPLE_CHOICE->value) {
            QuestionHelper::generateQuestionChoicesCombination($question);
        }

        return ResponseHelper::success();
    }

    public function rejectQuestion(Request $request)
    {
        self::modifyQuestionStatus($request->id, QuestionStatusEnum::REJECTED->value);
        return ResponseHelper::success();
    }

    private static function modifyQuestionStatus($question_id, $status_id)
    {
        $question = Question::findOrFail($question_id);
        $question::update([
            'status' => $status_id
        ]);
        // return ResponseHelper::success();
    }


    public function rules(Request $request): array
    {
        $rules = [
            'topic_id' => 'required|exists:topics,id',
            'content' => 'required|string',
            'attachment' => 'nullable|string',
            'title' => 'nullable|string',
            'type_id' => ['required', new Enum(QuestionTypeEnum::class)], // Assuming QuestionTypeEnum holds valid values
            'difficulty_level_id' => 'required|float',
            'status' => new Enum(QuestionStatusEnum::class), // Assuming QuestionStatusEnum holds valid values
            'accessbility_status_id' => ['required', new Enum(AccessibilityStatusEnum::class)], // Assuming AccessibilityStatusEnum holds valid values
            'estimated_answer_time' => 'required|integer',
            'language_id' => ['required', new Enum(LanguageEnum::class)],
            'is_true' => 'nullable',
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
