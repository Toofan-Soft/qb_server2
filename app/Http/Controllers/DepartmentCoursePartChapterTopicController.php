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
        $departmenCoursePart = DepartmentCoursePart::findOrFail($request->department_course_part_id);
        if ($request->topics_ids->count() > 1) {
            $departmenCoursePart->department_course_part_topics()->createMany($request->topics_ids);
        } else {
            $departmenCoursePart->department_course_part_topics()->create($request->topics_ids);
        }
        return ResponseHelper::success();
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
    }

    public function retrieveDepartmentCoursePartChapterTopics(Request $request)
    {
        $topics = DB::table('department_course_parts')
            ->join('department_course_part_topics', 'department_course_parts.id', '=', 'department_course_part_topics.department_course_part_id')
            ->join('topics', 'department_course_part_topics.topic_id', '=', 'topics.id')
            ->select('topics.id', 'topics.arabic_title', 'topics.english_title')
            ->where('department_course_parts.id', '=', $request->department_course_part_id)
            ->where('topics.chapter_id', '=', $request->chapter_id)
            ->get();
        return ResponseHelper::successWithData($topics);
    }


    public function retrieveAvailableDepartmentCoursePartChapters(Request $request)
    { 
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
            $isNon = $departmenCoursePartChapters->where('id', $coursePartChapter->id)->count() === 0;
            $isFull = $departmenCoursePartChapters->where('id', $coursePartChapter->id)->count() === $coursePartChapter->topics_count;
            $isHalf = !$isNon && !$isFull;

            $coursePartChapter['selection_status'] = [
                'is_non' => $isNon,
                'is_full' => $isFull,
                'is_half' => $isHalf
            ];
        }

        return ResponseHelper::successWithData($coursePartChapters);
    }

    public function retrieveAvailableDepartmentCoursePartTopics(Request $request)
    {
        $departmenCoursePart = DepartmentCoursePart::find($request->department_course_part_id);
        $chapter = Chapter::find($request->chapter_id);
        $chapterTopics = $chapter->topics()->get(['id', 'arabic_title', 'english_title']);

        $departmenCoursePartChapterTopics = DB::table('department_course_parts')
            ->join('department_course_part_topics', 'department_course_parts.id', '=', 'department_course_part_topics.department_course_part_id')
            ->join('topics', 'department_course_part_topics.topic_id', '=', 'topics.id')
            ->select('topics.id')
            ->where('department_course_parts.id', '=', $request->department_course_part_id)
            ->where('topics.chapter_id', '=', $request->chapter_id)
            ->get();

        foreach ($chapterTopics as $chapterTopic) {
            $chapterTopic['is_selected'] = ($departmenCoursePartChapterTopics->where('id', '=', $chapterTopic->id)) ? true : false;
        }
        return ResponseHelper::successWithData($chapterTopics);
    }
}
