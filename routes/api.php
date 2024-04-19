<?php

use App\Models\Topic;
use App\Enums\Example;
use App\Enums\RoleEnum;

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

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\GuestController;
use App\Http\Controllers\TopicController;
// use App\Http\Controllers\Auth\UserController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\ChapterController;
use App\Http\Controllers\CollegeController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\CoursePartController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\UniversityController;
use App\Http\Controllers\CourseStudentController;
use App\Http\Controllers\CourseLecturerController;
use App\Http\Controllers\QuestionChoiceController;
use App\Http\Controllers\DepartmentCourseController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\ForgetPasswordController;
use App\Http\Controllers\DepartmentCoursePartController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\DepartmentCoursePartChapterTopicController;
use App\Http\Controllers\UserManagmentController;

// Route::post('register',[UserController::class,'register']);
Route::post('register',[GuestController::class,'addGuest']);


// Route::get('index',[UserController::class,'index']);

Route::post('forget_password',[ForgetPasswordController::class,'forget_password']);
Route::post('password_reset',[ResetPasswordController::class,'password_reset']);


Route::post('login',[UserController::class,'login']);
Route::get('logout',[UserController::class,'logout']);
// Route::put('change-password',[UserController::class,'changePassword']);

Route::middleware('auth:api')->group(function () {
    Route::get('userinfo',[UserController::class,'userInfo']);
    Route::post('email_verification',[EmailVerificationController::class, 'email_verification']);
    Route::get('email_verification',[EmailVerificationController::class, 'sendEmailVerification']);
    Route::put('update',[UserController::class, 'update']);
    Route::put('change-password',[UserController::class,'changePassword']);

});

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
    Route::get('retrieve', [CollegeController::class, 'retrieveCollege']); //http://127.0.0.1:8000/api/colleges/1
    Route::get('retrieve-list', [CollegeController::class, 'retrieveColleges']);
    Route::get('retrieve-basic-info-list', [CollegeController::class, 'retrieveBasicCollegesInfo']);
});

// department
Route::prefix('department/')->group(function () {
    Route::post('add', [DepartmentController::class, 'addDepartment']);
    Route::put('modify', [DepartmentController::class, 'modifyDepartment']);
    Route::delete('delete', [DepartmentController::class, 'deleteDepartment']);
    Route::get('retrieve', [DepartmentController::class, 'retrieveDepartment']); //http://127.0.0.1:8000/api/colleges/1
    Route::get('retrieve-list', [DepartmentController::class, 'retrieveDepartments']);
    Route::get('retrieve-basic-info-list', [DepartmentController::class, 'retrieveBasicDepartmentsInfo']);
});

//course
Route::prefix('course/')->group(function () {
    Route::post('add', [CourseController::class, 'addCourse']);
    Route::put('modify', [CourseController::class, 'modifyCourse']);
    Route::delete('delete', [CourseController::class, 'deleteCourse']);
    Route::get('retrieve-editable', [CourseController::class, 'retrieveEditableCourse']); //http://127.0.0.1:8000/api/colleges/1
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
    Route::get('retrieve-description', [TopicController::class, 'retrieveTopicDescription']);
    Route::get('retrieve-list', [TopicController::class, 'retrieveTopics']);
    Route::get('retrieve-available-list', [TopicController::class, 'retrieveAvailableTopics']);
});


//question
Route::prefix('question/')->group(function () {
    Route::post('add', [QuestionController::class, 'addQuestion']);
    Route::put('modify', [QuestionController::class, 'modifyQuestion']);
    Route::delete('delete', [QuestionController::class, 'deleteQuestion']);
    Route::get('retrieve', [QuestionController::class, 'retrieveQuestion']);
    Route::get('retrieve-editable', [QuestionController::class, 'retrieveEditableQuestion']);
    Route::get('retrieve-list', [QuestionController::class, 'retrieveQuestions']);
    Route::put('submit', [QuestionController::class, 'submitQuestionReviewRequest']);
    Route::put('accept', [QuestionController::class, 'acceptQuestion']);
    Route::put('reject', [QuestionController::class, 'rejectQuestion']);

});


//question choice
Route::prefix('question-choice/')->group(function () {
    Route::post('add', [QuestionChoiceController::class, 'addQuestionChoice']);
    Route::put('modify', [QuestionChoiceController::class, 'modifyQuestionChoice']);
    Route::delete('delete', [QuestionChoiceController::class, 'deleteQuestionChoice']);
    Route::get('retrieve', [QuestionChoiceController::class, 'retrieveQuestionChoice']);
    Route::get('retrieve-editable', [QuestionChoiceController::class, 'retrieveEditableQuestionChoice']);
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
    Route::post('add-topic-list', [DepartmentCoursePartChapterTopicController::class, 'addDepartmentCoursePartTopics']);
    Route::delete('delete-topic-list', [DepartmentCoursePartChapterTopicController::class, 'deleteDepartmentCoursePartTopics']);
    Route::get('retrieve-chapter-list', [DepartmentCoursePartChapterTopicController::class, 'retrieveDepartmentCoursePartChapters']);
    Route::get('retrieve-topic-list', [DepartmentCoursePartChapterTopicController::class, 'retrieveDepartmentCoursePartChapterTopics']);
    Route::get('retrieve-available-chapter-list', [DepartmentCoursePartChapterTopicController::class, 'retrieveAvailableDepartmentCoursePartChapters']);
    Route::get('retrieve-available-topic-list', [DepartmentCoursePartChapterTopicController::class, 'retrieveAvailableDepartmentCoursePartTopics']);

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
    Route::put('modify', [CourseStudentController::class, 'modifyCourseStudent']);
    Route::delete('delete', [CourseStudentController::class, 'deleteCourseStudent']);
    Route::get('retrieve-editable', [CourseStudentController::class, 'retrieveEditableCourseStudent']);
    Route::get('retrieve-list', [CourseStudentController::class, 'retrieveCourseStudents']);
    Route::get('retrieve-unlink-list', [CourseStudentController::class, 'retrieveUnlinkCourceStudents']);
    Route::put('pass', [CourseStudentController::class, 'passCourseStudent']);
    Route::put('suspend', [CourseStudentController::class, 'suspendCourseStudent']);

});



//guest
Route::prefix('guest/')->group(function () {
    Route::post('add', [GuestController::class, 'addGuest']);
    Route::put('modify', [GuestController::class, 'modifyGuest']);
    Route::get('retrieve-editable', [GuestController::class, 'retrieveEditableGuestProfile']);
});



//user management
Route::prefix('user-management/')->group(function () {
    Route::post('add', [UserManagmentController::class, 'addUser']);
    Route::put('modify-role-list', [UserManagmentController::class, 'modifyUserRoles']);
    Route::put('change-status', [UserManagmentController::class, 'changeUserStatus']);
    Route::delete('delete', [UserManagmentController::class, 'deleteUser']);
    Route::get('retrieve', [UserManagmentController::class, 'retrieveUser']);
    Route::get('retrieve-list', [UserManagmentController::class, 'retrieveUsers']);
    Route::get('retrieve-owner-role-list', [UserManagmentController::class, 'retrieveOwnerRoles']);
});



//user
Route::prefix('user/')->group(function () {
    Route::post('verify', [UserController::class, 'verifyAccount']);
    Route::post('login', [UserController::class, 'login']);
    Route::post('logout', [UserController::class, 'logou']);
    Route::put('change-password', [UserController::class, 'changePassword']);
    Route::put('request-account-recovery', [UserController::class, 'requestAccountReovery']);
    Route::put('change-password-after-account-recovery', [UserController::class, 'changePasswordAfterAccountReovery']);
    Route::get('retrieve-profile', [UserController::class, 'retrieveProfile']);
    
});


//test enum
Route::get('getenum', function () {
    $name = RoleEnum::values();
    return response()->json(['name' => $name]);
});
// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });
