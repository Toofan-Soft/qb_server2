<?php

namespace App\Http\Controllers;

use App\Models\Topic;
use App\Models\Choice;
use App\Models\Question;
use App\Helpers\GetHelper;
use App\Enums\LanguageEnum;
use App\Helpers\NullHelper;
use App\Helpers\ImageHelper;
use Illuminate\Http\Request;
use App\Enums\ChoiceStatusEnum;
use App\Enums\QuestionTypeEnum;
use App\Helpers\QuestionHelper;
use App\Helpers\ResponseHelper;
use App\Helpers\ValidateHelper;
use App\Helpers\EnumReplacement;
use App\Enums\QuestionStatusEnum;
use App\Enums\TrueFalseAnswerEnum;
use App\Helpers\ProcessDataHelper;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rules\Enum;
use App\Enums\AccessibilityStatusEnum;
use App\Enums\ExamDifficultyLevelEnum;
use App\Models\QuestionChoicesCombination;

class QuestionController extends Controller
{
    public function addQuestion(Request $request)
    {
        if (ValidateHelper::validateData($request, $this->rules($request))) {
            return  ResponseHelper::clientError(401);
        }
        DB::beginTransaction();
        try {
            $topic = Topic::findOrFail($request->topic_id);
            $question =  $topic->questions()->create([
                'type' => $request->type_id,
                // 'difficulty_level' => $request->difficulty_level_id,
                'difficulty_level' => ExamDifficultyLevelEnum::toFloat($request->difficulty_level_id),
                'accessibility_status' => $request->accessibility_status_id,
                // 'accessability_status' => $request->accessibility_status_id,
                'language' => $request->language_id,
                'estimated_answer_time' => $request->estimated_answer_time,
                'content' => $request->content,
                'title' => $request->title ?? null,
                'attachment' => ImageHelper::uploadImage($request->attachment),
            ]);
            if (intval($question->type) === QuestionTypeEnum::TRUE_FALSE->value) {
                $question->true_false_question()->create([
                    'answer' => ($request->is_true) ? TrueFalseAnswerEnum::TRUE->value : TrueFalseAnswerEnum::FALSE->value,
                ]);
            }
            // else{
            //     if ($request->has('choices')) {
            //         foreach ($request->choices as $choice) {
            //             $question->choice()->create([
            //                 'content' => $choice['content'],
            //                 'attachment' => $choice['attachment'] ?? null,
            //                 'status' => $choice['is_true'] ,
            //             ]);
            //         }
            //     }
            // }
            DB::commit();
            return ResponseHelper::success();
        } catch (\Throwable $th) {
            DB::rollBack();
            // return  ResponseHelper::successWithData($th->getMessage());
            return ResponseHelper::serverError();
        }
    }

    public function modifyQuestion(Request $request, Question $question)
    {
        if (ValidateHelper::validateData($request, $this->rules($request))) {
            return  ResponseHelper::clientError(401);
        }
        try {
            $question = Question::findOrFail($request->id);
            $question->update([
                'difficulty_level' => ExamDifficultyLevelEnum::toFloat($request->difficulty_level_id) ?? $question->difficulty_level,
                'accessibility_status' => $request->accessibility_status_id ?? $question->accessibility_status,
                // 'accessability_status' => $request->accessibility_status_id ?? $question->accessability_status,
                'language' => $request->language_id ?? $question->language,
                'estimated_answer_time' => $request->estimated_answer_time ?? $question->estimated_answer_time,
                'content' => $request->content ?? $question->content,
                'title' => $request->title ?? $question->title,
                'attachment' => ImageHelper::updateImage($request->attachment, $question->attachment),
            ]);

            if (intval($question->type) === QuestionTypeEnum::TRUE_FALSE->value) {
                if ($request->has('is_true')) {
                    $question->true_false_question()->update([
                        'answer' => ($request->is_true === true) ? TrueFalseAnswerEnum::TRUE->value : TrueFalseAnswerEnum::FALSE->value,
                    ]);
                }
            }
            return ResponseHelper::success();
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function deleteQuestion(Request $request)
    {
        DB::beginTransaction();
        try {
            $question = Question::findOrFail($request->id);
            if (intval($question->status) === QuestionStatusEnum::ACCEPTED->value) {
                $question->question_usages()->delete();
            }

            if (intval($question->type) === TrueFalseAnswerEnum::TRUE->value) {
                $question->true_false_question()->delete();
            } else {
                QuestionChoicesCombination::where('question_id', '=', $question->id)->delete();
                Choice::where('question_id', '=', $question->id)->delete();
                //     $choices = $question->choices()->get(['id']);
                //      return DeleteHelper::deleteModels(Choice::class, $choices);
            }
            $question->delete();
            DB::commit();
            return ResponseHelper::success();
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseHelper::serverError();
        }
    }

    public function retrieveQuestions(Request $request)
    {
        $attributes = ['id', 'content'];
        $conditionAttribute = [
            'topic_id' => $request->topic_id,
        ];

        $enumReplacements  = [];

        if ($request->has('status_id')) {
            $conditionAttribute['status'] =  $request->status_id;
        } else {
            array_push($attributes, 'status as status_name');
            array_push($enumReplacements,  new EnumReplacement('status_name', QuestionStatusEnum::class));
        }

        if ($request->has('type_id')) {
            $conditionAttribute['type'] =  $request->type_id;
        } else {
            array_push($attributes, 'type as type_name');
            array_push($enumReplacements,  new EnumReplacement('type_name', QuestionTypeEnum::class));
        }
        try {
            $questions = GetHelper::retrieveModels(Question::class, $attributes, $conditionAttribute, $enumReplacements);
            return ResponseHelper::successWithData($questions);
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function retrieveQuestion(Request $request)
    {
        $attributes = [
            'id', 'type', 'difficulty_level as difficulty_level_name', 'status',
            'accessibility_status as accessibility_status_name',
            'language as language_name', 'estimated_answer_time', 'content',
            'attachment as attachment_url', 'title'
        ];
        try {
            $question = Question::findOrFail($request->id, $attributes);

            if (intval($question->type) === QuestionTypeEnum::TRUE_FALSE->value) {
                $trueFalseQuestion = $question->true_false_question()->first(['answer']);

                if (intval($trueFalseQuestion->answer) === TrueFalseAnswerEnum::TRUE->value) {
                    $question['is_true'] = true;
                } else {
                    $question['is_true'] = false;
                }
            } else {
                $choices = $question->choices()->get(['id', 'content', 'attachment as attachment_url', 'status as is_true']);
                foreach ($choices as $choice) {
                    if (intval($choice->is_true) === ChoiceStatusEnum::CORRECT_ANSWER->value) {
                        $choice['is_true'] = true;
                    } else {
                        $choice['is_true'] = false;
                    }
                }
                $question['choices'] = $choices;
            }
            unset($question['type']);
            unset($question['id']);

            $question = NullHelper::filter($question);
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

            $question->difficulty_level_name = ExamDifficultyLevelEnum::fromFloat($question->difficulty_level_name);

            $enumReplacements = [
                new EnumReplacement('difficulty_level_name', ExamDifficultyLevelEnum::class),
                new EnumReplacement('accessibility_status_name', AccessibilityStatusEnum::class),
                new EnumReplacement('language_name', LanguageEnum::class),
            ];

            $question = ProcessDataHelper::enumsConvertIdToName($question, $enumReplacements);
            $question['status'] = $status;

            return ResponseHelper::successWithData($question);
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function retrieveEditableQuestion(Request $request)
    {
        $attributes = [
            'id', 'type', 'difficulty_level as difficulty_level_id',
            'accessibility_status as accessibility_status_id',
            'language as language_id', 'estimated_answer_time', 'content',
            'attachment as attachment_url', 'title'
        ];
        try {
            $question = Question::findOrFail($request->id, $attributes);

            if (intval($question->type) === QuestionTypeEnum::TRUE_FALSE->value) {
                $trueFalseQuestion = $question->true_false_question()->get(['answer'])->first();

                if (intval($trueFalseQuestion->answer) === TrueFalseAnswerEnum::TRUE->value) {
                    $question['is_true'] = true;
                } else {
                    $question['is_true'] = false;
                }
            }
            unset($question['type']);
            unset($question['id']);
            $question = NullHelper::filter($question);
            return ResponseHelper::successWithData($question);
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function submitQuestionReviewRequest(Request $request)
    {
        try {
            $this->modifyQuestionStatus($request->id, QuestionStatusEnum::REQUESTED->value);
            return ResponseHelper::success();
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function acceptQuestion(Request $request)
    {

        DB::beginTransaction();
        try {
            // $this->modifyQuestionStatus($request->id, QuestionStatusEnum::ACCEPTED->value);
            
            $question = Question::findOrFail($request->id);
            // $question->question_usage()->create();
            
            if (intval($question->type) === QuestionTypeEnum::MULTIPLE_CHOICE->value) {
                return ResponseHelper::successWithData(QuestionHelper::generateQuestionChoicesCombination($question));
                QuestionHelper::generateQuestionChoicesCombination($question);
            }
            DB::commit();
            return ResponseHelper::success();
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseHelper::serverError();
        }
    }

    public function rejectQuestion(Request $request)
    {
        try {
            $this->modifyQuestionStatus($request->id, QuestionStatusEnum::REJECTED->value);
            return ResponseHelper::success();
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    private function modifyQuestionStatus($question_id, $status_id)
    {
        try {
            $question = Question::findOrFail($question_id);
            $question->update([
                'status' => $status_id
            ]);
        } catch (\Exception $e) {
            throw $e;
        }
    }


    public function rules(Request $request): array
    {
        $rules = [
            'topic_id' => 'required|exists:topics,id',
            'content' => 'required|string',
            'attachment' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'title' => 'nullable|string',
            'type_id' => ['required', new Enum(QuestionTypeEnum::class)], // Assuming QuestionTypeEnum holds valid values
            // 'difficulty_level_id' => 'required|float',
            'difficulty_level_id' => 'required',
            'status' => new Enum(QuestionStatusEnum::class), // Assuming QuestionStatusEnum holds valid values
            // 'accessability_status_id' => ['required', new Enum(AccessibilityStatusEnum::class)], // Assuming AccessibilityStatusEnum holds valid values
            'accessibility_status_id' => ['required', new Enum(AccessibilityStatusEnum::class)], // Assuming AccessibilityStatusEnum holds valid values
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
