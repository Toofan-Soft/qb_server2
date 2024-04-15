<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\College;
use App\Helpers\AddHelper;
use App\Helpers\GetHelper;
use App\Models\CoursePart;
use App\Helpers\ImageHelper;
use Illuminate\Http\Request;
use App\Helpers\DeleteHelper;
use App\Helpers\ModifyHelper;
use App\Enums\CoursePartsEnum;
use App\Enums\CourseStatusEnum;
use App\Helpers\ResponseHelper;
use App\Helpers\ValidateHelper;
use App\Helpers\EnumReplacement;
use App\Helpers\EnumReplacement1;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rules\Enum;

class CoursePartController extends Controller
{

     // 	add course parts (course id, [part id, description?])
    // 	modify course part (course id, part id, status id?, description?) : {}
    // 	delete course part (course id, part id) : {}
    // 	retrieve course parts (id) : { [id, name, status id, description?] }
    public function addCoursePart(Request $request)
    {
        if($failed = ValidateHelper::validateData($request, $this->rules($request))){
            return  ResponseHelper::clientError($failed);
        }
        $course = Course::findOrFail($request->course_id);
        $course->course_parts()->create([
            'part_id' => $request->course_part_id,
            'description' => $request->description ?? null,
        ]);
       return ResponseHelper::success();
       // return AddHelper::addModel($request, Course::class,  $this->rules($request), 'course_parts', $request->course_id);
    }

    public function modifyCoursePart (Request $request)
    {
        if($failed = ValidateHelper::validateData($request, $this->rules($request))){
            return  ResponseHelper::clientError($failed);
        }
        $coursePart = CoursePart::findOrFail($request->id);
        $coursePart->update([
            'status' => $request->status_id ?? $coursePart->status,
            'description' => $request->description ??  $coursePart->description,
        ]);
       return ResponseHelper::success();
       // return ModifyHelper::modifyModel($request, $coursePart,  $this->rules($request));
    }


    public function deleteCoursePart (Request $request)
    {
        $coursePart = CoursePart::findeOrFail( $request->id);
        return DeleteHelper::deleteModel($coursePart);
    }

    public function retrieveCourseParts(Request $request)
    {
        $attributes = ['id', 'part_id as name', 'status as status_name','description'];
        $conditionAttribute = ['course_id' => $request->course_id];
        $enumReplacements = [
            new EnumReplacement('name', CoursePartsEnum::class),
            new EnumReplacement('status_name', CourseStatusEnum::class),
        ];
          return GetHelper::retrieveModels(CoursePart::class, $attributes, $conditionAttribute, $enumReplacements);
    }

    public function retrieveEditableCoursePart(Request $request)
    {
        $attributes = ['status as status_id', 'description'];
        $conditionAttribute = ['id', $request->id];
        return GetHelper::retrieveModels(CoursePart::class, $attributes, $conditionAttribute);
    }

    public function rules(Request $request): array
    {
        $rules = [
            //'course_id' => 'required|exists:courses,id',
            'course_part_id' => ['required',new Enum (CoursePartsEnum::class)], // Assuming CoursePartsEnum holds valid values
            'status_id' =>['required', new Enum (CourseStatusEnum::class)], // Assuming CourseStatusEnum holds valid values
            'description' => 'nullable|string',
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
