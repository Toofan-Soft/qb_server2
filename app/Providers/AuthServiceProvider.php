<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;

use App\Policies\UserPolicy;
use App\Policies\EnumsPolicy;
use App\Policies\GuestPolicy;
use App\Policies\TopicPolicy;
use App\Policies\CoursePolicy;
use App\Policies\FilterPolicy;
use App\Policies\ChapterPolicy;
use App\Policies\CollegePolicy;
use App\Policies\StudentPolicy;
use App\Policies\EmployeePolicy;
use App\Policies\QuestionPolicy;
use App\Policies\PaperExamPolicy;
use App\Policies\CoursePartPolicy;
use App\Policies\DepartmentPolicy;
use App\Policies\UniversityPolicy;
use App\Policies\PracticeExamPolicy;
use App\Policies\CourseStudentPolicy;
use App\Policies\CourseLecturerPolicy;
use App\Policies\QuestionChoicePolicy;
use App\Policies\UserManagementPolicy;
use App\Http\Controllers\UserController;
use App\Policies\DepartmentCoursePolicy;
use App\Policies\FavoriteQuestionPolicy;
use App\Http\Controllers\EnumsController;
use App\Http\Controllers\GuestController;
use App\Http\Controllers\TopicController;
use App\Policies\ProctorOnlineExamPolicy;
use App\Policies\StudentOnlineExamPolicy;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\FilterController;
use App\Policies\LecturerOnlineExamPolicy;
use App\Http\Controllers\ChapterController;
use App\Http\Controllers\CollegeController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\QuestionController;
use App\Policies\DepartmentCoursePartPolicy;
use App\Http\Controllers\PaperExamController;
use App\Http\Controllers\CoursePartController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\UniversityController;
use App\Http\Controllers\PracticeExamController;
use App\Http\Controllers\CourseStudentController;
use App\Http\Controllers\CourseLecturerController;
use App\Http\Controllers\QuestionChoiceController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\DepartmentCourseController;
use App\Http\Controllers\FavoriteQuestionController;
use App\Http\Controllers\ProctorOnlinExamController;
use App\Http\Controllers\StudentOnlineExamController;
use App\Http\Controllers\LecturerOnlineExamController;
use App\Http\Controllers\DepartmentCoursePartController;
use App\Policies\DepartmentCoursePartChapterTopicPolicy;
use App\Http\Controllers\DepartmentCoursePartChapterTopicController;
use Laravel\Passport\Passport;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models' => 'App\Policies\ModelPolicy',
        // 'App\Controllers' => 'App\Policies\ControllerPolicy',
        UniversityController::class => UniversityPolicy::class,
        CollegeController::class => CollegePolicy::class,
        DepartmentController::class => DepartmentPolicy::class,
        CourseController::class => CoursePolicy::class,
        CoursePartController::class => CoursePartPolicy::class,
        ChapterController::class => ChapterPolicy::class,
        TopicController::class => TopicPolicy::class,
        QuestionController::class => QuestionPolicy::class,
        QuestionChoiceController::class => QuestionChoicePolicy::class,
        DepartmentCourseController::class => DepartmentCoursePolicy::class,
        DepartmentCoursePartController::class => DepartmentCoursePartPolicy::class,
        DepartmentCoursePartChapterTopicController::class => DepartmentCoursePartChapterTopicPolicy::class,        EmployeeController::class => EmployeePolicy::class,
        CourseLecturerController::class => CourseLecturerPolicy::class,
        StudentController::class => StudentPolicy::class,
        CourseStudentController::class => CourseStudentPolicy::class,
        GuestController::class => GuestPolicy::class,
        UserManagementController::class => UserManagementPolicy::class,
        UserController::class => UserPolicy::class,
        LecturerOnlineExamController::class => LecturerOnlineExamPolicy::class,
        StudentOnlineExamController::class => StudentOnlineExamPolicy::class,
        ProctorOnlinExamController::class => ProctorOnlineExamPolicy::class,
        PaperExamController::class => PaperExamPolicy::class,
        PracticeExamController::class => PracticeExamPolicy::class,
        FavoriteQuestionController::class => FavoriteQuestionPolicy::class,
        EnumsController::class => EnumsPolicy::class,
        FilterController::class => FilterPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
        //Passport::routes();

        // if (! $this->app->routesAreCached()) {
            // Passport::routes();
        // }
    }
}
