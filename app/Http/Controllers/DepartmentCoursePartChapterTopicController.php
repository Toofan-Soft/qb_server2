<?php

namespace App\Http\Controllers;

use App\Models\Chapter;
use App\Helpers\AddHelper;
use App\Models\CoursePart;
use Illuminate\Http\Request;
use App\Helpers\ResponseHelper;
use App\Enums\ChapterStatusEnum;
use Illuminate\Support\Facades\DB;
use App\Models\DepartmentCoursePart;
use App\Models\DepartmentCoursePartTopic;

class DepartmentCoursePartChapterTopicController extends Controller
{
    public function addDepartmentCoursePartTopics(Request $request)
    {
        // if ($request->topics_ids->count() > 1) {
        //     $departmentCoursePart->department_course_part_topics()->createMany($request->topics_ids);
        // } else {
        //     $departmentCoursePart->department_course_part_topics()->create($request->topics_ids);
        // }
        DB::beginTransaction();
        try {
            $departmentCoursePart = DepartmentCoursePart::findOrFail($request->department_course_part_id);
            if (count($request->topics_ids) === 1) {
                $departmentCoursePart->department_course_part_topics()->create([
                    'topic_id' => $request->topics_ids[0],
                ]);
            } else {
                $topicsData = [];
                foreach ($request->topics_ids as $topicId) {
                    $topicsData[] = [
                        'topic_id' => $topicId,
                    ];
                }
                $departmentCoursePart->department_course_part_topics()->createMany($topicsData);
            }
            DB::commit();
            return ResponseHelper::success();
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseHelper::serverError();
        }
    }

    public function deleteDepartmentCoursePartTopics(Request $request)
    {
        try {
            $departmenCoursePart = DepartmentCoursePart::findOrFail($request->department_course_part_id);
            $departmenCoursePart->department_course_part_topics()
                ->whereIn('topic_id', $request->topics_ids)->delete();
            return ResponseHelper::success();
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function retrieveDepartmentCoursePartChapters(Request $request)
    {
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


    public function retrieveAvailableDepartmentCoursePartChapters(Request $request)
    {
        try {
            $departmenCoursePart = DepartmentCoursePart::findOrFail($request->department_course_part_id);
            $coursePart = CoursePart::findOrFail($departmenCoursePart->course_part_id);
            $coursePartChapters = $coursePart->chapters()->where('status', ChapterStatusEnum::AVAILABLE->value)->get(['id', 'arabic_title', 'english_title']);

            foreach ($coursePartChapters as $coursePartChapter) {
                $coursePartChapter['topics_count'] = $coursePartChapter->topics()->count();
            }
            // coursePartChapters : [id, arabic_title, english_title, topics_count]

            $departmenCoursePartChapters = DB::table('department_course_parts')
                ->join('department_course_part_topics', 'department_course_parts.id', '=', 'department_course_part_topics.department_course_part_id')
                ->join('topics', 'department_course_part_topics.topic_id', '=', 'topics.id')
                ->join('chapters', 'topics.chapter_id', '=', 'chapters.id')
                ->select('chapters.id', DB::raw('count(chapters.id) as count'))
                ->where('department_course_parts.id', $departmenCoursePart->id)
                ->where('chapters.status', ChapterStatusEnum::AVAILABLE->value)
                ->groupBy('chapters.id')
                ->get();
            // departmenCoursePartChapters : [id, count]

            foreach ($coursePartChapters as $coursePartChapter) {
                $departmenCoursePartChapter = $departmenCoursePartChapters->where('id', $coursePartChapter->id)->first();
                $isNon = $departmenCoursePartChapters->where('id', $coursePartChapter->id)->first()->count === 0;
                $isFull = $departmenCoursePartChapters->where('id', $coursePartChapter->id)->first()->count === $coursePartChapter->topics_count;
                $isHalf = !$isNon && !$isFull;

                $coursePartChapter['selection_status'] = [
                    'is_non' => $isNon,
                    'is_full' => $isFull,
                    'is_half' => $isHalf
                ];
                unset($coursePartChapter['topics_count']);
            }
            return ResponseHelper::successWithData($coursePartChapters);
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function retrieveAvailableDepartmentCoursePartTopics(Request $request)
    {
        try {
            return ResponseHelper::success();
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
            'department_course_part_id' => 'required|integer|exists:department_course_parts,id',
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
