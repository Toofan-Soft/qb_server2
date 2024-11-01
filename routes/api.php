<?php

use App\Models\User;
use App\Models\Topic;
use App\Enums\Example;
use App\Enums\RoleEnum;
use App\Traits\EnumTraits;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

use App\Enums\ExamTypeEnum;

use App\Enums\SemesterEnum;
use App\Enums\UserRoleEnum;
use Illuminate\Http\Request;
// use App\Http\Controllers\Auth\UserController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Symfony\Component\Process\Process;
use App\Http\Controllers\UserController;
use App\Http\Controllers\EnumsController;
use App\Http\Controllers\GuestController;
use App\Http\Controllers\TopicController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\FilterController;
use App\Http\Controllers\ChapterController;
use App\Http\Controllers\CollegeController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\PaperExamController;
use App\Http\Controllers\CoursePartController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\UniversityController;
use App\Http\Controllers\PracticeExamController;
use App\Http\Controllers\PractiseExamController;
use App\Http\Controllers\CourseStudentController;
use App\Http\Controllers\UserManagmentController;
use App\Http\Controllers\CourseLecturerController;
use App\Http\Controllers\QuestionChoiceController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\InitialDatabaseController;
use App\Http\Controllers\TestDataAndTimeController;
use App\Http\Controllers\DepartmentCourseController;
use App\Http\Controllers\FavoriteQuestionController;
use App\Http\Controllers\ProctorOnlinExamController;
use App\Http\Controllers\StudentOnlinExamController;
use App\Http\Controllers\LecturerOnlinExamController;
use App\Http\Controllers\StudentOnlineExamController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\LecturerOnlineExamController;
use App\Http\Controllers\Auth\ForgetPasswordController;
use robertogallea\LaravelPython\Services\LaravelPython;
use App\Http\Controllers\DepartmentCoursePartController;
use App\Http\Controllers\Auth\EmailVerificationController;
use Symfony\Component\Process\Exception\ProcessFailedException;
use App\Http\Controllers\DepartmentCoursePartChapterTopicController;

Route::prefix('guest/')->group(function () {
    Route::post('add', [GuestController::class, 'addGuest']);
});
Route::put('user/resend-code', [UserController::class, 'resendCode']);
Route::post('user/verify', [UserController::class, 'verifyAccount']);
Route::post('user/verify-account-after-recovery', [UserController::class, 'verifyAccountAfterRecvery']);
Route::post('user/login', [UserController::class, 'login']);
Route::put('user/request-account-recovery', [UserController::class, 'requestAccountReovery']);
Route::put('user/change-password-after-account-recovery', [UserController::class, 'changePasswordAfterAccountReovery']);
Route::get('enum/retrieve-register-gender-list', [EnumsController::class, 'retrieveRegisterGenders']);
// Route::post('employee/add', [EmployeeController::class, 'addEmployee']);
// Route::post('user-management/add', [UserManagementController::class, 'addUser']);


// Route::post('forget_password',[ForgetPasswordController::class,'forget_password']);
// Route::post('password_reset',[ResetPasswordController::class,'password_reset']);
// Route::get('logout',[UserController::class,'logout']);
// Route::put('change-password',[UserController::class,'changePassword']);

// Route::post('paper-exam/add', [PaperExamController::class, 'addPaperExam']);
// Route::get('paper-exam/retrieve-list', [PaperExamController::class, 'retrievePaperExams1']);

Route::middleware('auth:api')->group(function () {
    // Route::post('email_verification', [EmailVerificationController::class, 'email_verification']);
    // Route::get('email_verification', [EmailVerificationController::class, 'sendEmailVerification']);
    // Route::put('change-password', [UserController::class, 'changePassword']);

    //lecturer onlineExam
    // Route::post('lecturer-online-exam/add', [LecturerOnlineExamController::class, 'addOnlineExam']);

    //paper exam
    // Route::post('paper-exam/add', [PaperExamController::class, 'addPaperExam']);
    // Route::get('paper-exam/retrieve-list', [PaperExamController::class, 'retrievePaperExams1']);
    // Route::get('paper-exam/retrieve-android-list', [PaperExamController::class, 'retrievePaperExamsAndroid']);

    //practice-exam
    // Route::post('practice-exam/add', [PracticeExamController::class, 'addPracticeExam']);
    // Route::get('practice-exam/retrieve-list', [PracticeExamController::class, 'retrievePractiseExams']);

    // user
    // Route::get('user/retrieve-profile', [UserController::class, 'retrieveProfile']);
    // Route::get('user/login1', [UserController::class, 'login1']);
    // Route::get('filter/retrieve-lecturer-college-list', [FilterController::class, 'retrieveLecturerColleges']);

    // });///

    //univercity
    Route::prefix('university/')->group(function () {
        Route::post('configure', [UniversityController::class, 'configureUniversityData']);
        Route::put('modify', [UniversityController::class, 'modifyUniversityData']);
        Route::get('retrieve', [UniversityController::class, 'retrieveUniversityInfo']);
        Route::get('retrieve-basic-info', [UniversityController::class, 'retrieveBasicUniversityInfo']);
    });

    // College
    Route::prefix('college/')->group(function () {
        Route::post('add', [CollegeController::class, 'addCollege']);
        Route::put('modify', [CollegeController::class, 'modifyCollege']);
        Route::delete('delete', [CollegeController::class, 'deleteCollege']);
        Route::get('retrieve', [CollegeController::class, 'retrieveCollege']);
        Route::get('retrieve-editable', [CollegeController::class, 'retrieveEditableCollege']); // حالي تعتبر نفس الاجراع الفردي
        Route::get('retrieve-list', [CollegeController::class, 'retrieveColleges']);
        Route::get('retrieve-basic-info-list', [CollegeController::class, 'retrieveBasicCollegesInfo']);
    });

    // department
    Route::prefix('department/')->group(function () {
        Route::post('add', [DepartmentController::class, 'addDepartment']);
        Route::put('modify', [DepartmentController::class, 'modifyDepartment']);
        Route::delete('delete', [DepartmentController::class, 'deleteDepartment']);
        Route::get('retrieve', [DepartmentController::class, 'retrieveDepartment']);
        Route::get('retrieve-editable', [DepartmentController::class, 'retrieveEditableDepartment']);
        Route::get('retrieve-list', [DepartmentController::class, 'retrieveDepartments']);
        Route::get('retrieve-basic-info-list', [DepartmentController::class, 'retrieveBasicDepartmentsInfo']);
    });

    //course
    Route::prefix('course/')->group(function () {
        Route::post('add', [CourseController::class, 'addCourse']);
        Route::put('modify', [CourseController::class, 'modifyCourse']);
        Route::delete('delete', [CourseController::class, 'deleteCourse']);
        Route::get('retrieve', [CourseController::class, 'retrieveCourse']);
        Route::get('retrieve-editable', [CourseController::class, 'retrieveEditableCourse']);
        Route::get('retrieve-list', [CourseController::class, 'retrieveCourses']);
    });


    //course part
    Route::prefix('course-part/')->group(function () {
        Route::post('add', [CoursePartController::class, 'addCoursePart']);
        Route::put('modify', [CoursePartController::class, 'modifyCoursePart']);
        Route::delete('delete', [CoursePartController::class, 'deleteCoursePart']);
        Route::get('retrieve-editable', [CoursePartController::class, 'retrieveEditableCoursePart']); //http://127.0.0.1:8000/api/colleges/1
        Route::get('retrieve-list', [CoursePartController::class, 'retrieveCourseParts']);
    });

    //chapter
    Route::prefix('chapter/')->group(function () {
        Route::post('add', [ChapterController::class, 'addChapter']);
        Route::put('modify', [ChapterController::class, 'modifyChapter']);
        Route::delete('delete', [ChapterController::class, 'deleteChapter']);
        Route::get('retrieve', [ChapterController::class, 'retrieveChapter']);
        Route::get('retrieve-editable', [ChapterController::class, 'retrieveEditableChapter']);
        Route::get('retrieve-description', [ChapterController::class, 'retrieveChapterDescription']);
        Route::get('retrieve-list', [ChapterController::class, 'retrieveChapters']);
        Route::get('retrieve-available-list', [ChapterController::class, 'retrieveAvailableChapters']);
    });


    //topic
    Route::prefix('topic/')->group(function () {
        Route::post('add', [TopicController::class, 'addTopic']);
        Route::put('modify', [TopicController::class, 'modifyTopic']);
        Route::delete('delete', [TopicController::class, 'deleteTopic']);
        Route::get('retrieve', [TopicController::class, 'retrieveTopic']);
        Route::get('retrieve-editable', [TopicController::class, 'retrieveEditableTopic']);
        Route::get('retrieve-description', [TopicController::class, 'retrieveTopicDescription']);
        Route::get('retrieve-list', [TopicController::class, 'retrieveTopics']);
        Route::get('retrieve-available-list', [TopicController::class, 'retrieveAvailableTopics']);
    });


    //question
    Route::prefix('question/')->group(function () {
        Route::post('add', [QuestionController::class, 'addQuestion']);
        Route::put('modify', [QuestionController::class, 'modifyQuestion']);
        Route::put('submit', [QuestionController::class, 'submitQuestionReviewRequest']);
        Route::put('withdraw-submit', [QuestionController::class, 'withdrawSubmitQuestionReviewRequest']);
        Route::put('accept', [QuestionController::class, 'acceptQuestion']);
        Route::put('reject', [QuestionController::class, 'rejectQuestion']);
        Route::delete('delete', [QuestionController::class, 'deleteQuestion']);
        Route::get('retrieve', [QuestionController::class, 'retrieveQuestion']);
        Route::get('retrieve-editable', [QuestionController::class, 'retrieveEditableQuestion']);
        Route::get('retrieve-list', [QuestionController::class, 'retrieveQuestions']);
    });

    //department course
    Route::prefix('department-course/')->group(function () {
        Route::post('add', [DepartmentCourseController::class, 'addDepartmentCourse']);
        Route::put('modify', [DepartmentCourseController::class, 'modifyDepartmentCourse']);
        Route::delete('delete', [DepartmentCourseController::class, 'deleteDepartmentCourse']);
        Route::get('retrieve', [DepartmentCourseController::class, 'retrieveDepartmentCourse']);
        Route::get('retrieve-editable', [DepartmentCourseController::class, 'retrieveEditableDepartmentCourse']);
        Route::get('retrieve-list', [DepartmentCourseController::class, 'retrieveDepartmentCourses']);
        Route::get('retrieve-course-department-list', [DepartmentCourseController::class, 'retrieveCourseDepartments']);
        Route::get('retrieve-level-course-list', [DepartmentCourseController::class, 'retrieveDepartmentLevelCourses']);
    });

    //department course part
    Route::prefix('department-course-part/')->group(function () {
        Route::post('add', [DepartmentCoursePartController::class, 'addDepartmentCoursePart']);
        Route::put('modify', [DepartmentCoursePartController::class, 'modifyDepartmentCoursePart']);
        Route::delete('delete', [DepartmentCoursePartController::class, 'deleteDepartmentCoursePart']);
        Route::get('retrieve-editable', [DepartmentCoursePartController::class, 'retrieveEditableDepartmentCoursePart']);
    });

    // department-course-part-chapter-and-topic
    Route::prefix('department-course-part-chapter-and-topic/')->group(function () {
        Route::post('modify-topic-list', [DepartmentCoursePartChapterTopicController::class, 'modifyDepartmentCoursePartTopics']);
        Route::get('retrieve-chapter-list', [DepartmentCoursePartChapterTopicController::class, 'retrieveDepartmentCoursePartChapters']);
        Route::get('retrieve-topic-list', [DepartmentCoursePartChapterTopicController::class, 'retrieveDepartmentCoursePartChapterTopics']);
        Route::get('retrieve-editable-chapter-list', [DepartmentCoursePartChapterTopicController::class, 'retrieveEditableDepartmentCoursePartChapters']);
        Route::get('retrieve-editable-topic-list', [DepartmentCoursePartChapterTopicController::class, 'retrieveEditableDepartmentCoursePartTopics']);
    });

    //employee
    Route::prefix('employee/')->group(function () {
        Route::post('add', [EmployeeController::class, 'addEmployee']);
        Route::put('modify', [EmployeeController::class, 'modifyEmployee']);
        Route::delete('delete', [EmployeeController::class, 'deleteEmployee']);
        Route::get('retrieve', [EmployeeController::class, 'retrieveEmployee']);
        Route::get('retrieve-editable', [EmployeeController::class, 'retrieveEditableEmployee']);
        Route::get('retrieve-list', [EmployeeController::class, 'retrieveEmployees']);
    });


    //course lecturer
    Route::prefix('course-lecturer/')->group(function () {
        Route::post('add', [CourseLecturerController::class, 'addCourseLecturer']);
        Route::delete('delete', [CourseLecturerController::class, 'deleteCourseLecturer']);
        Route::get('retrieve', [CourseLecturerController::class, 'retrieveCourseLecturer']);
        Route::get('retrieve-list', [CourseLecturerController::class, 'retrieveCourseLecturers']);
        Route::get('retrieve-lecturer-course-list', [CourseLecturerController::class, 'retrieveLecturerCourses']);
    });


    //student
    Route::prefix('student/')->group(function () {
        Route::post('add', [StudentController::class, 'addStudent']);
        Route::put('modify', [StudentController::class, 'modifyStudent']);
        Route::delete('delete', [StudentController::class, 'deleteStudent']);
        Route::get('retrieve', [StudentController::class, 'retrieveStudent']);
        Route::get('retrieve-editable', [StudentController::class, 'retrieveEditableStudent']);
        Route::get('retrieve-list', [StudentController::class, 'retrieveStudents']);
    });


    //course student
    Route::prefix('course-student/')->group(function () {
        Route::post('add-list', [CourseStudentController::class, 'addCourseStudents']);
        Route::put('pass', [CourseStudentController::class, 'passCourseStudent']);
        Route::put('suspend', [CourseStudentController::class, 'suspendCourseStudent']);
        Route::put('unsuspend', [CourseStudentController::class, 'unsuspendCourseStudent']);
        Route::delete('delete', [CourseStudentController::class, 'deleteCourseStudent']);
        Route::get('retrieve-list', [CourseStudentController::class, 'retrieveCourseStudents']);
        Route::get('retrieve-unlink-list', [CourseStudentController::class, 'retrieveUnlinkCourceStudents']);
    });


    //guest
    Route::prefix('guest/')->group(function () {
        // Route::post('add', [GuestController::class, 'addGuest']);
        Route::put('modify', [GuestController::class, 'modifyGuest']);
        Route::get('retrieve-editable', [GuestController::class, 'retrieveEditableGuestProfile']);
    });


    // user management
    Route::prefix('user-management/')->group(function () {
        Route::post('add', [UserManagementController::class, 'addUser']);
        Route::put('modify-role-list', [UserManagementController::class, 'modifyUserRoles']);
        Route::put('change-status', [UserManagementController::class, 'changeUserStatus']);
        Route::delete('delete', [UserManagementController::class, 'deleteUser']);
        Route::get('retrieve', [UserManagementController::class, 'retrieveUser']);
        Route::get('retrieve-list', [UserManagementController::class, 'retrieveUsers']);
        Route::get('retrieve-owner-role-list', [UserManagementController::class, 'retrieveOwnerRoles']);
    });

    //user
    Route::prefix('user/')->group(function () {
        // Route::post('verify', [UserController::class, 'verifyAccount']);
        // Route::post('login', [UserController::class, 'login']);
        Route::post('logout', [UserController::class, 'logout']);
        // Route::put('resend-code', [UserController::class, 'resendCode']);
        Route::put('change-password', [UserController::class, 'changePassword']);
        Route::put('change-language', [UserController::class, 'changeLanguage']);
        // Route::put('request-account-recovery', [UserController::class, 'requestAccountReovery']);
        // Route::put('change-password-after-account-recovery', [UserController::class, 'changePasswordAfterAccountReovery']);
        // Route::post('verify-account-after-recovery', [UserController::class, 'verifyAccountAfterRecvery']);
        Route::get('retrieve-profile', [UserController::class, 'retrieveProfile']);

    });


    //lecturer online exam
    Route::prefix('lecturer-online-exam/')->group(function () {
        Route::post('add', [LecturerOnlineExamController::class, 'addOnlineExam']);
        Route::put('modify', [LecturerOnlineExamController::class, 'modifyOnlineExam']);
        Route::put('change-status', [LecturerOnlineExamController::class, 'changeOnlineExamStatus']);
        Route::delete('delete', [LecturerOnlineExamController::class, 'deleteOnlineExam']);
        Route::get('retrieve', [LecturerOnlineExamController::class, 'retrieveOnlineExam']);
        Route::get('retrieve-editable', [LecturerOnlineExamController::class, 'retrieveEditableOnlineExam']);
        Route::get('retrieve-list', [LecturerOnlineExamController::class, 'retrieveOnlineExams']);
        Route::get('retrieve-android-list', [LecturerOnlineExamController::class, 'retrieveOnlineExamsAndroid']);
        Route::get('retrieve-chapter-list', [LecturerOnlineExamController::class, 'retrieveOnlineExamChapters']);
        Route::get('retrieve-chapter-topic-list', [LecturerOnlineExamController::class, 'retrieveOnlineExamChapterTopics']);
        Route::get('retrieve-form-list', [LecturerOnlineExamController::class, 'retrieveOnlineExamForms']);
        Route::get('retrieve-form-question-list', [LecturerOnlineExamController::class, 'retrieveOnlineExamFormQuestions']);
    });


    //student online exam
    Route::prefix('student-online-exam/')->group(function () {
        Route::post('save-question-answer', [StudentOnlineExamController::class, 'saveOnlineExamQuestionAnswer']);
        Route::put('finish', [StudentOnlineExamController::class, 'finishOnlineExam']);
        Route::get('retrieve', [StudentOnlineExamController::class, 'retrieveOnlineExam']);
        Route::get('retrieve-list', [StudentOnlineExamController::class, 'retrieveOnlineExams']);
        Route::get('retrieve-question-list', [StudentOnlineExamController::class, 'retrieveOnlineExamQuestions']);
    });


    //proctor online exam
    Route::prefix('proctor-online-exam/')->group(function () {
        Route::post('start-student', [ProctorOnlinExamController::class, 'startStudentOnlineExam']); //error not found method
        Route::put('finish', [ProctorOnlinExamController::class, 'finishOnlineExam']); //error not found method
        Route::put('suspend-student', [ProctorOnlinExamController::class, 'suspendStudentOnlineExam']);
        Route::put('continue-student', [ProctorOnlinExamController::class, 'continueStudentOnlineExam']);
        Route::put('finish-student', [ProctorOnlinExamController::class, 'finishStudentOnlineExam']);
        Route::get('retrieve', [ProctorOnlinExamController::class, 'retrieveOnlineExam']);
        Route::get('retrieve-list', [ProctorOnlinExamController::class, 'retrieveOnlineExams']);
        Route::get('retrieve-student-list', [ProctorOnlinExamController::class, 'retrieveOnlineExamStudents']);
    });


    //paper exam
    Route::prefix('paper-exam/')->group(function () {
        Route::post('add', [PaperExamController::class, 'addPaperExam']);
        Route::put('modify', [PaperExamController::class, 'modifyPaperExam']);
        Route::delete('delete', [PaperExamController::class, 'deletePaperExam']);
        Route::get('retrieve', [PaperExamController::class, 'retrievePaperExam']);
        Route::get('retrieve-editable', [PaperExamController::class, 'retrieveEditablePaperExam']);
        Route::get('retrieve-list', [PaperExamController::class, 'retrievePaperExams']);
        Route::get('retrieve-android-list', [PaperExamController::class, 'retrievePaperExamsAndroid']);
        Route::get('retrieve-chapter-list', [PaperExamController::class, 'retrievePaperExamChapters']);
        Route::get('retrieve-chapter-topic-list', [PaperExamController::class, 'retrievePaperExamChapterTopics']);
        Route::get('retrieve-form-list', [PaperExamController::class, 'retrievePaperExamForms']);
        Route::get('retrieve-form-question-list', [PaperExamController::class, 'retrievePaperExamFormQuestions']);
        Route::get('export', [PaperExamController::class, 'exportPaperExamToPDF']);
    });


    //practice exam
    Route::prefix('practice-exam/')->group(function () {
        Route::post('add', [PracticeExamController::class, 'addPracticeExam']);
        Route::put('modify', [PracticeExamController::class, 'modifyPracticeExam']);
        Route::put('start', [PracticeExamController::class, 'startPracticeExam']);
        Route::put('suspend', [PracticeExamController::class, 'suspendPracticeExam']);
        Route::put('continue', [PracticeExamController::class, 'continuePracticeExam']);
        Route::put('finish', [PracticeExamController::class, 'finishPracticeExam']);
        Route::put('save-question-answer', [PracticeExamController::class, 'savePracticeExamQuestionAnswer']);
        Route::delete('delete', [PracticeExamController::class, 'deletePracticeExam']);
        Route::get('retrieve', [PracticeExamController::class, 'retrievePracticeExam']);
        Route::get('retrieve-editable', [PracticeExamController::class, 'retrieveEditablePracticeExam']);
        Route::get('retrieve-result', [PracticeExamController::class, 'retrievePracticeExamResult']);
        Route::get('retrieve-list', [PracticeExamController::class, 'retrievePracticeExams']);
        Route::get('retrieve-android-list', [PracticeExamController::class, 'retrievePracticeExamsAndroid']);
        Route::get('retrieve-question-list', [PracticeExamController::class, 'retrievePracticeExamQuestions']);
    });

    //favorite question
    Route::prefix('favorite-question/')->group(function () {
        Route::post('add', [FavoriteQuestionController::class, '']);
        Route::delete('delete', [FavoriteQuestionController::class, '']);
        Route::get('retrieve', [FavoriteQuestionController::class, '']);
        Route::get('check', [FavoriteQuestionController::class, '']);
        Route::get('retrieve-list', [FavoriteQuestionController::class, '']);
    });


    //enum
    Route::prefix('enum/')->group(function () {
        Route::get('retrieve-course-part-status-list', [EnumsController::class, 'retrieveCoursePartStatus']);
        Route::get('retrieve-chapter-status-list', [EnumsController::class, 'retrieveChapterStatus']);
        Route::get('retrieve-course-part-list', [EnumsController::class, 'retrieveCourseParts']);
        Route::get('retrieve-language-list', [EnumsController::class, 'retrieveLanguages']);
        Route::get('retrieve-difficulty-level-list', [EnumsController::class, 'retrieveDifficultyLevels']);
        Route::get('retrieve-question-type-list', [EnumsController::class, 'retrieveQuestionTypes']);
        Route::get('retrieve-question-status-list', [EnumsController::class, 'retrieveQuestionStatus']);
        Route::get('retrieve-accessibility-status-list', [EnumsController::class, 'retrieveAccessibilityStatus']);
        Route::get('retrieve-semester-list', [EnumsController::class, 'retrieveSemesters']);
        Route::get('retrieve-levels-count-list', [EnumsController::class, 'retrieveLevelsCounts']);
        Route::get('retrieve-job-type-list', [EnumsController::class, 'retrieveJobTypes']);
        Route::get('retrieve-qualification-list', [EnumsController::class, 'retrieveQualifications']);
        Route::get('retrieve-gender-list', [EnumsController::class, 'retrieveGenders']);
        Route::get('retrieve-course-student-status-list', [EnumsController::class, 'retrieveCourseStudentStatus']);
        Route::get('retrieve-owner-type-list', [EnumsController::class, 'retrieveOwnerTypes']);
        Route::get('retrieve-user-status-list', [EnumsController::class, 'retrieveUserStatus']);
        Route::get('retrieve-conduct-method-list', [EnumsController::class, 'retrieveConductMethods']);
        Route::get('retrieve-exam-type-list', [EnumsController::class, 'retrieveExamTypes']);
        Route::get('retrieve-form-configuration-method-list', [EnumsController::class, 'retrieveformConfigurationMethods']);
        Route::get('retrieve-form-name-method-list', [EnumsController::class, 'retrieveformNameMethods']);
        Route::get('retrieve-online-exam-status-list', [EnumsController::class, 'retrieveOnlineExamStatus']);
        Route::get('retrieve-practice-exam-status-list', [EnumsController::class, 'retrievePracticeExamStatus']);
        Route::get('retrieve-student-online-exam-status-list', [EnumsController::class, 'retrieveStudentOnlineExamStatus']);
        Route::get('retrieve-online-exam-taking-status-list', [EnumsController::class, 'retrieveOnlineExamTakingStatus']);
    });


    //filter
    Route::prefix('filter/')->group(function () {
        Route::get('retrieve-course-list', [FilterController::class, 'retrieveCourses']);
        Route::get('retrieve-course-part-list', [FilterController::class, 'retrieveCourseParts']);
        Route::get('retrieve-chapter-list', [FilterController::class, 'retrieveChapters']);
        Route::get('retrieve-topic-list', [FilterController::class, 'retrieveTopics']);
        Route::get('retrieve-college-list', [FilterController::class, 'retrieveColleges']);
        Route::get('retrieve-lecturer-college-list', [FilterController::class, 'retrieveLecturerColleges']);
        Route::get('retrieve-lecturer-current-college-list', [FilterController::class, 'retrieveLecturerCurrentColleges']);
        Route::get('retrieve-department-list', [FilterController::class, 'retrieveDepartments']);
        Route::get('retrieve-lecturer-department-list', [FilterController::class, 'retrieveLecturerDepartments']);
        Route::get('retrieve-lecturer-current-department-list', [FilterController::class, 'retrieveLecturerCurrentDepartments']);
        Route::get('retrieve-department-level-list', [FilterController::class, 'retrieveDepartmentLevels']);
        Route::get('retrieve-department-course-list', [FilterController::class, 'retrieveDepartmentCourses']);
        Route::get('retrieve-department-level-course-list', [FilterController::class, 'retrieveDepartmentLevelCourses']);
        Route::get('retrieve-department-level-semester-course-list', [FilterController::class, 'retrieveDepartmentLevelSemesterCourses']);
        Route::get('retrieve-department-course-part-list', [FilterController::class, 'retrieveDepartmentCourseParts']);
        Route::get('retrieve-department-lecturer-course-list', [FilterController::class, 'retrieveDepartmentLecturerCourses']);
        Route::get('retrieve-department-lecturer-current-course-list', [FilterController::class, 'retrieveDepartmentLecturerCurrentCourses']);
        Route::get('retrieve-department-lecturer-course-part-list', [FilterController::class, 'retrieveDepartmentLecturerCourseParts']);
        Route::get('retrieve-department-lecturer-current-course-part-list', [FilterController::class, 'retrieveDepartmentLecturerCurrentCourseParts']);
        Route::get('retrieve-employee-list', [FilterController::class, 'retrieveEmployees']);
        Route::get('retrieve-lecturer-list', [FilterController::class, 'retrieveLecturers']);
        Route::get('retrieve-employee-of-job-list', [FilterController::class, 'retrieveEmployeesOfJob']);
        // Route::get('retrieve-academic-year-list', [FilterController::class, 'retrieveAcademicYears']);
        Route::get('retrieve-non-owner-employee-list', [FilterController::class, 'retrieveNonOwnerEmployees']);
        Route::get('retrieve-non-owner-student-list', [FilterController::class, 'retrieveNonOwnerStudents']);
        Route::get('retrieve-role-list', [FilterController::class, 'retrieveRoles']);
        Route::get('retrieve-proctor-list', [FilterController::class, 'retrieveProctors']);
    });
}); ///

//for test with out need to login
Route::prefix('test/')->group(function () {
    Route::post('add', [InitialDatabaseController::class, 'initialDatabase']);
    // Route::put('modify', [QuestionController::class, 'acceptQuestion']);
    // Route::put('change-status', [UserManagementController::class, 'changeUserStatus']);
    // Route::delete('delete', [UserManagementController::class, 'deleteUser']);
    // Route::get('retrieve', [UserManagementController::class, 'retrieveUser']);
    // Route::get('retrieve-list', [UserManagementController::class, 'retrieveUsers']);
});

//enum

//test enum
Route::get('getenum', function () {
    // $englishNames = EnumTraits::getEnglishNames(SemesterEnum::class);
    // $arabicNames = EnumTraits::getArabicNames(SemesterEnum::class);
    // $nameByNumber = EnumTraits::getNameByNumber(1,  SemesterEnum::class, 'en');
    // $enumArray = EnumTraits::getEnum(SemesterEnum::class, 'en');


});

///////////
Route::get('execute-python', function () {
    // $command = 'python ' . base_path() . '\app\Scripts\example.py '  ;
    // $output = shell_exec($command);
    // return $output;

    $arrayData = [
        'id' => 1,
        'name' => 'nasser',
        'age' => 22,
    ];

    $jsonData = json_encode($arrayData);
    $methodName = 'method_one';
    $process = new Process([
        'C:\Users\Nasser\AppData\Local\Programs\Python\Python39\python.exe',
        base_path() . '\app\Scripts\example.py',
        $methodName,
        $jsonData
    ]);

    $process->setEnv([
        'SYSTEMROOT' => getenv('SYSTEMROOT'),
        'PATH' => getenv('PATH')
    ]);

    $process->run();

    if (!$process->isSuccessful()) {
        throw new ProcessFailedException($process);
    }

    // $updatedArray = json_decode($process->getOutput(), true);
    return $process->getOutput();
});

Route::get('test', function () {
    return DB::table('true_false_questions')
        //  ->join('department_courses', 'departments.id', '=', 'department_courses.department_id')
        //  ->join('course_students', 'department_courses.id', '=', 'course_students.department_course_id')
        //  ->join('students', 'course_students.student_id', '=', 'students.id')
        //  ->select('students.id', 'students.academic_id', 'students.arabic_name as name', 'gender as gender_name', 'image_url')
        //  ->where('departments.id', '=', 1)
        //  ->where('department_courses.level', '=', 1)
        //  ->distinct()
        ->get();
});
