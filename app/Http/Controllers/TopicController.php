<?php

namespace App\Http\Controllers;

use App\Models\Topic;
use App\Models\Chapter;
use App\Helpers\AddHelper;
use App\Helpers\GetHelper;
use App\Helpers\NullHelper;
use Illuminate\Http\Request;
use App\Helpers\DeleteHelper;
use App\Helpers\ModifyHelper;
use App\Helpers\ResponseHelper;
use App\Helpers\ValidateHelper;

class TopicController extends Controller
{
    public function addTopic(Request $request)
    {
        try {
            return AddHelper::addModel($request, Chapter::class,  $this->rules($request), 'topics', $request->chapter_id);
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function modifyTopic(Request $request)
    {
        if (ValidateHelper::validateData($request, $this->rules($request))) {
            return  ResponseHelper::clientError(401);
        }
        try {
            $topic = Topic::findOrFail($request->id);
            $topic->update([
                'arabic_title' => $request->arabic_title ?? $topic->arabic_title,
                'english_title' => $request->english_title ?? $topic->english_title,
                'description' => $request->description ??  $topic->description,
            ]);
            return ResponseHelper::success();
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function deleteTopic(Request $request)
    {
        try {
            $topic = Topic::findOrFail($request->id);
            $topic->delete();
            return ResponseHelper::success();
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function retrieveTopics(Request $request)
    {
        try {
            $attributes = ['id', 'arabic_title', 'english_title', 'description'];
            $conditionAttribute = ['chapter_id' => $request->chapter_id];
            $topics = GetHelper::retrieveModels(Topic::class, $attributes, $conditionAttribute);
            $topics = NullHelper::filter($topics);
            return ResponseHelper::successWithData($topics);
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function retrieveTopic(Request $request)
    {
        try {
            $attributes = ['arabic_title', 'english_title', 'description'];
            $conditionAttribute = ['id' => $request->id];
            $topic = GetHelper::retrieveModel(Topic::class, $attributes, $conditionAttribute);
            $topic = NullHelper::filter($topic);
            return ResponseHelper::successWithData($topic);
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function retrieveEditableTopic(Request $request)
    {
        try {
            $attributes = ['arabic_title', 'english_title', 'description'];
            $conditionAttribute = ['id' => $request->id];
            $topic = GetHelper::retrieveModel(Topic::class, $attributes, $conditionAttribute);
            $topic = NullHelper::filter($topic);
            return ResponseHelper::successWithData($topic);
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function retrieveTopicDescription(Request $request)
    {
        try {
            $attributes = ['description'];
            $conditionAttribute = ['id' => $request->id];
            $topic = GetHelper::retrieveModels(Topic::class, $attributes, $conditionAttribute);
            $topic = NullHelper::filter($topic);
            return ResponseHelper::successWithData($topic);
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function retrieveAvailableTopics(Request $request)
    {
        try {
            $attributes = ['id', 'arabic_title', 'english_title'];
            $conditionAttribute = [
                'chapter_id' => $request->chapter_id,
            ];
            $topics = GetHelper::retrieveModels(Topic::class, $attributes, $conditionAttribute);
            return ResponseHelper::successWithData($topics);
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function rules(Request $request): array
    {
        $rules = [
            'arabic_title' => 'required|string|max:255',
            'english_title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'chapter_id' => 'required|exists:chapters,id'
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
