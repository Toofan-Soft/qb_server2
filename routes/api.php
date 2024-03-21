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
use App\Http\Controllers\CourseController;
use App\Http\Controllers\CollegeController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\Auth\UserController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\UniversityController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\ForgetPasswordController;
use App\Http\Controllers\Auth\EmailVerificationController;


Route::post('register',[UserController::class,'register']);


// Route::get('index',[UserController::class,'index']);

Route::post('forget_password',[ForgetPasswordController::class,'forget_password']);
Route::post('password_reset',[ResetPasswordController::class,'password_reset']);


Route::post('login',[UserController::class,'login']);
Route::middleware('auth:api')->group(function () {
   // Route::get('login',[UserController::class,'login']);
    Route::get('userinfo',[UserController::class,'userInfo']);
    // Route::resource('products',ProductController::class);
    Route::post('email_verification',[EmailVerificationController::class, 'email_verification']);
    Route::get('email_verification',[EmailVerificationController::class, 'sendEmailVerification']);
    Route::put('update',[UserController::class, 'update']);

});

//univercity
Route::post('university/configure-university-data', [UniversityController::class, 'configureUniversityData']);
Route::put('/university/modify-university-data', [UniversityController::class, 'modifyUniversityData']);
Route::get('/university/retrieve-university-info', [UniversityController::class, 'retrieveUniversityInfo']);
Route::get('/university/retrieve-basic-university-info', [UniversityController::class, 'retrieveBasicUniversityInfo']);

//students
Route::post('/student/create',[StudentController::class,'create']);
Route::get('student/{id}/studentinfo',[StudentController::class,'studentInfo']);
Route::get('students',[StudentController::class,'index']);

// Colleges
Route::prefix('colleges/')->group(function () {
    Route::get('retrieve-colleges', [CollegeController::class, 'retrieveColleges']);
    Route::get('retrieve-basic-colleges-info', [CollegeController::class, 'retrieveBasicCollegesInfo']);
    Route::post('add-college', [CollegeController::class, 'addCollege']);
    Route::put('modify-college', [CollegeController::class, 'modifyCollege']);
    Route::delete('delete-college', [CollegeController::class, 'deleteCollege']);
    Route::get('retrieve-college', [CollegeController::class, 'retrieveCollege']); //http://127.0.0.1:8000/api/colleges/1
});

Route::get('retrieve-college', [CollegeController::class, 'retrieveCollege']);
//  Route::post('retrieve-college', [CollegeController::class, 'retrieveCollege']);
// departments
Route::get('departments/retrieve-departments',[DepartmentController::class,'retrieveDepartments']);
Route::get('departments/retrieve-basic-departments-info',[DepartmentController::class,'retrieveBasicDepartmentsInfo']);
Route::post('departments/add-department',[DepartmentController::class,'addDepartment']);
Route::put('departments/modify-department',[DepartmentController::class,'modifyDepartment']);
Route::delete('departments/delete-department',[DepartmentController::class,'deleteDepartment']);
Route::get('/departments/retrieve-department', [DepartmentController::class, 'retrieveDepartment']); //http://127.0.0.1:8000/api/colleges/1


//courses
Route::put('courses/update',[CourseController::class,'update']);
Route::post('courses/create',[CourseController::class,'store']);






//test enum
Route::get('getenum', function () {
    $name = RoleEnum::getEnum();
    return response()->json(['name' => $name]);
});
// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });
