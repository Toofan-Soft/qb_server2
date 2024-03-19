<?php

namespace App\Http\Controllers;

use App\Models\Chapter;
use App\Helpers\AddHelper;
use App\Models\CoursePart;
use Illuminate\Http\Request;
use App\Enums\ChapterStatusEnum;
use Illuminate\Support\Facades\DB;
use App\Models\DepartmentCoursePart;
use App\Models\DepartmentCoursePartTopic;

class DepartmentCoursePartChapterTopicController extends Controller
{
    public function addDepartmentCoursePartTopics(Request $request)
    {
        //return AddHelper::addModel($request, DepartmentCoursePart::class,  $this->rules($request), 'department_course_part_topics', $request->chapter_id);

        $departmenCoursePart = DepartmentCoursePart::find($request->department_course_part_id);
        $departmenCoursePart->department_course_part_topics()->createMany($request->topics_ids);

    }

    public function deleteDepartmentCoursePartTopics(Request $request)
    {

        foreach ($request->topics_ids as $topic_id) {
            $departmenCoursePartTopic = DepartmentCoursePartTopic::find([$topic_id, $request->department_course_part_id]);
            $departmenCoursePartTopic->delete();
        }
    //    return DeleteHelper::deleteModel($department);

    }


    public function retrieveDepartmentCoursePartChapters(Request $request)
    {
   
    $result = DB::table('department_course_parts')
    ->join('department_course_part_topics', 'department_course_parts.id', '=', 'department_course_part_topics.department_course_part_id')
    ->join('topics', 'department_course_part_topics.topic_id', '=', 'topics.id')
    ->join('chapters', 'topics.chapter_id', '=', 'chapters.id')
    ->select('chapters.id', 'chapters.arabic_title', 'chapters.english_title')
    ->where('department_course_parts.id', '=', $request->department_course_part_id)
    ->where('chapters.status', '=', ChapterStatusEnum::AVAILABLE->value)
    ->distinct()
    ->get();
    return $result;
    }

    public function retrieveDepartmentCoursePartChapterTopics(Request $request)
    {
    $result = DB::table('department_course_parts')
    ->join('department_course_part_topics', 'department_course_parts.id', '=', 'department_course_part_topics.department_course_part_id')
    ->join('topics', 'department_course_part_topics.topic_id', '=', 'topics.id')
    ->select('topics.id', 'topics.arabic_title', 'topics.english_title')
    ->where('department_course_parts.id', '=', $request->department_course_part_id)
    ->where('topics.chapter_id', '=', $request->chapter_id)
    ->get();

    }


    public function retrieveAvailableDepartmentCoursePartChapters(Request $request)
    {
        ////////////////////
        $departmenCoursePart = DepartmentCoursePart::find($request->department_course_part_id);
        $coursePart = CoursePart::find($departmenCoursePart->course_part_id);
        $coursePartChapters = $coursePart->chapters()->where('status', ChapterStatusEnum::AVAILABLE->value)->get(['id', 'arabic_title', 'english_title']);
        foreach ($coursePartChapters as $coursePartChapter) {
            $coursePartChapter['topics_count'] = $coursePartChapter->topics()->count();
        }

        $departmenCoursePartChapters = DB::table('department_course_parts')
        ->join('department_course_part_topics', 'department_course_parts.id', '=', 'department_course_part_topics.department_course_part_id')
        ->join('topics', 'department_course_part_topics.topic_id', '=', 'topics.id')
        ->join('chapters', 'topics.chapter_id', '=', 'chapters.id')
        ->select('chapters.id', DB::raw('count(chapters.id) as count'))
        ->where('department_course_parts.id', $departmenCoursePart->course_part_id)
        ->where('chapters.status', ChapterStatusEnum::AVAILABLE->value)
        ->groupBy('chapters.id')
        ->get();

        foreach ($coursePartChapters as $coursePartChapter) {
        $isNon = $departmenCoursePartChapters->where('id', $coursePartChapter->id)->count() === 0;
        $isFull = $departmenCoursePartChapters->where('id', $coursePartChapter->id)->first()->count === $coursePartChapter->topics_count;
        $isHalf = !$isNon && !$isFull;

        $coursePartChapter['selection_status'] = [
            'is_non' => $isNon,
            'is_full' => $isFull,
            'is_half' => $isHalf
        ];
    }

    return $coursePartChapters;
    }

    public function retrieveAvailableDepartmentCoursePartTopics(Request $request)
    {
    ////////////////////
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
        if(in_array($chapterTopic->id, $departmenCoursePartChapterTopics['id'])){

            $chapterTopic['is_selected'] = true;
        }else{
            $chapterTopic['is_selected'] = false;

        }
    }
    return $chapterTopics;

    }

    public function rules(Request $request): array
    {
        $rules = [
            // 'arabic_name' => 'required|string|max:255',
            // 'english_name' => 'required|string|max:255',
            // 'logo_url' =>  'image|mimes:jpeg,png,jpg,gif|max:2048',
            // 'levels_count' =>  new Enum(LevelsCountEnum::class),
            // 'description' => 'nullable|string',
            // 'college_id' => 'required',
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
