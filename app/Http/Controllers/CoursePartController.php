<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\College;
use App\Helpers\AddHelper;
use App\Helpers\GetHelper;
use App\Models\CoursePart;
use App\Helpers\NullHelper;
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

    public function addCoursePart(Request $request)
    {
        if (ValidateHelper::validateData($request, $this->rules($request))) {
            return  ResponseHelper::clientError(401);
        }
        try {
            $course = Course::findOrFail($request->course_id);
            $course->course_parts()->create([
                'part_id' => $request->course_part_id,
                'description' => $request->description ?? null,
            ]);
    
            return ResponseHelper::success();
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function modifyCoursePart(Request $request)
    {
        if (ValidateHelper::validateData($request, $this->rules($request))) {
            return  ResponseHelper::clientError(401);
        }
        try {
            $coursePart = CoursePart::findOrFail($request->id);
            $coursePart->update([
                'status' => $request->status_id ?? $coursePart->status,
                'description' => $request->description ??  $coursePart->description,
            ]);
            return ResponseHelper::success();
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }


    public function deleteCoursePart(Request $request)
    {
        try {
            $coursePart = CoursePart::findOrFail($request->id);
            $coursePart->delete();
            // return DeleteHelper::deleteModel($coursePart);
            return ResponseHelper::success();
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function retrieveCourseParts(Request $request)
    {
        $attributes = ['id', 'part_id as name', 'status as status_name', 'description'];
        $conditionAttribute = ['course_id' => $request->course_id];
        $enumReplacements = [
            new EnumReplacement('name', CoursePartsEnum::class),
            new EnumReplacement('status_name', CourseStatusEnum::class),
        ];
        try {
            $courseParts = GetHelper::retrieveModels(CoursePart::class, $attributes, $conditionAttribute, $enumReplacements);
            $courseParts = NullHelper::filter($courseParts);
            return ResponseHelper::successWithData($courseParts);
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }


    public function retrieveEditableCoursePart(Request $request)
    {
        $attributes = ['status as status_id', 'description'];
        $conditionAttribute = ['id' => $request->id];
        try {
            $coursePart = GetHelper::retrieveModel(CoursePart::class, $attributes, $conditionAttribute);
            $coursePart = NullHelper::filter($coursePart);
            return ResponseHelper::successWithData($coursePart);
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function rules(Request $request): array
    {
        $rules = [
            'course_id' => 'required|exists:courses,id',
            'course_part_id' => ['required', new Enum(CoursePartsEnum::class)], // Assuming CoursePartsEnum holds valid values
            'status_id' => [new Enum(CourseStatusEnum::class)], // Assuming CourseStatusEnum holds valid values
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
