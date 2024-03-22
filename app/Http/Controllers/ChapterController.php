<?php

namespace App\Http\Controllers;

use App\Models\Chapter;
use App\Helpers\AddHelper;
use App\Helpers\GetHelper;
use App\Models\CoursePart;
use Illuminate\Http\Request;
use App\Helpers\DeleteHelper;
use App\Helpers\ModifyHelper;
use App\Enums\ChapterStatusEnum;
use Illuminate\Validation\Rules\Enum;

class ChapterController extends Controller
{
    public function addChapter(Request $request)
    {
        return AddHelper::addModel($request, CoursePart::class,  $this->rules($request), 'chapters', $request->course_part_id);
    }

    public function modifyChapter(Request $request, Chapter $chapter)
    {
        return ModifyHelper::modifyModel($request, $chapter,  $this->rules($request));
    }

    public function deleteChapter(Chapter $chapter)
    {
       return DeleteHelper::deleteModel($chapter);
    }

    public function retrieveChapters(Request $request)
    {
        $attributes = ['id', 'arabic_title', 'english_title', 'status', 'description'];
        $conditionAttribute = ['course_part_id'=> $request->course_part_id];
        $enumAttributes = ['status'  => 'status_name'];
        return GetHelper::retrieveModelsWithEnum(Chapter::class, $attributes, $conditionAttribute, $enumAttributes, ChapterStatusEnum::class );

    }


    public function retrieveAvailableChapters(Request $request)
    {
        $attributes = ['id', 'arabic_title', 'english_title'];
        $conditionAttribute = [
            'course_part_id' => $request->course_part_id,
            'status' => ChapterStatusEnum::AVAILABLE->value,
        ];
        return GetHelper::retrieveModels(Chapter::class, $attributes, $conditionAttribute);
    }


    public function retrieveChapter(Request $request)
    {
        $attributes = ['arabic_title', 'english_title', 'status', 'description'];
        $conditionAttribute = ['id' => $request->id];
        return GetHelper::retrieveModels(Chapter::class, $attributes, $conditionAttribute);

    }
    public function retrieveChapterDescription(Request $request)
    {
        $attributes = ['description'];
        $conditionAttribute = ['id' => $request->id];
        return GetHelper::retrieveModels(Chapter::class, $attributes, $conditionAttribute);

    }


    public function rules(Request $request): array
    {

        $rules = [
            'arabic_title' => 'required|string|max:255',
            'english_title' => 'required|string|max:255',
            'status' => new Enum (ChapterStatusEnum::class), // Assuming ChapterStatusEnum is an enum class
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
