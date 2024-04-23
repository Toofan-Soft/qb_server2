<?php

namespace App\Http\Controllers;

use App\Models\Chapter;
use App\Helpers\AddHelper;
use App\Helpers\GetHelper;
use App\Models\CoursePart;
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
        return AddHelper::addModel($request, CoursePart::class,  $this->rules($request), 'chapters', $request->course_part_id);
    }

    public function modifyChapter(Request $request)
    {
        if( ValidateHelper::validateData($request, $this->rules($request))){
            return  ResponseHelper::clientError(401);
        }
        $chapter = Chapter::findOrFail($request->id);
        $chapter->update([
            'arabic_title' => $request->arabic_title ?? $chapter->arabic_title,
            'english_title' =>$request->english_title ?? $chapter->english_title,
            'status' => $request->status_id ?? $chapter->status,
            'description' => $request->description ??  $chapter->description,
        ]);
       return ResponseHelper::success();
    }

    public function deleteChapter(Request $request)
    {
        $chapter = Chapter::findeOrFail( $request->id);
       return DeleteHelper::deleteModel($chapter);
    }

    
    public function retrieveChapters(Request $request)
    {
        $attributes = ['id', 'arabic_title', 'english_title', 'status as status_name', 'description'];
        $conditionAttribute = ['course_part_id'=> $request->course_part_id];
        $enumReplacements = [
            new EnumReplacement('status_name', ChapterStatusEnum::class),
          ];
        return GetHelper::retrieveModels(Chapter::class, $attributes, $conditionAttribute, $enumReplacements );

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
        $attributes = ['arabic_title', 'english_title', 'status as status_name', 'description'];
        $conditionAttribute = ['id' => $request->id];
        $enumReplacements = [
            new EnumReplacement('status_name', ChapterStatusEnum::class),
        ];
        return GetHelper::retrieveModels(Chapter::class, $attributes, $conditionAttribute, $enumReplacements);

    }

    public function retrieveEditableChapter(Request $request)
    {
        $attributes = ['arabic_title', 'english_title', 'status as status_id', 'description'];
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
            'status_id' =>[ new Enum (ChapterStatusEnum::class)],
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
