<?php

namespace App\Http\Controllers;

use App\Models\College;
use App\Helpers\AddHelper;
use App\Helpers\GetHelper;
use App\Helpers\ImageHelper;
use Illuminate\Http\Request;
use App\Helpers\DeleteHelper;
use App\Helpers\ModifyHelper;
use App\Helpers\ResponeHelper;
use App\Helpers\ResponseHelper;
use App\Helpers\ValidateHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class CollegeController extends Controller
{

    public function addCollege(Request $request)
    {

        if($failed = ValidateHelper::validateData($request, $this->rules($request))){
            return  ResponseHelper::clientError($failed);
        }
        College::create([
            'arabic_name' => $request->arabic_name,
            'english_name' => $request->english_name,
            'phone' => $request->phone ?? null,
            'email' => $request->email ?? null,
            'description' => $request->description?? null,
            'facebook' => $request->facebook ?? null,
            'youtube' => $request->youtube?? null,
            'x_platform' => $request->x_platform ?? null,
            'telegram' => $request->telegram ?? null,
            'logo_url' => ImageHelper::uploadImage($request->logo)
        ]);
       return ResponseHelper::success();
    }

    public function modifyCollege (Request $request)
    {
        if($failed = ValidateHelper::validateData($request, $this->rules($request))){
            return  ResponseHelper::clientError($failed);
        }

        $college = College::findOrFail($request->id);
        $college->update([
            'arabic_name' => $request->arabic_name ?? $college->arabic_name ,
            'english_name' => $request->english_name ?? $college->english_name,
            'phone' => $request->phone ??  $college->phone,
            'email' => $request->email ?? $college->email,
            'description' => $request->description?? $college->description,
            'youtube' => $request->youtube?? $college->youtube,
            'facebook' => $request->facebook ?? $college->facebook,
            'x_platform' => $request->x_platform ?? $college->x_platform,
            'telegram' => $request->telegram ?? $college->telegram,
            'logo_url' => ImageHelper::updateImage($request->logo, $college->logo_url)
        ]);
       return ResponseHelper::success();
    }


    public function deleteCollege (Request $request)
    {
        $college = College::findeOrFail( $request->id);
        return DeleteHelper::deleteModel($college);
    }

    public function retrieveColleges ()
    {
        $attributes = ['id', 'arabic_name', 'english_name', 'phone', 'email', 'logo_url'];
        return GetHelper::retrieveModels(College::class, $attributes);
    }
    public function retrieveBasicCollegesInfo ()
    {
        $attributes = ['id', 'arabic_name as name','logo_url'];
        return GetHelper::retrieveModels(College::class, $attributes, null);
    }


    public function retrieveCollege(Request $request)
    {
        // $requestType = $request->method(); // Get the HTTP method from SERVER

        // $allRequestData = $request->all(); // Get all request data as an array

        // // Print all data (not recommended for production due to verbosity)
        // Log::debug(json_encode($allRequestData, JSON_PRETTY_PRINT)); // Pretty-printed JSON format

        // // Access specific data points
        // $headers = $request->headers->all(); // Get all request headers as an array
        // $body = $request->getContent(); // Get the request body (usually for POST requests)

        // // Print specific data (more manageable)
        // Log::info("Headers: " . json_encode($headers));
        // Log::debug("Body: " . $body);
         $college = College::findOrFail($request->id);
        // $college = $college->departments()->get();

// for test : College::
    //      $result =  DB::table('course_parts')
    // ->join('courses', 'course_parts.course_id', '=', 'courses.id')
    // ->join('department_courses', 'courses.id', '=', 'department_courses.course_id')
    // ->join('departments', 'department_courses.department_id', '=', 'departments.id')
    // ->select('course_parts.part_id as part_name',
    //         'courses.arabic_name as course_name',
    //          'department_courses.semester as semester_name',
    //           'departments.arabic_name as department_name'
    //      )
    // ->where('course_parts.course_id', '=', $request->id)
    // ->when($request->status, function ($query) use ($request) {
    //     $query->where('department_courses.semester', '=', $request->status);
    // })
    // ->get();
        return response()->json(['data' => $college  ], 200);
        // $college = College::with(['departments:id,arabic_name as name,college_id'])->find($request->id); // لازم العمود حق العلاقه يكون ضمن البيانات المحددة




        // $attributes = [ 'arabic_name', 'english_name', 'phone', 'email', 'description', 'youtube', 'x_platform', 'facebook', 'telegram', 'logo_url'];
        // $conditionAttribute = ['id' => $request->id];
        // return GetHelper::retrieveModels(College::class, $attributes, $conditionAttribute);

    }


    public function rules(Request $request): array
    {
        $rules = [
            'arabic_name' => 'required|string|max:255',
            'english_name' => 'required|string|max:255',
            'logo_url' =>  'image|mimes:jpeg,png,jpg,gif|max:2048', // Adjust max size as needed
            'description' => 'nullable|string',
            'phone' => 'nullable|string|unique:colleges,phone',
            'email' => 'nullable|email|unique:colleges,email',
            'facebook' => 'nullable|string|max:255',
            'twitter' => 'nullable|string|max:255',
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
