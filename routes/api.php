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
use App\Http\Controllers\GuestController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\CollegeController;
use App\Http\Controllers\StudentController;
// use App\Http\Controllers\Auth\UserController;
use App\Http\Controllers\UserController;
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
    Route::get('retrieve-list-basic-info', [CollegeController::class, 'retrieveBasicCollegesInfo']);
    Route::put('modify', [CollegeController::class, 'modifyCollege']);
    Route::delete('delete', [CollegeController::class, 'deleteCollege']);
    Route::get('retrieve', [CollegeController::class, 'retrieveCollege']); //http://127.0.0.1:8000/api/colleges/1
});

// departments
Route::prefix('departments/')->group(function () {
    Route::get('retrieve-departments', [DepartmentController::class, 'retrieveDepartments']);
    Route::get('retrieve-basic-departments-info', [DepartmentController::class, 'retrieveBasicDepartmentsInfo']);
    Route::post('add-department', [DepartmentController::class, 'addDepartment']);
    Route::put('modify-department', [DepartmentController::class, 'modifyDepartment']);
    Route::delete('delete-department', [DepartmentController::class, 'deleteDepartment']);
    Route::get('retrieve-department', [DepartmentController::class, 'retrieveDepartment']); //http://127.0.0.1:8000/api/colleges/1
});

//courses
Route::put('courses/update',[CourseController::class,'update']);
Route::post('courses/create',[CourseController::class,'store']);






//test enum
Route::get('getenum', function () {
    $name = RoleEnum::values();
    return response()->json(['name' => $name]);
});
// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });
