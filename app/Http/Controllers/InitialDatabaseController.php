<?php

namespace App\Http\Controllers;

use Exception;
use Pusher\Pusher;
use App\Models\User;
use App\Models\Guest;
use App\Models\Topic;
use App\Models\Course;
use App\Enums\RoleEnum;
use App\Models\Chapter;
use App\Models\College;
use App\Models\Student;
use App\Models\Employee;
use App\Models\Question;
use App\Events\FireEvent;
use App\Enums\JobTypeEnum;
use App\Helpers\AddHelper;
use App\Helpers\GetHelper;
use App\Models\CoursePart;
use App\Models\Department;
use App\Enums\LanguageEnum;
use App\Enums\OwnerTypeEnum;
use App\Helpers\ImageHelper;
use Illuminate\Http\Request;
use App\Enums\UserStatusEnum;
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
use App\Enums\CourseStudentStatusEnum;
use App\Enums\ExamDifficultyLevelEnum;
use App\Helpers\InitialDatabaseHelper;
use Illuminate\Support\Facades\Storage;
use League\CommonMark\Node\Query\OrExpr;
use App\Models\DepartmentCoursePartTopic;
use Illuminate\Support\Facades\Validator;

class InitialDatabaseController extends Controller
{

    public function initialDatabase(Request $request)
    {
        DB::beginTransaction();
        // step 1
        // $this->colleges();
        // $this->courses();

        // step 2 add questions
        // $this->questionsChoices(1, 'ar');
        // $this->questionsTrueFalse(1, 'ar');
        // $this->questionsChoices(2, 'en');
        // $this->questionsTrueFalse(2, 'en');

        // step 3 accept questions
        // $this->acceptQuestions(100);
        // $this->acceptQuestions(200);
        // $this->acceptQuestions(300);
        // $this->acceptQuestions(400);
        // $this->acceptQuestions(700);
        // $this->acceptQuestions(800);
        // $this->acceptQuestions(900);
        // $this->acceptQuestions(1000);
        // $this->acceptQuestions(1100);
        // $this->acceptQuestions(1200);
        // $this->acceptQuestions(1300);
        // $this->acceptQuestions(1390);

        // step 4 add study plan, emplyees, students
        // $this->studyCoursesPlans();
        // $this->employeesAdmins();
        // $this->employees();
        // $this->students();

        DB::commit();
        return ResponseHelper::success();
    }

    private function university()
    {
    }

    private function colleges()
    {
        $jsonFilePath = base_path() . '/app/InitialDatabase/colleges.json';
        $rows = $this->readDataFromJson($jsonFilePath);
        // DB::beginTransaction();
        foreach ($rows as $row) {
            $college = College::create([
                'arabic_name' => $row['arabic_name'],
                'english_name' => $row['english_name'],
                'phone' => $row['phone'],
                'email' => $row['email'],
                'description' => $row['description'],
                'facebook' => $row['facebook'],
                'youtube' => $row['youtube'],
                'x_platform' => $row['x_platform'],
                'telegram' => $row['telegram']
            ]);
            foreach ($row['departments'] as $department) {
                $college->departments()->create([
                    'arabic_name' => $department['arabic_name'],
                    'english_name' => $department['english_name'],
                    'levels_count' => $department['levels_count']
                ]);
            }
        }
        // DB::commit();
    }

    private function courses()
    {
        $jsonFilePath = base_path() . '/app/InitialDatabase/courses.json';
        $rows = $this->readDataFromJson($jsonFilePath);
        // DB::beginTransaction();
        foreach ($rows as $row) {
            $course = Course::create([
                'arabic_name' => $row['arabic_name'],
                'english_name' => $row['english_name'],
            ]);
            foreach ($row['course_parts'] as $course_part) {
                $coursePart = $course->course_parts()->create([
                    'part_id' => $course_part['part_id'],
                    'description' => $course_part['description'],
                ]);
                $this->chapters($coursePart->id);
            }
        }
        // DB::commit();
    }

    private function chapters($coursePartId)
    {
        $jsonFilePath = base_path() . '/app/InitialDatabase/chapters.json';
        $rows = $this->readDataFromJson($jsonFilePath);
        $coursePart = CoursePart::findOrFail($coursePartId);
        // DB::beginTransaction();
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
        // DB::commit();
    }


    private function questionsChoices($coursePartId, $type)
    {
        if ($type === 'en') {
            $jsonFilePath = base_path() . '/app/InitialDatabase/questions_choices_english_language.json';
            $languageId = LanguageEnum::ENGLISH->value;
        } else {
            $jsonFilePath = base_path() . '/app/InitialDatabase/questions_choices_arabic_language.json';
            $languageId = LanguageEnum::ARABIC->value;
        }
        $rows = $this->readDataFromJson($jsonFilePath);
        $coursePart = CoursePart::findOrFail($coursePartId);
        $chapters = $coursePart->chapters()->get();
        foreach ($chapters as $chapter) {
            $topicIds = $chapter->topics()->pluck('id')->toArray();
            foreach ($rows as $row) {
                $question = Question::create([
                    'topic_id' => $this->selectRandomFromList($topicIds),
                    'type' => QuestionTypeEnum::MULTIPLE_CHOICE->value,
                    'difficulty_level' => ExamDifficultyLevelEnum::toFloat($this->selectRandomDifficultyLevel()),
                    'accessibility_status' => $this->selectRandomAccessibilityStatus(),
                    'language' => $languageId,
                    'estimated_answer_time' => $this->selectRandomEstimatedAnswerTime(),
                    'content' => $row['content'],
                    'status' => QuestionStatusEnum::REQUESTED->value,
                    'attachment' => null,
                    'title' => null,
                ]);
                $this->saveQuestionChoices($question, $row['choices']);
            }
        }
    }

    private function questionsTrueFalse($coursePartId, $type)
    {
        if ($type === 'en') {
            $jsonFilePath = base_path() . '/app/InitialDatabase/questions_true_false_english_language.json';
            $languageId = LanguageEnum::ENGLISH->value;
        } else {
            $jsonFilePath = base_path() . '/app/InitialDatabase/questions_true_false_arabic_language.json';
            $languageId = LanguageEnum::ARABIC->value;
        }
        $rows = $this->readDataFromJson($jsonFilePath);
        $coursePart = CoursePart::findOrFail($coursePartId);
        $chapters = $coursePart->chapters()->get();
        foreach ($chapters as $chapter) {
            $topicIds = $chapter->topics()->pluck('id')->toArray();
            foreach ($rows as $row) {
                $question = Question::create([
                    'topic_id' => $this->selectRandomFromList($topicIds),
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
        }
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

    private function acceptQuestions($maxId)
    {
        // DB::beginTransaction();
        $questions = Question::where('type', '=', QuestionTypeEnum::MULTIPLE_CHOICE->value)
            ->where('status', '=', 1)
            // ->where('topic_id', '=', $topicId)
            ->where('id', '<=', $maxId)
            ->get();
        // return $questions;
        foreach ($questions as $question) {
            $question->update([
                'status' => QuestionStatusEnum::ACCEPTED->value,
            ]);
            $question->question_usage()->create();

            if (intval($question->type) === QuestionTypeEnum::MULTIPLE_CHOICE->value) {
                QuestionHelper::generateQuestionChoicesCombination($question);
            }
        }
        // DB::commit();
        // return $questions;
    }

    private function studyCoursesPlans()
    {
        $jsonFilePath = base_path() . '/app/InitialDatabase/study_courses_plan.json';
        $rows = $this->readDataFromJson($jsonFilePath);
        // DB::beginTransaction();
        foreach ($rows as $row) {
            $department = Department::findOrfail($row['department_id']);
            foreach ($row['department_courses'] as $department_course) {
                $departmentCourse = $department->department_courses()->create([
                    // 'department_id' => $department_course['department_id'],
                    'course_id' => $department_course['course_id'],
                    'level' => $department_course['level_id'],
                    'semester' => $department_course['semester_id'],

                ]);
                foreach ($department_course['department_course_parts'] as $department_course_part) {
                    $departmentCoursePart = $departmentCourse->department_course_parts()->create([
                        // 'department_course_id' => $department_course_part['department_course_id'],
                        'course_part_id' => $department_course_part['course_part_id'],
                        'score' => $department_course_part['score'],
                        'lectures_count' => $department_course_part['lectures_count'],
                        'lecture_duration' => $department_course_part['lecture_duration'],
                        'note' => $department_course_part['note'],
                    ]);
                    $coursePart = CoursePart::findOrFail($department_course_part['course_part_id']);
                    $chaptersIds = $coursePart->chapters()->pluck('id')->toArray();
                    $chaptersIds = $coursePart->chapters()->pluck('id')->toArray();
                    $topicsIds = Topic::whereIn('id', $chaptersIds)->pluck('id')->toArray();
                    foreach ($topicsIds as $topicId) {
                        DepartmentCoursePartTopic::create([
                            'topic_id' => $topicId,
                            'department_course_part_id' => $departmentCoursePart->id,
                        ]);
                    }
                }
            }
        }
        // DB::commit();
    }

    private function employeesAdmins()
    {
        $jsonFilePath = base_path() . '/app/InitialDatabase/employees_admins.json';
        $rows = $this->readDataFromJson($jsonFilePath);
        // DB::beginTransaction();
        $roles = [3, 4, 5, 6, 7];
        foreach ($rows as $row) {
            $emplyee = Employee::create([
                'arabic_name' => $row['arabic_name'],
                'english_name' => $row['english_name'],
                'gender' => $row['gender_id'],
                'phone' => $row['phone'],
                'job_type' => $row['job_type_id'],
                'qualification' => $row['qualification_id'],
                'specialization' => $row['specialization'],
                'image_url' => null,
            ]);
            $this->addUser($row['email'], OwnerTypeEnum::EMPLOYEE->value, $emplyee->id, $row['password'],$roles);
        }
        // DB::commit();
    }

    private function employees()
    {
        $jsonFilePath = base_path() . '/app/InitialDatabase/employees.json';
        $rows = $this->readDataFromJson($jsonFilePath);
        // DB::beginTransaction();
        $phone = 777200001;
        foreach ($rows as $row) {
            $emplyee = Employee::create([
                'arabic_name' => $row['arabic_name'],
                'english_name' => $row['english_name'],
                'gender' => $row['gender_id'],
                'phone' => $phone,
                'job_type' => $this->selectRandomFromList([0, 1, 2]),
                'qualification' => $this->selectRandomFromList([0, 1, 2, 3, 4, 5]),
                'image_url' => null,
                'specialization' => null,
            ]);
            $email = $phone . '@gmail.com';
            $password = 'e' . $phone . 'e';
            $phone++;
            $this->addUser($email, OwnerTypeEnum::EMPLOYEE->value, $emplyee->id, $password);
        }
        // DB::commit();
    }

    private function students()
    {
        $jsonFilePath = base_path() . '/app/InitialDatabase/students.json';
        $rows = $this->readDataFromJson($jsonFilePath);
        // DB::beginTransaction();
        $phone = 777300001;
        $academicId = 1902001;
        foreach ($rows as $row) {
            foreach ($row['students'] as $student) {
                $student = Student::create([
                    'academic_id' => $academicId++,
                    'arabic_name' => $student['arabic_name'],
                    'english_name' => $student['english_name'],
                    'gender' => $student['gender_id'],
                    'phone' => $phone,
                    'birthdate' => 12615,
                    'image_url' => null,
                ]);
                $email = $phone . '@gmail.com';
                $password = 's' . $phone . 's';
                $phone++;
                $this->addUser($email, OwnerTypeEnum::STUDENT->value, $student->id, $password);
                $departmentCourses = DepartmentCourse::where('department_id', $row['department_id'])
                ->where('level', $row['level_id'])
                ->where('semester', $row['semester_id'])
                ->get();

                foreach ($departmentCourses as $departmentCourse) {
                    $departmentCourse->course_students()->create([
                        'student_id' => $student->id,
                        'status' => CourseStudentStatusEnum::ACTIVE->value,
                        'academic_year' => now()->format('Y')
                    ]);
                }
            }

        }
        // DB::commit();
    }

    private function selectRandomFromList($list): int
    {
        $randomIndex = array_rand($list);
        return $list[$randomIndex];
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

    private function addUser($email, $ownerTypeId, $ownerId, $password = null, $roles = [])
    {

        $roles = $roles ?? [];
        // DB::beginTransaction();

        $user = User::create([
            'email' => $email,
            'password' => bcrypt($password),
            'status' => UserStatusEnum::ACTIVATED->value,
            'owner_type' => $ownerTypeId,
            'email_verified_at' => now(),
        ]);
        $owner = null;
        if ($ownerTypeId === OwnerTypeEnum::GUEST->value) {
            $owner = Guest::findOrFail($ownerId)->update(['user_id' => $user->id]);
            array_push($roles, RoleEnum::GUEST->value);
        } elseif ($ownerTypeId === OwnerTypeEnum::STUDENT->value) {
            $owner = Student::findOrFail($ownerId)->update(['user_id' => $user->id]);
            array_push($roles, RoleEnum::STUDENT->value);
        } elseif ($ownerTypeId === OwnerTypeEnum::EMPLOYEE->value) {
            $owner = Employee::findOrFail($ownerId);
            if ((intval($owner->job_type) === JobTypeEnum::LECTURER->value) ||
                (intval($owner->job_type) === JobTypeEnum::EMPLOYEE_LECTURE->value)
            ) {
                array_push($roles, RoleEnum::LECTURER->value);
            }
            $owner->update(['user_id' => $user->id]);
        }

        foreach ($roles as $role) {
            $user->user_roles()->create([
                'role_id' => $role,
            ]);
        }
    }
}
