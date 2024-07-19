<?php

namespace App\Http\Controllers;

use Exception;
use Pusher\Pusher;
use App\Models\User;
use App\Models\Topic;
use App\Models\Course;
use App\Models\Chapter;
use App\Models\College;
use App\Models\Question;
use App\Events\FireEvent;
use App\Helpers\AddHelper;
use App\Helpers\GetHelper;
use App\Models\CoursePart;
use App\Enums\LanguageEnum;
use App\Helpers\ImageHelper;
use Illuminate\Http\Request;
use App\Helpers\DeleteHelper;
use App\Helpers\ModifyHelper;
use App\Helpers\ResponeHelper;
use App\Enums\ChoiceStatusEnum;
use App\Enums\QuestionTypeEnum;
use App\Helpers\QuestionHelper;
use App\Helpers\ResponseHelper;
use App\Helpers\ValidateHelper;
use App\Models\DepartmentCourse;
use App\Enums\QuestionStatusEnum;
use App\Enums\TrueFalseAnswerEnum;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Enums\AccessibilityStatusEnum;
use App\Enums\ExamDifficultyLevelEnum;
use App\Helpers\InitialDatabaseHelper;
use Illuminate\Support\Facades\Storage;
use League\CommonMark\Node\Query\OrExpr;
use Illuminate\Support\Facades\Validator;

class InitialDatabaseController extends Controller
{

    public function initialDatabase(Request $request)
    {
        // $this->colleges();
        // $this->courses();
        // $this->chapters($request->course_part_id);
        // return $this->chapters($request->course_part_id);
        // $this->questionsChoices($request->topic_id);
        // $this->questionsTrueFalse($request->topic_id);
        // $this->acceptQuestions($request->topic_id);
        // $this->acceptQuestions();

        return ResponseHelper::success();
    }

    private function colleges()
    {
        $jsonFilePath = base_path() . '/app/InitialDatabase/colleges.json';
        $rows = $this->readDataFromJson($jsonFilePath);
        $temp = [];
        DB::beginTransaction();
        foreach ($rows as $row) {
            $college = College::create([
                'arabic_name' => $row['arabic_name'],
                'english_name' => $row['english_name'],
                'phone' => $row['phone'],
                'email' => $row['email']
            ]);
            foreach ($row['departments'] as $department) {
                $college->departments()->create([
                    'arabic_name' => $department['arabic_name'],
                    'english_name' => $department['english_name'],
                    'levels_count' => $department['levels_count']
                ]);
            }
        }
        DB::commit();
    }

    private function courses()
    {
        $jsonFilePath = base_path() . '/app/InitialDatabase/courses.json';
        $rows = $this->readDataFromJson($jsonFilePath);
        DB::beginTransaction();
        foreach ($rows as $row) {
            $course = Course::create([
                'arabic_name' => $row['arabic_name'],
                'english_name' => $row['english_name'],
            ]);
            foreach ($row['course_parts'] as $course_part) {
                $course->course_parts()->create([
                    'part_id' => $course_part['part_id'],
                    'description' => $course_part['description'],
                ]);
            }
        }
        DB::commit();
    }

    private function chapters($coursePartId)
    {
        $jsonFilePath = base_path() . '/app/InitialDatabase/chapters.json';
        $rows = $this->readDataFromJson($jsonFilePath);
        $coursePart = CoursePart::findOrFail($coursePartId);
        DB::beginTransaction();
        foreach ($rows as $row) {
            $chapter = $coursePart->chapters()->create([
                'arabic_title' => $row['arabic_title'],
                'english_title' => $row['english_title'],
                'description' => $row['description']
            ]);
            foreach ($row['topics'] as $topic) {
                $chapter->topics()->create([
                    'arabic_title' => $topic['arabic_title'],
                    'english_title' => $topic['english_title'],
                    'description' => $topic['description']
                ]);
            }
        }
        DB::commit();
    }

    private function questionsChoices($topicId)
    {
        $jsonFilePath = base_path() . '/app/InitialDatabase/questions.json';
        $rows = $this->readDataFromJson($jsonFilePath);
        $topic = Topic::findOrFail($topicId);
        DB::beginTransaction();
        foreach ($rows as $row) {
            $question = $topic->questions()->create([
                'type' => QuestionTypeEnum::MULTIPLE_CHOICE->value,
                'difficulty_level' => ExamDifficultyLevelEnum::toFloat($this->selectRandomDifficultyLevel()),
                'accessibility_status' => $this->selectRandomAccessibilityStatus(),
                'language' => LanguageEnum::ENGLISH->value,
                'estimated_answer_time' => $this->selectRandomEstimatedAnswerTime(),
                'content' => $row['content'],
                'status' => QuestionStatusEnum::REQUESTED->value,
                'attachment' => null,
                'title' => null,
            ]);
            $this->saveQuestionChoices($question, $row['choices']);
        }
        DB::commit();
    }

    private function saveQuestionChoices(Question $question, $choices)
    {
        foreach ($choices as $choice) {
            $question->choices()->create([
                'content' => $choice['content'],
                'status' => ($choice['isCorrect']) ? ChoiceStatusEnum::CORRECT_ANSWER->value : ChoiceStatusEnum::INCORRECT_ANSWER->value
            ]);
        }
    }

    private function questionsTrueFalse($topicId)
    {
        $jsonFilePath = base_path() . '/app/InitialDatabase/questionsTrueFalse.json';
        $rows = $this->readDataFromJson($jsonFilePath);
        $topic = Topic::findOrFail($topicId);
        DB::beginTransaction();
        foreach ($rows as $row) {
            $question = $topic->questions()->create([
                'type' => QuestionTypeEnum::TRUE_FALSE->value,
                'difficulty_level' => ExamDifficultyLevelEnum::toFloat($this->selectRandomDifficultyLevel()),
                'accessibility_status' => $this->selectRandomAccessibilityStatus(),
                'language' => LanguageEnum::ENGLISH->value,
                'estimated_answer_time' => $this->selectRandomEstimatedAnswerTime(),
                'content' => $row['content'],
                'status' => QuestionStatusEnum::ACCEPTED->value,
                'attachment' => null,
                'title' => null,
            ]);
            $question->true_false_question()->create([
                'answer' => ($row['isCorrect']) ? TrueFalseAnswerEnum::TRUE->value : TrueFalseAnswerEnum::FALSE->value,
            ]);
            $question->question_usage()->create();
        }
        DB::commit();
    }

    private function acceptQuestions($topicId)
    {
        DB::beginTransaction();
        $questions = Question::where('type', '=', QuestionTypeEnum::MULTIPLE_CHOICE->value)
            ->where('status', '=', 1)
            ->where('topic_id', '=', $topicId)
            // ->where('id', '<=', 230)
            ->get();
        // return $questions;
        foreach ($questions as $question) {
            $question->update([
                'status' => 2
            ]);
            $question->question_usage()->create();

            if (intval($question->type) === QuestionTypeEnum::MULTIPLE_CHOICE->value) {
                QuestionHelper::generateQuestionChoicesCombination($question);
            }
        }
        DB::commit();
        // return $questions;
    }

    private function readDataFromJson($jsonFilePath)
    {
        if (!file_exists($jsonFilePath)) {
            throw new Exception("File not found: " . $jsonFilePath);
        }

        $jsonContents = file_get_contents($jsonFilePath);
        $data = json_decode($jsonContents, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Error decoding JSON: " . json_last_error_msg());
        }
        return $data;
    }
    private function selectRandomDifficultyLevel(): int
    {
        $difficultyLevels = ExamDifficultyLevelEnum::values();
        $randomIndex = array_rand($difficultyLevels);
        return $difficultyLevels[$randomIndex];
    }
    private function selectRandomAccessibilityStatus(): int
    {
        $accessibilityStatuses = AccessibilityStatusEnum::values();
        $randomIndex = array_rand($accessibilityStatuses);
        return $accessibilityStatuses[$randomIndex];
    }
    private function selectRandomEstimatedAnswerTime(): float
    {
        // select randomly int number
        // this number represent time in second. 
        // the selected time must be >= 1 minute and <= 10 minute 

        $minSeconds = 1 * 60; // 1 minute in seconds
        $maxSeconds = 10 * 60; // 10 minutes in seconds
        $randomSeconds = mt_rand($minSeconds, $maxSeconds);
        return $randomSeconds;
    }
}
