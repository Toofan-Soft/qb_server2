<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Topic;
use App\Models\Choice;
use App\Models\Course;
use App\Models\Chapter;
use App\Models\College;
use App\Models\Question;
use App\Models\CoursePart;
use App\Models\Department;
use App\Models\QuestionUsage;
use App\Enums\CoursePartsEnum;
use App\Models\CourseLecturer;
use App\Models\CourseStudent;
use Illuminate\Database\Seeder;
use App\Models\DepartmentCourse;
use App\Models\TrueFalseQuestion;
use App\Models\DepartmentCoursePart;
use App\Models\DepartmentCourseTopic;
use App\Models\DepartmentCoursePartTopic;
use App\Models\Employee;
use App\Models\FavoriteQuestion;
use App\Models\Form;
use App\Models\FormQuestion;
use App\Models\Guest;
use App\Models\OnlineExam;
use App\Models\PaperExam;
use App\Models\PracticeExam;
use App\Models\PracticeExamQuestion;
use App\Models\RealExam;
use App\Models\RealExamQuestionType;
use App\Models\Student;
use App\Models\StudentAnswer;
use App\Models\StudentOnlineExam;
use App\Models\User;
use App\Models\UserRole;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        // User::factory(10)->create(['password'=>'12345678']);
        // then go to consol> php artisan db:seed

        // \App\Models\Department::factory(10)->create();
        // \App\Models\College::factory(10)->create();
        // \App\Models\Course::factory(10)->create();
        // \App\Models\CoursePart::factory(10)->create();
        // // //////

        //  College::factory()->count(10)->create()->each(function ($college) {
        //     $college->departments()->save(Department::factory()->make());
        // });

                // Course::factory()->count(10)->create()->each(function ($course) {
                //     $coursePart = CoursePart::factory()->create([
                //         'course_id' => $course->id, // Assign the current course's ID
                //     ]);

                //     $department = Department::inRandomOrder()->first(); // Get a random department
                //     $departmentCourse = DepartmentCourse::factory()->create([
                //         'course_id' => $course->id,
                //         'department_id' => $department->id, // Assign a random department ID
                //     ]);

                //     $departmentCoursepart = DepartmentCoursePart::factory()->create([
                //         'department_course_id' => $departmentCourse->id,
                //         'course_part_id' => $coursePart->id, // Assign a random department ID
                //     ]);

                //     $chapter = Chapter::factory()->create([
                //         'course_part_id' => $coursePart->id,
                //     ]);

                //     $topic = Topic::factory()->create([
                //         'chapter_id' => $chapter->id,
                //     ]);

                //     $departmentcoursetopic = DepartmentCoursePartTopic::factory()->create([
                //         'department_course_part_id' => $departmentCourse->id,
                //         'topic_id' => $topic->id,
                //     ]);
                // });

        /////////////////////

        // $topic = Topic::inRandomOrder()->first();
        // Question::factory()->count(10)->create([
        //     'topic_id' => $topic->id,
        //   ])->each(function ($question) {
        //      $choice = Choice::factory()->create([
        //         'question_id' => $question->id,
        //     ]);

        //     $qusage = QuestionUsage::factory()->create([
        //         'question_id' => $question->id,
        //     ]);

        //     $truefalse = TrueFalseQuestion::factory()->create([
        //         'question_id' => $question->id,
        //     ]);
        // });

////////////////////////////

    //    User::factory()->count(10)->create([
    //         'password' => '12345678',
    //     ])->each(function ($user) {
    //         $departmentCoursepart = DepartmentCoursePart::inRandomOrder()->first();
    //         $departmentCourse = DepartmentCourse::inRandomOrder()->first();
    //         $question = Question::inRandomOrder()->first();

    //         $userRole = UserRole::factory()->create([
    //             'user_id' => $user->id,
    //         ]);

    //         $employee = Employee::factory()->create([
    //             'user_id' => $user->id,
    //         ]);

    //         $courseLecturer = CourseLecturer::factory()->create([
    //             'lecturer_id' => $employee->id,
    //             'department_course_part_id' => $departmentCoursepart->id, ///
    //         ]);

    //         $realExam = RealExam::factory()->create([
    //             'course_lecturer_id' => $courseLecturer->id,
    //         ]);

    //         $realExamQuestionType = RealExamQuestionType::factory()->create([
    //             'real_exam_id' => $realExam->id,
    //         ]);
    //         $form = Form::factory()->create([
    //             'real_exam_id' => $realExam->id,
    //         ]);

    //         $onlineExam = OnlineExam::factory()->create([
    //             'id' => $realExam->id,
    //             'proctor_id' => $employee->id
    //         ]);


    //         $paperExam = PaperExam::factory()->create([
    //             'id' => $realExam->id,
    //         ]);
    //         $practiseExam = PracticeExam::factory()->create([
    //             'department_course_part_id' => $departmentCoursepart->id, ///
    //             'user_id' => $user->id,
    //         ]);

    //         $guest = Guest::factory()->create([
    //             'user_id' => $user->id,
    //         ]);
    //         $student = Student::factory()->create([
    //             'user_id' => $user->id,
    //         ]);

    //         $StudentonlineExam = CourseStudent::factory()->create([
    //             'department_course_id' => $departmentCourse->id,
    //             'student_id' => $student->id,
    //         ]);

    //         $StudentonlineExam = StudentOnlineExam::factory()->create([
    //             'online_exam_id' => $onlineExam->id,
    //             'student_id' => $student->id,
    //         ]);

    //         $formQuestion = FormQuestion::factory()->create([
    //             'question_id' => $question->id,
    //             'form_id' => $form->id,
    //         ]);

    //         $studentAnswer = StudentAnswer::factory()->create([
    //             'question_id' => $question->id,
    //             'form_id' => $form->id,
    //             'student_id' => $student->id,
    //         ]);

    //         $practiseExamQuestion = PracticeExamQuestion::factory()->create([
    //             'practice_exam_id' => $practiseExam->id,
    //             'question_id' => $question->id,
    //         ]);
    //         $favoriteQuestion = FavoriteQuestion::factory()->create([
    //             'user_id' => $user->id,
    //             'question_id' => $question->id,
    //         ]);
    //     });

     }
}
