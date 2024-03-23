<?php

namespace App\Helpers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class ImageHelper
{
    public static function uploadImage(UploadedFile $file, $folder = 'images')
    {
        if (!Storage::exists($folder)) {
            Storage::makeDirectory($folder);
        }

        // Generate a unique filename for the image
        if ($file->isValid()) {
            $imageName = time() . '.' . $file->getClientOriginalExtension();
            $path = $file->move(public_path($folder), $imageName);
            return $folder . '/' . $imageName;
        }
        return null;
    }

    public static function updateImage(UploadedFile $newImage, $oldImagePath ,$folder = 'images')
    {
        if (!Storage::exists($folder)) {
            Storage::makeDirectory($folder);
        }

        // Generate a unique filename for the image
        if ($oldImagePath->isValid()) {
            $imageName = time() . '.' . $newImage->getClientOriginalExtension();
            $newImagePathath = $newImage->move(public_path($folder), $imageName);
            if ($newImagePathath) {
                if ($oldImagePath) {
                    Storage::delete($oldImagePath);
                }
             return $folder . '/' . $imageName;
            }
        }
        return null;
    }

    // public static function checkImage(Request $request)
    // {
    //     $updatedAttributes=[];
    //     foreach (['image_url', 'logo_url'] as $fileKey) {
    //         if ($request->hasFile($fileKey)) {
    //             $filePath = ImageHelper::uploadImage($request->file($fileKey));
    //             $updatedAttributes[$fileKey] = $filePath; // Update attribute with file path
    //         }
    //     }
    //     return null;
    // }
}
