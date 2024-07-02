<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Helpers\AddHelper;
use App\Helpers\GetHelper;
use Illuminate\Http\Request;
use App\Helpers\DeleteHelper;
use App\Helpers\ModifyHelper;
use App\Helpers\ResponseHelper;
use App\Helpers\ValidateHelper;
use App\Http\Requests\CourseRequest;
use Illuminate\Support\Facades\Validator;

class CourseController extends Controller
{

    public function addCourse(Request $request)
    {
        if( ValidateHelper::validateData($request, $this->rules($request))){
            return  ResponseHelper::clientError(401);
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

    public function modifyCourse (Request $request)
    {
        try {
            $course = Course::findOrFail($request->id);
            return ModifyHelper::modifyModel($request, $course,  $this->rules($request));
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }


    public function deleteCourse (Request $request)
    {
        try {
            $course = Course::findOrFail( $request->id);
            $course->delete();
            // return DeleteHelper::deleteModel($course);
            return ResponseHelper::success();
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function retrieveCourses()
    {
        // $attributes = ['id', 'arabic_name', 'english_name'];
        try {
            return GetHelper::retrieveModels(Course::class);
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function retrieveEditableCourse(Request $request)
    {
        $attributes = ['arabic_name', 'english_name'];
        $conditionAttribute = ['id' => $request->id];
        try {
            return GetHelper::retrieveModel(Course::class, $attributes, $conditionAttribute);
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
