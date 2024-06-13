<?php

namespace App\Http\Controllers;

use Pusher\Pusher;
use App\Models\User;
use App\Models\College;
use App\Events\FireEvent;
use App\Helpers\AddHelper;
use App\Helpers\GetHelper;
use App\Helpers\ImageHelper;
use Illuminate\Http\Request;
use App\Helpers\DeleteHelper;
use App\Helpers\InitialDatabaseHelper;
use App\Helpers\ModifyHelper;
use App\Helpers\ResponeHelper;
use App\Helpers\ResponseHelper;
use App\Helpers\ValidateHelper;
use App\Models\DepartmentCourse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use League\CommonMark\Node\Query\OrExpr;
use Illuminate\Support\Facades\Validator;

class InitialDatabaseController extends Controller
{

    public function initialDatabase()
    {
        // InitialDatabaseHelper::colleges();
        // InitialDatabaseHelper::departments();
        // InitialDatabaseHelper::courses();
        // InitialDatabaseHelper::chapters();
        // InitialDatabaseHelper::topics();
        
        $temp = InitialDatabaseHelper::questions();
        return ResponseHelper::successWithData($temp);
        return ResponseHelper::success();
    }

    public function addCollege(Request $request)
    {
        if( ValidateHelper::validateData($request, $this->rules($request))){
            return  ResponseHelper::clientError(401);
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

        if(ValidateHelper::validateData($request, $this->rules($request))){
            return  ResponseHelper::clientError(401);
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

        // event(new FireEvent($college));

      return ResponseHelper::success();
    }


    public function deleteCollege (Request $request)
    {
        $college = College::findOrFail( $request->id);
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
        return GetHelper::retrieveModels(College::class, $attributes);
    }


    public function retrieveCollege(Request $request)
    {
       
        $attributes = [ 'arabic_name', 'english_name', 'phone', 'email', 'description', 'youtube', 'x_platform', 'facebook', 'telegram', 'logo_url'];
        $conditionAttribute = ['id' => $request->id];
        return GetHelper::retrieveModel(College::class, $attributes, $conditionAttribute);

    }


}
