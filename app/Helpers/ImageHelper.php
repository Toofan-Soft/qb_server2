<?php

namespace App\Helpers;

use Faker\Core\Uuid;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class ImageHelper
{
    // public static function uploadImage( $file, $folder = 'images') // NSR
    // {
    //     if (!Storage::exists($folder)) {
    //         Storage::makeDirectory($folder);
    //     }

    //     // Generate a unique filename for the image
    //     if ( $file !== null) {
    //         $imageName = time() . '.' . $file->getClientOriginalExtension();
    //         $path = $file->move(public_path($folder), $imageName);
    //         return $folder . '/' . $imageName;
    //     }
    //     return null;
    // }

    public static function uploadImage($fileData, $folder = 'images') // M7D
    {
        if ($fileData !== null) {
            if (!Storage::exists($folder)) {
                Storage::makeDirectory($folder);
            }

            $fileName = time() . '.png'; // Adjust the extension as needed
            
            $filePath = public_path($folder . '/' . $fileName);

            file_put_contents($filePath, implode(array_map("chr", $fileData)));
           // file_put_contents($filePath,  $fileData);

            return $fileName;
        }
        
        return null;
    }

    public static function updateImage($newImage, $oldImageName, $folder = 'images')
    {
        if(is_null($newImage)){
            return $oldImageName;
        }else{
            // Delete old image if it exists
            if ($oldImageName !== null) {
                self::deleteImage($oldImageName, $folder);
            }
    
            // Upload new image
            return self::uploadImage($newImage, $folder);
        }
    }

    private static function deleteImage($imageName, $folder = 'images')
    {
        if ($imageName) {
            $filePath = public_path($folder . '/' . $imageName);
            
            if (file_exists($filePath)) {
                unlink($filePath); // Delete the file
            }
        }
    }





    // public static function updateImage1($newImage, $oldImagePath ,$folder = 'images')
    // {
    //     if (!Storage::exists($folder)) {
    //         Storage::makeDirectory($folder);
    //     }

    //     // Generate a unique filename for the image
    //     if ($newImage !==null) {
    //         $imageName = time() . '.' . $newImage->getClientOriginalExtension();
    //         $newImagePathath = $newImage->move(public_path($folder), $imageName);
    //         if ($newImagePathath) {
    //             if ($oldImagePath) {
    //                 Storage::delete($oldImagePath);
    //             }
    //          return $folder . '/' . $imageName;
    //         }
    //     }
    //     return $oldImagePath;
    // }

    // public static function addCompleteDomainToMediaUrls($data, $imageFields = ['image_url', 'attachment', 'logo_url'])
    // {
    //     if (is_array($data)) {
    //         foreach ($data as $item) {
    //             foreach ($imageFields as $field) {
    //                 if (isset($item->$field)) {
    //                     $item->$field = asset($item->$field);
    //                 }
    //             }
    //         }
    //     } else {
    //         foreach ($imageFields as $field) {
    //             if (isset($data->$field)) {
    //                 $data->$field =asset($data->$field);
    //             }
    //         }
    //     }

    //     return $data;

    // }

    public static function addCompleteDomainToMediaUrls($data, $imageFields = ['image_url', 'attachment', 'logo_url'])
{
    // Check if data is a collection
    if ($data instanceof \Illuminate\Support\Collection) {
        
        $data->each(function($item) use ($imageFields) {
            foreach ($imageFields as $field) {
                if (isset($item->$field)) {
                    $item->$field = asset($item->$field);
                }
            }
        });
    }
    // Check if data is an array
    else if (is_array($data)) {

        foreach ($data as &$item) { // Use reference to modify array elements
            foreach ($imageFields as $field) {
                if (isset($item[$field])) {
                    $item[$field] = asset($item[$field]);
                }
            }
        }
    }
    // Check if data is an object
    else if (is_object($data)) {

        foreach ($imageFields as $field) {
            if (isset($data->$field)) {
                $data->$field = asset($data->$field);
            }
        }
    }

    return $data;
}



}
