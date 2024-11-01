<?php

namespace App\Helpers;

use App\Helpers\ImageHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ModifyHelper
{

    public static function modifyModel(Request $request, $model, $rules, $deletedAttributes = [] )
    {

        if(ValidateHelper::validateData($request,$rules)){
            return  ResponseHelper::clientError();
        }
            $updatedAttributes = $request->all();
            foreach ($rules as $fileKey ) {
                if ($request->hasFile($fileKey)) {
                    $filePath = ImageHelper::uploadImage($request->file($fileKey));

                    if ($filePath && $model->$fileKey) {
                        Storage::delete($model->$fileKey);
                    }
                    $updatedAttributes[$fileKey] = $filePath;
                }
            }
            DB::beginTransaction();
            try {
                $response = self::modify($model, $updatedAttributes, $deletedAttributes);
                DB::commit();
                return  ResponseHelper::success();
            } catch (\Exception $e) {
                DB::rollBack();
                return  ResponseHelper::serverError(500);
            }
    }


    private static function modify( $model ,$attributes, $deletedAttributes=[])
    {
        $dataToUpdate = array_diff_key($attributes, array_flip($deletedAttributes)); // Extract attributes to update (excluding deletedAttributes)
        $model->update($dataToUpdate);
        return [
            'message' =>'Successfully updated attributes: ',
            'data' => $model->fresh(), // Refresh data after update
        ];
    }



    // public static function modifyAttribute($model ,$attributeName , $attributeValue){

    //     $model::update([
    //         $attributeName => $attributeValue
    //     ]);
    //     response()->json(['messag' => 'successful updated attribute'], 200);
    // }
}
