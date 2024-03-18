<?php

namespace App\Http\Controllers;

use App\Helpers\ImageHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class UniversityController extends Controller
{
    public function configureUniversityData(Request $request)
    {
        $validator = Validator::make($request->all(), $this->rules($request));
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->first()], 400);
        }
        $updatedAttributes = $request->all();
        foreach (['image_url', 'logo_url'] as $fileKey) {
            if ($request->hasFile($fileKey)) {
                $filePath = ImageHelper::uploadImage($request->file($fileKey));
                $updatedAttributes[$fileKey] = asset($filePath) ;
            }
        }
        // Convert the data to JSON
        $jsonData = json_encode($updatedAttributes, JSON_UNESCAPED_SLASHES);
        Storage::disk('local')->put('university.json', $jsonData);
        return response()->json(['message' => 'University data configured successfully'],201);
    }

    public function modifyUniversityData(Request $request)
    {
        $validator = Validator::make($request->all(), $this->rules($request));
        if ($validator->fails()) {
            return response()->json(['error_message' => $validator->errors()->first()], 400);
        }
        $updatedAttributes =$request->all();
        foreach (['image_url', 'logo_url'] as $fileKey) {
            if ($request->hasFile($fileKey)) {
                $filePath = ImageHelper::uploadImage($request->file($fileKey));
                $updatedAttributes[$fileKey] = $filePath;
            }
        }
        $jsonData = Storage::disk('local')->get('university.json');
        $universityData = json_decode($jsonData, true);
        foreach ($updatedAttributes as $key => $value) {
            if (array_key_exists($key, $universityData)) {
                $universityData[$key] = $value;
            }
        }
        $modifiedJsonData = json_encode($universityData, JSON_UNESCAPED_SLASHES);
        Storage::disk('local')->put('university.json', $modifiedJsonData);
        return response()->json(['message' => 'University data modified successfully'],200);
    }

    public function retrieveUniversityInfo()
    {
        $jsonData = Storage::disk('local')->get('university.json');
        $universityData = json_decode($jsonData, true);
        if (isset($universityData['logo_url'])) {
            $universityData['logo_url'] = urldecode($universityData['logo_url']);
        }
        return response()->json($universityData);
    }

    public function retrieveBasicUniversityInfo()
    {
        $jsonData = Storage::disk('local')->get('university.json');
        $universityData = json_decode($jsonData, true);
        $basicUniversityInfo = [
            'arabic_name' => $universityData['arabic_name'],
            'english_name' => $universityData['english_name'],
            'logo_url' => urldecode($universityData['logo_url']) ,
        ];
        return response()->json($basicUniversityInfo);
    }

    public function rules(Request $request): array
    {
        $rules = [
            'arabic_name' => 'required',
            'english_name' => 'required',
            'phone' => 'nullable',
            'email' => 'nullable|email',
            'address' => 'nullable',
            'description' => 'nullable',
            'web' => 'nullable|url',
            'youtube' => 'nullable|url',
            'x_platform' => 'nullable|url',
            'facebook' => 'nullable|url',
            'telegram' => 'nullable|url',
            'logo' => 'nullable|image|max:2048', // Assuming the logo is uploaded as a file
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
