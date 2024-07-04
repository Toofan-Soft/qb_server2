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
use Illuminate\Validation\Rules\Enum;

class ChapterController extends Controller
{
    public function addChapter(Request $request)
    {
        try {
            return AddHelper::addModel($request, CoursePart::class,  $this->rules($request), 'chapters', $request->course_part_id);
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function modifyChapter(Request $request)
    {
        if (ValidateHelper::validateData($request, $this->rules($request))) {
            return  ResponseHelper::clientError(401);
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
        try {
            $chapter = Chapter::findOrFail($request->id);
            $chapter->delete();
            //    return DeleteHelper::deleteModel($chapter);
            return ResponseHelper::success();
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function retrieveChapters(Request $request)
    {
        $attributes = ['id', 'arabic_title', 'english_title', 'status as status_name', 'description'];
        $conditionAttribute = ['course_part_id' => $request->course_part_id];
        $enumReplacements = [
            new EnumReplacement('status_name', ChapterStatusEnum::class),
        ];
        try {
            $chapters = GetHelper::retrieveModels(Chapter::class, $attributes, $conditionAttribute, $enumReplacements);
            
            $chapters = NullHelper::filter($chapters);

            return ResponseHelper::successWithData($chapters);
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }
    
    public function retrieveAvailableChapters(Request $request)
    {
        $attributes = ['id', 'arabic_title', 'english_title'];
        $conditionAttribute = [
            'course_part_id' => $request->course_part_id,
            'status' => ChapterStatusEnum::AVAILABLE->value,
        ];
        try {
            $chapters = GetHelper::retrieveModels(Chapter::class, $attributes, $conditionAttribute);
            return ResponseHelper::successWithData($chapters);
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function retrieveChapter(Request $request)
    {
        $attributes = ['arabic_title', 'english_title', 'status as status_name', 'description'];
        $conditionAttribute = ['id' => $request->id];
        $enumReplacements = [
            new EnumReplacement('status_name', ChapterStatusEnum::class),
        ];
        try {
            $chapter = GetHelper::retrieveModel(Chapter::class, $attributes, $conditionAttribute, $enumReplacements);

            $chapter = NullHelper::filter($chapter);
            
            return ResponseHelper::successWithData($chapter);
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function retrieveEditableChapter(Request $request)
    {
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
