<?php

namespace App\Http\Controllers;

use App\Models\Chapter;
use App\Helpers\AddHelper;
use App\Helpers\GetHelper;
use App\Models\CoursePart;
use App\Helpers\NullHelper;
use Illuminate\Http\Request;
use App\Helpers\DeleteHelper;
use App\Helpers\ModifyHelper;
use App\Helpers\ResponseHelper;
use App\Helpers\ValidateHelper;
use App\Enums\ChapterStatusEnum;
use App\Helpers\EnumReplacement;
use App\Helpers\EnumReplacement1;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\Enum;

class ChapterController extends Controller
{
    public function addChapter(Request $request)
    {
        Gate::authorize('addChapter', ChapterController::class);

        try {
            return AddHelper::addModel($request, CoursePart::class,  $this->rules($request), 'chapters', $request->course_part_id);
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function modifyChapter(Request $request)
    {
        Gate::authorize('modifyChapter', ChapterController::class);

        if (ValidateHelper::validateData($request, $this->rules($request))) {
            return  ResponseHelper::clientError();
        }

        try {
            $chapter = Chapter::findOrFail($request->id);
            $chapter->update([
                'arabic_title' => $request->arabic_title ?? $chapter->arabic_title,
                'english_title' => $request->english_title ?? $chapter->english_title,
                'status' => $request->status_id ?? $chapter->status,
                'description' => $request->description ??  $chapter->description,
            ]);
            return ResponseHelper::success();
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function deleteChapter(Request $request)
    {
        Gate::authorize('deleteChapter', ChapterController::class);
        try {
            $chapter = Chapter::findOrFail($request->id);
            $chapter->delete();
            return ResponseHelper::success();
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function retrieveChapters(Request $request)
    {
        Gate::authorize('retrieveChapters', ChapterController::class);
        try {
            $attributes = ['id', 'arabic_title', 'english_title', 'status as status_name', 'description'];
            $conditionAttribute = ['course_part_id' => $request->course_part_id];
            $enumReplacements = [
                new EnumReplacement('status_name', ChapterStatusEnum::class),
            ];
            $chapters = GetHelper::retrieveModels(Chapter::class, $attributes, $conditionAttribute, $enumReplacements);

            $chapters = NullHelper::filter($chapters);

            return ResponseHelper::successWithData($chapters);
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function retrieveAvailableChapters(Request $request)
    {
        Gate::authorize('retrieveAvailableChapters', ChapterController::class);
        try {
            $attributes = ['id', 'arabic_title', 'english_title'];
            $conditionAttribute = [
                'course_part_id' => $request->course_part_id,
                'status' => ChapterStatusEnum::AVAILABLE->value,
            ];
            $chapters = GetHelper::retrieveModels(Chapter::class, $attributes, $conditionAttribute);
            return ResponseHelper::successWithData($chapters);
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function retrieveChapter(Request $request)
    {
        Gate::authorize('retrieveChapter', ChapterController::class);
        try {
            $attributes = ['arabic_title', 'english_title', 'status as status_name', 'description'];
            $conditionAttribute = ['id' => $request->id];
            $enumReplacements = [
                new EnumReplacement('status_name', ChapterStatusEnum::class),
            ];
            $chapter = GetHelper::retrieveModel(Chapter::class, $attributes, $conditionAttribute, $enumReplacements);

            $chapter = NullHelper::filter($chapter);

            return ResponseHelper::successWithData($chapter);
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function retrieveEditableChapter(Request $request)
    {
        Gate::authorize('retrieveEditableChapter', ChapterController::class);
        $attributes = ['arabic_title', 'english_title', 'status as status_id', 'description'];
        $conditionAttribute = ['id' => $request->id];
        try {
            $chapter = GetHelper::retrieveModel(Chapter::class, $attributes, $conditionAttribute);
            $chapter = NullHelper::filter($chapter);
            return ResponseHelper::successWithData($chapter);
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function retrieveChapterDescription(Request $request)
    {
        Gate::authorize('retrieveChapterDescription', ChapterController::class);
        $attributes = ['description'];
        $conditionAttribute = ['id' => $request->id];
        try {
            $chapter = GetHelper::retrieveModel(Chapter::class, $attributes, $conditionAttribute);
            $chapter = NullHelper::filter($chapter);
            return ResponseHelper::successWithData($chapter);
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }


    public function rules(Request $request): array
    {
        $rules = [
            'arabic_title' => 'required|string|max:255',
            'english_title' => 'required|string|max:255',
            'status_id' => [new Enum(ChapterStatusEnum::class)],
            'description' => 'nullable|string',
            'course_part_id' => 'required|exists:course_parts,id',
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
