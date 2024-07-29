<?php

namespace App\Http\Controllers;

use App\Helpers\NullHelper;
use App\Helpers\ImageHelper;
use Illuminate\Http\Request;
use App\Helpers\ResponseHelper;
use App\Helpers\ValidateHelper;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use App\Helpers\Roles\ByteArrayValidationRule;

class UniversityController extends Controller
{
    public function configureUniversityData(Request $request)
    {
        Gate::authorize('configureUniversityData', UniversityController::class);

        if (ValidateHelper::validateData($request, $this->rules($request))) {
            return  ResponseHelper::clientError();
        }

        try {
            if (Storage::disk('local')->exists('university.json')) {
                return  ResponseHelper::clientError();
                // can not be configure university data more than one time 
            }
            $updatedAttributes = $request->all();
            $filePath = ImageHelper::uploadImage($request->logo);
            $updatedAttributes['logo'] =  $filePath;

            $jsonData = json_encode($updatedAttributes, JSON_UNESCAPED_SLASHES);
            Storage::disk('local')->put('university.json', $jsonData);

            return ResponseHelper::success();
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function modifyUniversityData(Request $request)
    {
        Gate::authorize('modifyUniversityData', UniversityController::class);

        if (ValidateHelper::validateData($request, $this->rules($request))) {
            return  ResponseHelper::clientError();
        }
        try {
            if (!Storage::disk('local')->exists('university.json')) {
                return  ResponseHelper::clientError();
                // you must first configure university data
            }

            $updatedAttributes = $request->all();
            $filePath = ImageHelper::uploadImage($request->logo);
            $updatedAttributes['logo'] = $filePath;
            $jsonData = Storage::disk('local')->get('university.json');
            $universityData = json_decode($jsonData, true);
            foreach ($updatedAttributes as $key => $value) {
                if (array_key_exists($key, $universityData)) {
                    $universityData[$key] = $value;
                }
            }
            $modifiedJsonData = json_encode($universityData, JSON_UNESCAPED_SLASHES);
            Storage::disk('local')->put('university.json', $modifiedJsonData);
            return ResponseHelper::success();
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function retrieveUniversityInfo()
    {
        Gate::authorize('retrieveUniversityInfo', UniversityController::class);

        try {
            $jsonData = Storage::disk('local')->get('university.json');
            $universityData = json_decode($jsonData, true);
            if (isset($universityData['logo'])) {
                $universityData['logo_url'] = urldecode($universityData['logo']);
            }
            $universityData = NullHelper::filter($universityData);
            return ResponseHelper::successWithData($universityData);
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function retrieveBasicUniversityInfo()
    {
        Gate::authorize('retrieveBasicUniversityInfo', UniversityController::class);

        try {
            $jsonData = Storage::disk('local')->get('university.json');
            $universityData = json_decode($jsonData, true);
            $basicUniversityInfo = [
                'arabic_name' => $universityData['arabic_name'],
                'english_name' => $universityData['english_name'],
                'logo_url' => urldecode($universityData['logo']),
            ];

            return ResponseHelper::successWithData($basicUniversityInfo);
        } catch (\Exception $e) {
            return ResponseHelper::serverError();
        }
    }

    public function rules(Request $request): array
    {
        $rules = [
            'arabic_name' => 'required|string',
            'english_name' => 'required|string',
            'phone' => 'nullable',
            'email' => 'nullable|email',
            'address' => 'nullable|string',
            'description' => 'nullable|string',
            'web' => 'nullable|url',
            'youtube' => 'nullable|url',
            'x_platform' => 'nullable|url',
            'facebook' => 'nullable|url',
            'telegram' => 'nullable|url',
            // 'logo' => 'required|image|max:2048', // Assuming the logo is uploaded as a file
            'logo' => ['required', new ByteArrayValidationRule], // Assuming the logo is uploaded as a file
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
