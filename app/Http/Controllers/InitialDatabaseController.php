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
        // InitialDatabaseHelper::colleges();
        // InitialDatabaseHelper::departments();
        // $this->courses();
        // $this->chapters(6);
        // $this->topics($request->id);
        // $this->questionsChoice($request->id);
        // $this->questionsTrueFalse($request->id);
        return $this->acceptQuestions();
        // $this->acceptQuestions();

        return ResponseHelper::success();
    }

    private function courses()
    {
        $jsonFilePath = base_path() . '/app/InitialDatabase/courses.json';
        $rows = $this->readDataFromJson($jsonFilePath);
        $temp = [];
        foreach ($rows as $row) {
            Course::create([]);
        }
    }
    private function chapters($coursePartId)
    {
        $jsonFilePath = base_path() . '/app/InitialDatabase/chapters.json';
        $rows = $this->readDataFromJson($jsonFilePath);
        $coursePart = CoursePart::findOrFail($coursePartId);
        foreach ($rows as $row) {
            // return $row;
            $coursePart->chapters()->create([
                "arabic_title" => $row['arabic_title'],
                "english_title" => $row['english_title'],
                "description" => $row['description']
            ]);
        }
    }
    private function topics($chapterId)
    {
        $jsonFilePath = base_path() . '/app/InitialDatabase/topics.json';
        $rows = $this->readDataFromJson($jsonFilePath);
        $chapter = Chapter::findOrFail($chapterId);
        foreach ($rows as $row) {
            // return $row;
            $chapter->topics()->create([
                "arabic_title" => $row['arabic_title'],
                "english_title" => $row['english_title'],
                "description" => $row['description']
            ]);
        }
    }
    private function questionsChoice($topicId)
    {
        $jsonFilePath = base_path() . '/app/InitialDatabase/questions.json';
        $rows = $this->readDataFromJson($jsonFilePath);
        $topic = Topic::findOrFail($topicId);
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
    }
    private function questionsTrueFalse($topicId)
    {
        $jsonFilePath = base_path() . '/app/InitialDatabase/questionsTrueFalse.json';
        $rows = $this->readDataFromJson($jsonFilePath);
        $topic = Topic::findOrFail($topicId);
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
        }
    }
    private function acceptQuestions()
    {
        DB::beginTransaction();
        $questions = Question::where('type', '=', QuestionTypeEnum::MULTIPLE_CHOICE->value)
            ->where('status', '=', 1)
            ->where('id', '<=', 600)
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
    private function saveQuestionChoices(Question $question, $choices)
    {
        foreach ($choices as $choice) {
            $question->choices()->create([
                'content' => $choice['content'],
                'status' => ($choice['isCorrect']) ? ChoiceStatusEnum::CORRECT_ANSWER->value : ChoiceStatusEnum::INCORRECT_ANSWER->value
            ]);
        }
    }
}
