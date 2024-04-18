<?php

use App\Enums\Example;
use App\Enums\RoleEnum;
use Illuminate\Http\Request;

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

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\GuestController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\ChapterController;
// use App\Http\Controllers\Auth\UserController;
use App\Http\Controllers\CollegeController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\CoursePartController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\UniversityController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\ForgetPasswordController;
use App\Http\Controllers\Auth\EmailVerificationController;


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
    Route::post('configure-university-data', [UniversityController::class, 'configureUniversityData']);
    Route::put('modify-university-data', [UniversityController::class, 'modifyUniversityData']);
    Route::get('retrieve-university-info', [UniversityController::class, 'retrieveUniversityInfo']);
    Route::get('retrieve-basic-university-info', [UniversityController::class, 'retrieveBasicUniversityInfo']);
});

//students
Route::prefix('students/')->group(function () {
    Route::post('add-mployee', [StudentController::class, 'addEmployee']);
    Route::put('modify-student', [StudentController::class, 'modifyStudent']);
    Route::delete('delete-studente', [StudentController::class, 'deleteStudent']);
    Route::get('retrieve-students', [StudentController::class, 'retrieveStudents']);
    Route::get('retrieve-student', [StudentController::class, 'retrieveStudent']);
});

// Colleges
Route::prefix('colleges/')->group(function () {
    Route::post('add', [CollegeController::class, 'addCollege']);
    Route::get('retrieve-list', [CollegeController::class, 'retrieveColleges']);
    Route::get('retrieve-basic-info-list', [CollegeController::class, 'retrieveBasicCollegesInfo']);
    Route::put('modify', [CollegeController::class, 'modifyCollege']);
    Route::delete('delete', [CollegeController::class, 'deleteCollege']);
    Route::get('retrieve', [CollegeController::class, 'retrieveCollege']); //http://127.0.0.1:8000/api/colleges/1
});

// departments
Route::prefix('departments/')->group(function () {
    Route::get('retrieve-list', [DepartmentController::class, 'retrieveDepartments']);
    Route::get('retrieve-basic-info-list', [DepartmentController::class, 'retrieveBasicDepartmentsInfo']);
    Route::post('add', [DepartmentController::class, 'addDepartment']);
    Route::put('modify', [DepartmentController::class, 'modifyDepartment']);
    Route::delete('delete', [DepartmentController::class, 'deleteDepartment']);
    Route::get('retrieve', [DepartmentController::class, 'retrieveDepartment']); //http://127.0.0.1:8000/api/colleges/1
});

//courses
Route::prefix('courses/')->group(function () {
    Route::get('retrieve-list', [CourseController::class, 'retrieveCourses']);
    Route::post('add', [CourseController::class, 'addCourse']);
    Route::put('modify', [CourseController::class, 'modifyCourse']);
    Route::delete('delete', [CourseController::class, 'deleteCourse']);
    Route::get('retrieve-editable', [CourseController::class, 'retrieveEditableCourse']); //http://127.0.0.1:8000/api/colleges/1
});


//courseParts
Route::prefix('courseParts/')->group(function () {
    Route::get('retrieve-list', [CoursePartController::class, 'retrieveCourseParts']);
    Route::post('add', [CoursePartController::class, 'addCoursePart']);
    Route::put('modify', [CoursePartController::class, 'modifyCoursePart']);
    Route::delete('delete', [CoursePartController::class, 'deleteCoursePart']);
    Route::get('retrieve-editable', [CoursePartController::class, 'retrieveEditableCoursePart']); //http://127.0.0.1:8000/api/colleges/1
});

//chapters
Route::prefix('chapters/')->group(function () {
    Route::get('retrieve-list', [ChapterController::class, 'retrieveChapters']);
    Route::get('retrieve-available-list', [ChapterController::class, 'retrieveAvailableChapters']);
    Route::post('add', [ChapterController::class, 'addChapter']);
    Route::put('modify', [ChapterController::class, 'modifyChapter']);
    Route::delete('delete', [ChapterController::class, 'deleteChapter']);
    Route::get('retrieve-editable', [ChapterController::class, 'retrieveEditableChapter']);
    Route::get('retrieve', [ChapterController::class, 'retrieveChapter']);
    Route::get('retrieve-description', [ChapterController::class, 'retrieveChapterDescription']);
});




//test enum
Route::get('getenum', function () {
    $name = RoleEnum::values();
    return response()->json(['name' => $name]);
});
// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });
