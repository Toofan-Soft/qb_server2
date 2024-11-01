<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Helpers\GetHelper;
use Illuminate\Http\Request;
use App\Helpers\ModifyHelper;
use App\Helpers\ResponseHelper;
use App\Helpers\ValidateHelper;
use Illuminate\Support\Facades\Gate;

class CourseController extends Controller
{

    public function addCourse(Request $request)
    {
        Gate::authorize('addCourse', CourseController::class);

        if (ValidateHelper::validateData($request, $this->rules($request))) {
            return  ResponseHelper::clientError();
        }
        try {
            $course = Course::create([
                'arabic_name' => $request->arabic_name,
                'english_name' => $request->english_name
            ]);

            return ResponseHelper::successWithData(['id' => $course->id]);
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function modifyCourse(Request $request)
    {
        Gate::authorize('modifyCourse', CourseController::class);

        try {
            $course = Course::findOrFail($request->id);
            return ModifyHelper::modifyModel($request, $course,  $this->rules($request));
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }


    public function deleteCourse(Request $request)
    {
        Gate::authorize('deleteCourse', CourseController::class);
        if (ValidateHelper::validateData($request, [
            'id' => 'required|integer'
        ])) {
            return  ResponseHelper::clientError();
        }
        try {
            $course = Course::findOrFail($request->id);
            $course->delete();
            return ResponseHelper::success();
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function retrieveCourses()
    {
        Gate::authorize('retrieveCourses', CourseController::class);

        $attributes = ['id', 'arabic_name', 'english_name'];
        try {
            $courses = GetHelper::retrieveModels(Course::class, $attributes);
            return ResponseHelper::successWithData($courses);
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function retrieveCourse(Request $request)
    {
        Gate::authorize('retrieveCourse', CourseController::class);
        if (ValidateHelper::validateData($request, [
            'id' => 'required|integer'
        ])) {
            return  ResponseHelper::clientError();
        }
        $attributes = ['arabic_name', 'english_name'];
        $conditionAttribute = ['id' => $request->id];
        try {
            $course = GetHelper::retrieveModel(Course::class, $attributes, $conditionAttribute);
            return ResponseHelper::successWithData($course);
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }
    public function retrieveEditableCourse(Request $request)
    {
        Gate::authorize('retrieveEditableCourse', CourseController::class);
        if (ValidateHelper::validateData($request, [
            'id' => 'required|integer'
        ])) {
            return  ResponseHelper::clientError();
        }
        $attributes = ['arabic_name', 'english_name'];
        $conditionAttribute = ['id' => $request->id];
        try {
            $course = GetHelper::retrieveModel(Course::class, $attributes, $conditionAttribute);
            return ResponseHelper::successWithData($course);
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function rules(Request $request): array
    {
        $rules = [
            'arabic_name' => 'required|string|max:255|unique:courses,arabic_name',
            'english_name' => 'required|string|max:255|unique:courses,english_name',
        ];
        if ($request->method() === 'PUT' || $request->method() === 'PATCH') {
            $rules = array_filter($rules, function ($attribute) use ($request) {
                // Ensure strict type comparison for security
                return $request->has($attribute);
            });
        }
        return $rules;
    }
}
