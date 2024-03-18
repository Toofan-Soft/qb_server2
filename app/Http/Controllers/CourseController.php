<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Helpers\AddHelper;
use App\Helpers\GetHelper;
use Illuminate\Http\Request;
use App\Helpers\DeleteHelper;
use App\Helpers\ModifyHelper;
use App\Http\Requests\CourseRequest;
use Illuminate\Support\Facades\Validator;

class CourseController extends Controller
{

    // 	add course (arabic name, english name) : {}
    // 	modify course (id, arabic name?, english name?) : {}
    // 	delete course (id) : {}
    // 	retrieve courses () : { [id, arabic name, english name] }

    public function addCourse(Request $request)
    {
      return AddHelper::addModel($request, Course::class,  $this->rules($request));
    }

    public function modifyCourse (Request $request, Course $course)
    {
        return ModifyHelper::modifyModel($request, $course,  $this->rules($request));
    }


    public function deleteCourse (Course $course)
    {
        return DeleteHelper::deleteModel($course);
    }

    public function retrieveCourses(Request $request)
    {
        $attributes = ['id', 'arabic_name', 'english_name'];
        return GetHelper::retrieveModels(Course::class, $attributes, null);
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
