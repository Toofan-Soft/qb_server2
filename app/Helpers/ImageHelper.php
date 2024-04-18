<?php

namespace App\Helpers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class ImageHelper
{
    public static function uploadImage( $file, $folder = 'images')
    {
        if (!Storage::exists($folder)) {
            Storage::makeDirectory($folder);
        }

        // Generate a unique filename for the image
        if ( $file !== null) {
            $imageName = time() . '.' . $file->getClientOriginalExtension();
            $path = $file->move(public_path($folder), $imageName);
            return $folder . '/' . $imageName;
        }
        return null;
    }

    public static function updateImage( $newImage, $oldImagePath ,$folder = 'images')
    {
        if (!Storage::exists($folder)) {
            Storage::makeDirectory($folder);
        }

        // Generate a unique filename for the image
        if ($newImage !==null) {
            $imageName = time() . '.' . $newImage->getClientOriginalExtension();
            $newImagePathath = $newImage->move(public_path($folder), $imageName);
            if ($newImagePathath) {
                if ($oldImagePath) {
                    Storage::delete($oldImagePath);
                }
             return $folder . '/' . $imageName;
            }
        }
        return $oldImagePath;
    }

    public static function addCompleteDomainToMediaUrls($data, $imageFields = ['image_url', 'attachment', 'logo_url'])
    {
        if (is_array($data)) {
            foreach ($data as $item) {
                foreach ($imageFields as $field) {
                    if (isset($item->$field)) {
                        $item->$field = asset($item->$field);
                    }
                }
            }
        } else {
            foreach ($imageFields as $field) {
                if (isset($data->$field)) {
                    $data->$field =asset($data->$field);
                }
            }
        }

        return $data;

    }
}
