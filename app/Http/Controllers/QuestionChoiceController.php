<?php

namespace App\Http\Controllers;

use App\Models\Choice;
use App\Models\Quesion;
use App\Models\Question;
use App\Helpers\AddHelper;
use App\Helpers\GetHelper;
use App\Helpers\NullHelper;
use App\Helpers\ImageHelper;
use Illuminate\Http\Request;
use App\Helpers\DeleteHelper;
use App\Helpers\ModifyHelper;
use App\Enums\ChoiceStatusEnum;
use App\Enums\QuestionTypeEnum;
use App\Helpers\QuestionHelper;
use App\Helpers\ResponseHelper;
use App\Helpers\ValidateHelper;
use App\Enums\QuestionStatusEnum;
use App\Algorithm\QuestionChoices;
use App\Enums\TrueFalseAnswerEnum;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class QuestionChoiceController extends Controller
{

    public function addQuestionChoice(Request $request)
    {

        if (ValidateHelper::validateData($request, $this->rules($request))) {
            return  ResponseHelper::clientError(401);
        }
        DB::beginTransaction();
        try {
            $question = Question::findOrFail($request->question_id);
            $question =  $question->choices()->create([
                'content' => $request->content,
                'status' => ($request->is_true) ? ChoiceStatusEnum::CORRECT_ANSWER->value : ChoiceStatusEnum::INCORRECT_ANSWER->value,
                'attachment' => ImageHelper::uploadImage($request->attachment),
            ]);

            if (intval($question->status) === QuestionStatusEnum::ACCEPTED->value) {
                self::regenerateQuestionChoicesCombination($question);
            }
            DB::commit();
            return ResponseHelper::success();
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseHelper::serverError();
        }
    }

    public function modifyQuestionChoice(Request $request)
    {
        if (ValidateHelper::validateData($request, $this->rules($request))) {
            return  ResponseHelper::clientError(401);
        }
        DB::beginTransaction();
        try {
            $choice = Choice::findOrFail($request->id);
            if ($request->has('is_true')) {
                $choice->update([
                    'content' => $request->content ??  $choice->content,
                    'status' => ($request->is_true) ? ChoiceStatusEnum::CORRECT_ANSWER->value : ChoiceStatusEnum::INCORRECT_ANSWER->value,
                    'attachment' => ImageHelper::updateImage($request->attachment, $choice->attachment),
                ]);
                $question = $choice->question()->first();
                if (intval($question->status) === QuestionStatusEnum::ACCEPTED->value) {
                    self::regenerateQuestionChoicesCombination($question);
                }
            } else {
                $choice->update([
                    'content' => $request->content ??  $choice->content,
                    'attachment' => ImageHelper::updateImage($request->attachment, $choice->attachment),
                ]);
            }
            DB::commit();
            return ResponseHelper::success();
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseHelper::serverError();
        }
    }

    public function deleteQuestionChoice(Request $request)
    {
        DB::beginTransaction();
        try {
            $choice = Choice::findOrFail($request->id);
            $question = $choice->question()->first();
            $choice->delete();
            if (intval($question->status) === QuestionStatusEnum::ACCEPTED->value) {
                self::regenerateQuestionChoicesCombination($question);
            }
            DB::commit();
            return ResponseHelper::success();
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseHelper::serverError();
        }
    }

    public function retrieveEditableQuestionChoice(Request $request)
    {
        $attributes = ['content', 'attachment as attachment_url', 'status as is_true'];
        try {
            $choice = Choice::findOrFail($request->id, $attributes);
            if (intval($choice->is_true) === ChoiceStatusEnum::CORRECT_ANSWER->value) {
                $choice['is_true'] = true;
            } else {
                $choice['is_true'] = false;
            }
            $choice = NullHelper::filter($choice);
            return ResponseHelper::successWithData($choice);
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    private static function regenerateQuestionChoicesCombination($question)
    {
        try {
            $question->question_choices_combinations()->delete();
            QuestionHelper::generateQuestionChoicesCombination($question);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function rules(Request $request): array
    {
        $rules = [
            'question_id' => 'required|exists:questions,id',
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
