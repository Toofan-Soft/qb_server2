<?php

namespace App\Http\Controllers;

use App\Models\Topic;
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
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\Enum;
use App\Enums\AccessibilityStatusEnum;
use App\Enums\ExamDifficultyLevelEnum;
use App\Helpers\BooleanHelper;
use App\Helpers\Roles\ByteArrayValidationRule;

class QuestionController extends Controller
{
    public function addQuestion(Request $request)
    {
        Gate::authorize('addQuestion', QuestionController::class);

        if (ValidateHelper::validateData($request, $this->rules($request))) {
            return  ResponseHelper::clientError();
        }
        DB::beginTransaction();
        try {

            $question =  Question::create([
                'topic_id' => $request->topic_id,
                'type' => $request->type_id,
                'difficulty_level' => ExamDifficultyLevelEnum::toFloat($request->difficulty_level_id),
                'accessibility_status' => $request->accessibility_status_id,
                'language' => $request->language_id,
                'estimated_answer_time' => $request->estimated_answer_time,
                'content' => $request->content,
                'title' => $request->title ?? null,
                'attachment' => ImageHelper::uploadImage($request->attachment),
            ]);
            if (intval($request->type_id) === QuestionTypeEnum::TRUE_FALSE->value) {
                if (NullHelper::is_null($request, ['is_true'])) {
                    // return ResponseHelper::clientError1("required 'is_true'!");
                    DB::rollBack();
                    return ResponseHelper::clientError();
                }

                $question->true_false_question()->create([
                    'answer' => BooleanHelper::toBoolean($request->is_true) ? TrueFalseAnswerEnum::TRUE->value : TrueFalseAnswerEnum::FALSE->value,
                ]);
            } elseif (intval($request->type_id) === QuestionTypeEnum::MULTIPLE_CHOICE->value) {
                if (NullHelper::is_null($request, ['choices'])) {
                    // return ResponseHelper::clientError1("required 'choices'!");
                    DB::rollBack();
                    return ResponseHelper::clientError();
                }

                foreach ($request->choices as $choice) {
                    $question->choices()->create([
                        'content' => $choice['content'],
                        'attachment' => !NullHelper::is_null($choice, ['attachment']) ? ImageHelper::uploadImage($choice['attachment']) : null,
                        'status' => BooleanHelper::toBoolean($choice['is_true']) ? ChoiceStatusEnum::CORRECT_ANSWER->value : ChoiceStatusEnum::INCORRECT_ANSWER->value
                    ]);
                }
            } else {
                // return ResponseHelper::clientError1("unknown 'type_id'!");
                DB::rollBack();
                return ResponseHelper::clientError();
            }
            DB::commit();
            return ResponseHelper::success();
        } catch (\Throwable $th) {
            DB::rollBack();
            return ResponseHelper::serverError();
        }
    }

    public function modifyQuestion(Request $request)
    {
        Gate::authorize('modifyQuestion', QuestionController::class);

        if (ValidateHelper::validateData($request, $this->rules($request))) {
            return  ResponseHelper::clientError();
        }
        // try {
        // $params = ParamHelper::getParams(
        //     $request,
        //     [
        //         new Param('difficulty_level_id', 'difficulty_level'),
        //         new Param('accessibility_status_id', 'accessibility_status'),
        //         new Param('language_id', 'language'),
        //         new Param('estimated_answer_time'),
        //         new Param('content'),
        //         new Param('attachment'),
        //         new Param('title')
        //     ]
        // );

        $question = Question::findOrFail($request->id);
        if (intval($question->status) === QuestionStatusEnum::ACCEPTED->value) {
            return ResponseHelper::clientError();
            // can not modify accepted question 
        }
        $question->update([
            'difficulty_level' => isset($request->difficulty_level_id) ? ExamDifficultyLevelEnum::toFloat($request->difficulty_level_id) : $question->difficulty_level,
            'accessibility_status' => $request->accessibility_status_id ?? $question->accessibility_status,
            'language' => $request->language_id ?? $question->language,
            'estimated_answer_time' => $request->estimated_answer_time ?? $question->estimated_answer_time,
            'content' => $request->content ?? $question->content,
            'title' => $request->title ?? $question->title,
            'attachment' => ImageHelper::updateImage($request->attachment, $question->attachment),
        ]);

        if (intval($question->type) === QuestionTypeEnum::TRUE_FALSE->value) {
            // $params = ParamHelper::getParams(
            //     $request,
            //     [
            //         new Param('is_true', 'answer')
            //     ]
            // );

            if (!NullHelper::is_null($request, ['is_true'])) {
                $question->true_false_question()->update([
                    'answer' => BooleanHelper::toBoolean($request->is_true) ? TrueFalseAnswerEnum::TRUE->value : TrueFalseAnswerEnum::FALSE->value,
                ]);
            }
        } elseif (intval($question->type) === QuestionTypeEnum::MULTIPLE_CHOICE->value) {
            // $params = ParamHelper::getParams(
            //     $request,
            //     [
            //         new Param('choices', '')
            //     ]
            // );

            if (!NullHelper::is_null($request, ['choices'])) {

                foreach ($request->choices as $choice) {
                    if (
                        !NullHelper::is_null($choice, ['id']) &&
                        NullHelper::is_null($choice, ['content']) &&
                        NullHelper::is_null($choice, ['is_true']) &&
                        NullHelper::is_null($choice, ['attachment'])
                    ) {
                        // delete choice..
                        $old_choice = $question->choices()->where('id', $choice['id'])->first();
                        $old_choice->delete();
                    } elseif (
                        !NullHelper::is_null($choice, ['id']) &&
                        (
                            !NullHelper::is_null($choice, ['content']) ||
                            !NullHelper::is_null($choice, ['is_true']) ||
                            !NullHelper::is_null($choice, ['attachment'])
                        )
                    ) {
                        // modify choice..
                        $old_choice = $question->choices()->where('id', $choice['id'])->first();
                        if (NullHelper::is_null($choice, ['is_true'])) {
                            $status = $old_choice->status;
                        } else {
                            $status = BooleanHelper::toBoolean($choice['is_true']) ? ChoiceStatusEnum::CORRECT_ANSWER->value : ChoiceStatusEnum::INCORRECT_ANSWER->value;
                        }
                        $old_choice->update([
                            'content' => $choice['content'] ?? $old_choice->content,
                            'attachment' => !NullHelper::is_null($choice, ['attachment']) ? ImageHelper::updateImage($choice['attachment'], $old_choice->attachment) : $old_choice->attachment,
                            'status' => $status
                        ]);
                    } elseif (
                        NullHelper::is_null($choice, ['id']) &&
                        !NullHelper::is_null($choice, ['content', 'is_true'])
                    ) {
                        // add choice..
                        $question->choices()->create([
                            'content' => $choice['content'],
                            'attachment' => !NullHelper::is_null($choice, ['attachment']) ? ImageHelper::uploadImage($choice['attachment']) : null,
                            'status' => BooleanHelper::toBoolean($choice['is_true']) ? ChoiceStatusEnum::CORRECT_ANSWER->value : ChoiceStatusEnum::INCORRECT_ANSWER->value
                        ]);
                    }
                }
            }
        }

        return ResponseHelper::success();
        // } catch (\Exception $e) {
        //     return ResponseHelper::serverError();
        // }
    }

    public function addQuestion1(Request $request)
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

    public function modifyQuestion1(Request $request, Question $question)
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
        Gate::authorize('deleteQuestion', QuestionController::class);
        if (ValidateHelper::validateData($request, [
            'id' => 'required|integer'
        ])) {
            return  ResponseHelper::clientError();
        }
        try {
            $question = Question::findOrFail($request->id);
            if ((intval($question->status) === QuestionStatusEnum::REQUESTED->value) ||
                (intval($question->status) === QuestionStatusEnum::ACCEPTED->value)
            ) {
                return  ResponseHelper::clientError();
                // can not delete requested or accepted question  
            }

            $question->delete();
            return ResponseHelper::success();
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function retrieveQuestions(Request $request)
    {
        Gate::authorize('retrieveQuestions', QuestionController::class);
        if (ValidateHelper::validateData($request, [
            'topic_id' => 'required|integer',
            'type_id' => ['nullable', new Enum(QuestionTypeEnum::class)],
            'status_id' => ['nullable', new Enum(QuestionStatusEnum::class)]
        ])) {
            return  ResponseHelper::clientError();
        }
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
        Gate::authorize('retrieveQuestion', QuestionController::class);
        if (ValidateHelper::validateData($request, [
            'id' => 'required|integer'
        ])) {
            return  ResponseHelper::clientError();
        }
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
                // $trueFalseQuestion =  $question->true_false_question()->get(['answer'])->first();

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
                $question['choices'] = $choices->toArray();
            }
            unset($question['type']);
            unset($question['id']);

            $question = NullHelper::filter($question);
            $status = [];

            if (intval($question->status) === QuestionStatusEnum::NEW->value) {
                $status  = [
                    'is_accepted' => null,
                    'is_requested' => false,
                ];
            } elseif (intval($question->status) ===  QuestionStatusEnum::REQUESTED->value) {
                $status  = [
                    'is_accepted' => null,
                    'is_requested' => true,
                ];
            } elseif (intval($question->status) === QuestionStatusEnum::ACCEPTED->value) {
                $status  = [
                    'is_accepted' => true,
                    'is_requested' => true,
                ];
            } else {
                $status  = [
                    'is_accepted' => false,
                    'is_requested' => true,
                ];
            }

            $question->difficulty_level_name = ExamDifficultyLevelEnum::fromFloat($question->difficulty_level_name);

            $enumReplacements = [
                new EnumReplacement('difficulty_level_name', ExamDifficultyLevelEnum::class),
                new EnumReplacement('accessibility_status_name', AccessibilityStatusEnum::class),
                new EnumReplacement('language_name', LanguageEnum::class),
            ];

            $question = ProcessDataHelper::enumsConvertIdToName($question, $enumReplacements);
            $question->status = $status;

            return ResponseHelper::successWithData($question);
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function retrieveEditableQuestion(Request $request)
    {
        Gate::authorize('retrieveEditableQuestion', QuestionController::class);
        if (ValidateHelper::validateData($request, [
            'id' => 'required|integer'
        ])) {
            return  ResponseHelper::clientError();
        }
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

            return ResponseHelper::successWithData($question);
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function submitQuestionReviewRequest(Request $request)
    {
        Gate::authorize('submitQuestionReviewRequest', QuestionController::class);
        if (ValidateHelper::validateData($request, [
            'id' => 'required|integer'
        ])) {
            return  ResponseHelper::clientError();
        }
        try {
            $question = Question::findOrFail($request->id);
            if (intval($question->status) != QuestionStatusEnum::NEW->value) {
                return  ResponseHelper::clientError();
            }

            $this->modifyQuestionStatus($question, QuestionStatusEnum::REQUESTED->value);
            return ResponseHelper::success();
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }
    public function withdrawSubmitQuestionReviewRequest(Request $request)
    {
        Gate::authorize('withdrawSubmitQuestionReviewRequest', QuestionController::class);
        if (ValidateHelper::validateData($request, [
            'id' => 'required|integer'
        ])) {
            return  ResponseHelper::clientError();
        }
        try {
            $question = Question::findOrFail($request->id);
            if (intval($question->status) != QuestionStatusEnum::REQUESTED->value) {
                return  ResponseHelper::clientError();
            }

            $this->modifyQuestionStatus($question, QuestionStatusEnum::NEW->value);
            return ResponseHelper::success();
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function acceptQuestion(Request $request)
    {
        Gate::authorize('acceptQuestion', QuestionController::class);
        if (ValidateHelper::validateData($request, [
            'id' => 'required|integer'
        ])) {
            return  ResponseHelper::clientError();
        }
        DB::beginTransaction();
        try {
            $question = Question::findOrFail($request->id);
            if (intval($question->status) != QuestionStatusEnum::REQUESTED->value) {
                DB::rollBack();
                return  ResponseHelper::clientError();
            }

            $this->modifyQuestionStatus($question, QuestionStatusEnum::ACCEPTED->value);

            $question->question_usage()->create();

            if (intval($question->type) === QuestionTypeEnum::MULTIPLE_CHOICE->value) {
                QuestionHelper::generateQuestionChoicesCombination($question);
            }

            DB::commit();
            return ResponseHelper::success();
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseHelper::serverError(400);
        }
    }

    public function rejectQuestion(Request $request)
    {
        Gate::authorize('rejectQuestion', QuestionController::class);
        if (ValidateHelper::validateData($request, [
            'id' => 'required|integer'
        ])) {
            return  ResponseHelper::clientError();
        }
        try {
            $question = Question::findOrFail($request->id);
            if (intval($question->status) != QuestionStatusEnum::REQUESTED->value) {
                return  ResponseHelper::clientError();
            }

            $this->modifyQuestionStatus($question, QuestionStatusEnum::REJECTED->value);

            return ResponseHelper::success();
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    private function modifyQuestionStatus(Question $question, $status_id)
    {
        try {
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
            'attachment' => ['nullable', new ByteArrayValidationRule],
            'title' => 'nullable|string',
            'type_id' => ['required', new Enum(QuestionTypeEnum::class)], // Assuming QuestionTypeEnum holds valid values
            'difficulty_level_id' => 'required|numeric',
            'status_id' => new Enum(QuestionStatusEnum::class), // Assuming QuestionStatusEnum holds valid values
            'accessibility_status_id' => ['required', new Enum(AccessibilityStatusEnum::class)], // Assuming AccessibilityStatusEnum holds valid values
            'estimated_answer_time' => 'required|integer',
            'language_id' => ['required', new Enum(LanguageEnum::class)],
            // 'is_true' => 'nullable|boolean',
            'is_true' => 'nullable',

            // choice rules 
            'choices' => 'nullable|array|min:4',
            'choices.*.attachment' => ['nullable', new ByteArrayValidationRule],
            'choices.*.content' => 'required|string',
            // 'choices.*.is_true' => 'required|boolean',
            'choices.*.is_true' => 'required',

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
