<?php

namespace App\Http\Controllers;

use App\Models\Chapter;
use App\Models\CoursePart;
use Illuminate\Http\Request;
use App\Helpers\ResponseHelper;
use App\Helpers\ValidateHelper;
use App\Enums\ChapterStatusEnum;
use Illuminate\Support\Facades\DB;
use App\Models\DepartmentCoursePart;
use Illuminate\Support\Facades\Gate;

use function PHPUnit\Framework\isNull;

class DepartmentCoursePartChapterTopicController extends Controller
{
    public function modifyDepartmentCoursePartTopics(Request $request)
    {
        Gate::authorize('modifyDepartmentCoursePartTopics', DepartmentCoursePartChapterTopicController::class);
        if (ValidateHelper::validateData($request, [
            'department_course_part_id' => 'required|exists:department_course_parts,id',
            'topics_ids'                => 'required|array|min:1',
            'topics_ids.*'              => 'required|integer|exists:topics,id',
        ])) {
            return  ResponseHelper::clientError();
        }

        DB::beginTransaction();
        try {
            $departmentCoursePart = DepartmentCoursePart::findOrFail($request->department_course_part_id);
            $departmentCoursePartTopics = $departmentCoursePart->department_course_part_topics()->pluck('topic_id')->toArray();
            foreach ($request->topics_ids as $topicId) {
                if (in_array($topicId, $departmentCoursePartTopics)) {
                    $departmentCoursePart->department_course_part_topics()->where('topic_id', '=', $topicId)->delete();
                } else {
                    $departmentCoursePart->department_course_part_topics()->create([
                        'topic_id' => $topicId
                    ]);
                }
            }
            DB::commit();
            return ResponseHelper::success();
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseHelper::serverError();
        }
    }

    public function retrieveDepartmentCoursePartChapters(Request $request)
    {
        Gate::authorize('retrieveDepartmentCoursePartChapters', DepartmentCoursePartChapterTopicController::class);
        if (ValidateHelper::validateData($request, [
            'department_course_part_id' => 'required|integer'
        ])) {
            return  ResponseHelper::clientError();
        }
        try {
            $chapters = DB::table('department_course_parts')
                ->join('department_course_part_topics', 'department_course_parts.id', '=', 'department_course_part_topics.department_course_part_id')
                ->join('topics', 'department_course_part_topics.topic_id', '=', 'topics.id')
                ->join('chapters', 'topics.chapter_id', '=', 'chapters.id')
                ->select('chapters.id', 'chapters.arabic_title', 'chapters.english_title')
                ->where('department_course_parts.id', '=', $request->department_course_part_id)
                ->where('chapters.status', '=', ChapterStatusEnum::AVAILABLE->value)
                ->distinct()
                ->get();

            return ResponseHelper::successWithData($chapters);
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function retrieveDepartmentCoursePartChapterTopics(Request $request)
    {
        Gate::authorize('retrieveDepartmentCoursePartChapterTopics', DepartmentCoursePartChapterTopicController::class);
        if (ValidateHelper::validateData($request, [
            'department_course_part_id' => 'required|integer',
            'chapter_id' => 'required|integer',
        ])) {
            return  ResponseHelper::clientError();
        }
        try {
            $topics = DB::table('department_course_parts')
                ->join('department_course_part_topics', 'department_course_parts.id', '=', 'department_course_part_topics.department_course_part_id')
                ->join('topics', 'department_course_part_topics.topic_id', '=', 'topics.id')
                ->select('topics.id', 'topics.arabic_title', 'topics.english_title')
                ->where('department_course_parts.id', '=', $request->department_course_part_id)
                ->where('topics.chapter_id', '=', $request->chapter_id)
                ->get();
            return ResponseHelper::successWithData($topics);
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function retrieveEditableDepartmentCoursePartChapters(Request $request)
    {
        Gate::authorize('retrieveEditableDepartmentCoursePartChapters', DepartmentCoursePartChapterTopicController::class);
        if (ValidateHelper::validateData($request, [
            'department_course_part_id' => 'required|integer'
        ])) {
            return  ResponseHelper::clientError();
        }
        try {
        $departmenCoursePart = DepartmentCoursePart::findOrFail($request->department_course_part_id);
        $coursePart = CoursePart::findOrFail($departmenCoursePart->course_part_id);
        $coursePartChapters = $coursePart->chapters()->where('status', ChapterStatusEnum::AVAILABLE->value)->get(['id', 'arabic_title', 'english_title']);

        // coursePartChapters : [id, arabic_title, english_title]

        $departmenCoursePartChapters = DB::table('department_course_part_topics')
            ->join('topics', 'department_course_part_topics.topic_id', '=', 'topics.id')
            ->join('chapters', 'topics.chapter_id', '=', 'chapters.id')
            ->select('chapters.id', DB::raw('count(chapters.id) as count'))
            ->where('department_course_part_topics.department_course_part_id', $request->department_course_part_id)
            ->where('chapters.status', ChapterStatusEnum::AVAILABLE->value)
            ->groupBy('chapters.id')
            ->get();
        // departmenCoursePartChapters : [id, count]

        foreach ($coursePartChapters as $coursePartChapter) {
            $departmenCoursePartChapter = $departmenCoursePartChapters->where('id', $coursePartChapter->id)->first();

            if (is_null($departmenCoursePartChapter)) {
                $isNon = true;
                $isFull = false;
                $isHalf = false;
            } else {
                $isNon = false;
                $isFull = $departmenCoursePartChapter->count === $coursePartChapter->topics()->count();
                $isHalf = !$isFull;
            }

            $coursePartChapter['selection_status'] = [
                'is_non' => $isNon,
                'is_full' => $isFull,
                'is_half' => $isHalf
            ];
        }
        return ResponseHelper::successWithData($coursePartChapters);
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function retrieveEditableDepartmentCoursePartTopics(Request $request)
    {
        Gate::authorize('retrieveEditableDepartmentCoursePartTopics', DepartmentCoursePartChapterTopicController::class);
        if (ValidateHelper::validateData($request, [
            'department_course_part_id' => 'required|integer',
            'chapter_id' => 'required|integer',
        ])) {
            return  ResponseHelper::clientError();
        }
        try {
            // return ResponseHelper::success();
            ////////////////////
            $departmenCoursePart = DepartmentCoursePart::findOrFail($request->department_course_part_id);
            $chapter = Chapter::findOrFail($request->chapter_id);
            $chapterTopics = $chapter->topics()->get(['id', 'arabic_title', 'english_title']);

            $departmenCoursePartChapterTopics = DB::table('department_course_parts')
                ->join('department_course_part_topics', 'department_course_parts.id', '=', 'department_course_part_topics.department_course_part_id')
                ->join('topics', 'department_course_part_topics.topic_id', '=', 'topics.id')
                ->select('topics.id')
                ->where('department_course_parts.id', '=', $request->department_course_part_id)
                ->where('topics.chapter_id', '=', $request->chapter_id)
                ->get();

            // return ResponseHelper::successWithData($departmenCoursePartChapterTopics);

            foreach ($chapterTopics as $chapterTopic) {
                $chapterTopic['is_selected'] = ($departmenCoursePartChapterTopics->where('id', $chapterTopic->id)->count() === 1) ? true : false;
            }
            return ResponseHelper::successWithData($chapterTopics);
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function rules(Request $request): array
    {
        $rules = [
            'department_course_part_id' => 'required|exists:department_course_parts,id',
            'topics_ids'                => 'required|array|min:1',
            'topics_ids.*'              => 'required|integer|exists:topics,id',
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
