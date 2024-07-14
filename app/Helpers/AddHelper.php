<?php

namespace App\Helpers;

use App\Models\User;
use App\Enums\RoleEnum;
use App\Enums\GenderEnum;
use App\Enums\JobTypeEnum;
use Illuminate\Support\Str;
use App\Enums\OwnerTypeEnum;
use App\Helpers\ImageHelper;
use Illuminate\Http\Request;
use App\Enums\UserStatusEnum;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Notifications\EmaiVerificationNotification;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AddHelper
{
    public static $filePath = null;
    public static function addModel(Request $request, $model, $rules, $relationShip = null, $related_id = null )
    {
        // return ResponseHelper::successWithData(ValidateHelper::validateData($request,$rules));
        if(ValidateHelper::validateData($request,$rules)){
            return  ResponseHelper::clientError();
        }
           $updatedAttributes = $request->all();
           foreach (['image_url', 'logo_url', 'attachment'] as $fileKey) {
               if ($request->hasFile($fileKey)) {
                   $filePath = ImageHelper::uploadImage($request->file($fileKey));
                   $updatedAttributes[$fileKey] = $filePath; // Update attribute with file path
               }
           }
           
           if ($related_id) {
               // Create model with relationship
               try {
                   $parentModel = $model::findOrFail($related_id);
                   $parentModel->$relationShip()->create($updatedAttributes);
               } catch (ModelNotFoundException $e) {
                return  ResponseHelper::serverError();
               }
           } else {
               // Create model without relationship
               $data = $model::create($updatedAttributes);
           }
           return  ResponseHelper::success();
    }

}
