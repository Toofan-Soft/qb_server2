<?php

namespace App\Http\Controllers;

use App\Models\Topic;
use App\Models\Chapter;
use App\Helpers\AddHelper;
use App\Helpers\GetHelper;
use Illuminate\Http\Request;
use App\Helpers\DeleteHelper;
use App\Helpers\ModifyHelper;
use App\Helpers\ResponseHelper;
use App\Helpers\ValidateHelper;

class TopicController extends Controller
{
    public function addTopic(Request $request)
    {
        return AddHelper::addModel($request, Chapter::class,  $this->rules($request), 'topics', $request->chapter_id);
    }

    public function modifyTopic(Request $request)
    {
        // return ModifyHelper::modifyModel($request, Topic::class,  $this->rules($request));
        if( ValidateHelper::validateData($request, $this->rules($request))){
            return  ResponseHelper::clientError(401);
        }
        $topic = Topic::findOrFail($request->id);
        $topic->update([
            'arabic_title' => $request->arabic_title ?? $topic->arabic_title,
            'english_title' =>$request->english_title ?? $topic->english_title,
            'description' => $request->description ??  $topic->description,
        ]);
       return ResponseHelper::success();
    }

    public function deleteTopic(Request $request)
    {
        $topic = Topic::findOrFail( $request->id);
       return DeleteHelper::deleteModel($topic);
    }

    public function retrieveTopics(Request $request)
    {
        $attributes = ['id', 'arabic_title', 'english_title', 'description'];
        $conditionAttribute = ['chapter_id' => $request->chapter_id];

        return GetHelper::retrieveModels(Topic::class, $attributes, $conditionAttribute );

    }


    public function retrieveAvailableTopics(Request $request)
    {
        $attributes = ['id', 'arabic_title', 'english_title'];
        $conditionAttribute = [
            'chapter_id' => $request->chapter_id,
        ];
        return GetHelper::retrieveModels(Topic::class, $attributes, $conditionAttribute);
    }


    public function retrieveTopic(Request $request)
    {
        $attributes = ['arabic_title', 'english_title', 'description'];
        $conditionAttribute = ['id' => $request->id];
        return GetHelper::retrieveModels(Topic::class, $attributes, $conditionAttribute);

    }
    public function retrieveTopicDescription(Request $request)
    {
        $attributes = ['description'];
        $conditionAttribute = ['id' => $request->id];
        return GetHelper::retrieveModels(Topic::class, $attributes, $conditionAttribute);

    }


    public function rules(Request $request): array
    {
        $rules = [
            'arabic_title' => 'required|string|max:255',
            'english_title' => 'required|string|max:255',
            'description' => 'nullable|string',
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
