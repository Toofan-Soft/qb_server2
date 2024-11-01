<?php

namespace App\Http\Controllers;

use App\Models\College;
use App\Helpers\GetHelper;
use App\Helpers\NullHelper;
use App\Helpers\ImageHelper;
use Illuminate\Http\Request;
use App\Helpers\LanguageHelper;
use App\Helpers\ResponseHelper;
use App\Helpers\ValidateHelper;
use Illuminate\Support\Facades\Gate;
use App\Helpers\Roles\ByteArrayValidationRule;

class CollegeController extends Controller
{

    public function addCollege(Request $request)
    {
        Gate::authorize('addCollege', CollegeController::class);

        if (ValidateHelper::validateData($request, $this->rules($request))) {
            return  ResponseHelper::clientError();
        }

        try {
            College::create([
                'arabic_name' => $request->arabic_name,
                'english_name' => $request->english_name,
                'phone' => $request->phone ?? null,
                'email' => $request->email ?? null,
                'description' => $request->description ?? null,
                'facebook' => $request->facebook ?? null,
                'youtube' => $request->youtube ?? null,
                'x_platform' => $request->x_platform ?? null,
                'telegram' => $request->telegram ?? null,
                'logo_url' => ImageHelper::uploadImage($request->logo)
            ]);

            return ResponseHelper::success();
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function modifyCollege(Request $request)
    {
        Gate::authorize('modifyCollege', CollegeController::class);
        if (ValidateHelper::validateData($request, $this->rules($request))) {
            return  ResponseHelper::clientError();
        }
        try {
            $college = College::findOrFail($request->id);
            $college->update([
                'arabic_name' => $request->arabic_name ?? $college->arabic_name,
                'english_name' => $request->english_name ?? $college->english_name,
                'phone' => $request->phone ??  $college->phone,
                'email' => $request->email ?? $college->email,
                'description' => $request->description ?? $college->description,
                'youtube' => $request->youtube ?? $college->youtube,
                'facebook' => $request->facebook ?? $college->facebook,
                'x_platform' => $request->x_platform ?? $college->x_platform,
                'telegram' => $request->telegram ?? $college->telegram,
                'logo_url' => ImageHelper::updateImage($request->logo, $college->logo_url)
            ]);
            //  event(new FireEvent($college));
            return ResponseHelper::success();
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function deleteCollege(Request $request)
    {
        Gate::authorize('deleteCollege', CollegeController::class);
        if (ValidateHelper::validateData($request, [
            'id' => 'required|integer'
        ])) {
            return  ResponseHelper::clientError();
        }
        try {
            $college = College::findOrFail($request->id);
            $college->delete();
            // DeleteHelper::deleteModel($college);
            return ResponseHelper::success();
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function retrieveColleges()
    {
        Gate::authorize('retrieveColleges', CollegeController::class);
        $attributes = ['id', 'arabic_name', 'english_name', 'phone', 'email', 'logo_url'];
        try {
            $colleges = GetHelper::retrieveModels(College::class, $attributes);

            $colleges = NullHelper::filter($colleges);

            return ResponseHelper::successWithData($colleges);
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function retrieveBasicCollegesInfo()
    {
        Gate::authorize('retrieveBasicCollegesInfo', CollegeController::class);

        $attributes = ['id', LanguageHelper::getNameColumnName(null, 'name'), 'logo_url'];
        try {
            $colleges = GetHelper::retrieveModels(College::class, $attributes);
            $colleges = NullHelper::filter($colleges);
            return ResponseHelper::successWithData($colleges);
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function retrieveCollege(Request $request)
    {
        Gate::authorize('retrieveCollege', CollegeController::class);
        if (ValidateHelper::validateData($request, [
            'id' => 'required|integer'
        ])) {
            return  ResponseHelper::clientError();
        }
        $attributes = ['arabic_name', 'english_name', 'phone', 'email', 'description', 'youtube', 'x_platform', 'facebook', 'telegram', 'logo_url'];
        $conditionAttribute = ['id' => $request->id];
        try {
            $college = GetHelper::retrieveModel(College::class, $attributes, $conditionAttribute);
            $college = NullHelper::filter($college);
            return ResponseHelper::successWithData($college);
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function retrieveEditableCollege(Request $request)
    {
        Gate::authorize('retrieveEditableCollege', CollegeController::class);
        if (ValidateHelper::validateData($request, [
            'id' => 'required|integer'
        ])) {
            return  ResponseHelper::clientError();
        }
        $attributes = ['arabic_name', 'english_name', 'phone', 'email', 'description', 'youtube', 'x_platform', 'facebook', 'telegram', 'logo_url'];
        $conditionAttribute = ['id' => $request->id];
        try {
            $college = GetHelper::retrieveModel(College::class, $attributes, $conditionAttribute);
            $college = NullHelper::filter($college);
            return ResponseHelper::successWithData($college);
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }


    public function rules(Request $request): array
    {
        $rules = [
            'arabic_name' => 'required|string|unique:colleges,arabic_name|max:255',
            'english_name' => 'required|string|unique:colleges,english_name|max:255',
            'logo' =>  ['nullable', new ByteArrayValidationRule], // Adjust max size as needed
            'description' => 'nullable|string',
            'phone' => 'nullable|integer',
            'email' => 'nullable|email',
            'facebook' => 'nullable|string|max:255',
            'x_platform' => 'nullable|string|max:255',
            'youtube' => 'nullable|string|max:255',
            'telegram' => 'nullable|string|max:255',
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
