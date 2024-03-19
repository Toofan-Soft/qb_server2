<?php

namespace App\Http\Controllers;

use App\Models\College;
use App\Helpers\AddHelper;
use App\Helpers\GetHelper;
use App\Helpers\ImageHelper;
use Illuminate\Http\Request;
use App\Helpers\DeleteHelper;
use App\Helpers\ModifyHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class CollegeController extends Controller
{

    public function addCollege(Request $request)
    {
      return AddHelper::addModel($request, College::class,  $this->rules($request));
    }

    public function modifyCollege (Request $request, College $college)
    {
        return ModifyHelper::modifyModel($request, $college,  $this->rules($request));
    }


    public function deleteCollege (Request $request)
    {
        $deleteCount = College::where('id', $request->id)->delete();
        if($deleteCount){
            return response()->json([
                'error_message' => 'college deleted successfully!',
            ], 200);
        }else {
            return response()->json([
                'error_message' => 'college not deleted!',
            ], 200);
        }

        //return DeleteHelper::deleteModel($college);
    }

    public function retrieveColleges ()
    {
        $attributes = ['id', 'arabic_name', 'english_name', 'phone', 'email', 'logo_url'];
        return GetHelper::retrieveModels(College::class, $attributes, null);
    }
    public function retrieveBasicCollegesInfo ()
    {
        $attributes = ['id', 'arabic_name','logo_url'];
        return GetHelper::retrieveModels(College::class, $attributes, null);
    }


    public function retrieveCollege(Request $request)
    {
        $requestType = $request->method(); // Get the HTTP method from SERVER

        $allRequestData = $request->all(); // Get all request data as an array

        // Print all data (not recommended for production due to verbosity)
        Log::debug(json_encode($allRequestData, JSON_PRETTY_PRINT)); // Pretty-printed JSON format

        // Access specific data points
        $headers = $request->headers->all(); // Get all request headers as an array
        $body = $request->getContent(); // Get the request body (usually for POST requests)

        // Print specific data (more manageable)
        Log::info("Headers: " . json_encode($headers));
        Log::debug("Body: " . $body);
       // $college = College::findOrFail(1);
        return response()->json(['data' => $request->id  ], 200);
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
