<?php

namespace App\Helper;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ImageUploadHelper
{
    /**
     * Stores the files in default disk.
     *
     * @param  \Illuminate\Http\UploadedFile  $contents
     * @param  null  $disk
     *
     * @throws \Exception
     */
    public static function saveImageToStorage(UploadedFile $image, $directory, $disk = 'public')
    {
        $originalName = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
        $originalName = str_replace(' ', '_', $originalName);
        $extension = $image->getClientOriginalExtension();
        $fileName = $originalName.'_'.now()->format('Ymd').'.'.$extension;

        $path = Storage::disk($disk)->putFileAs($directory, $image, $fileName);

        return $fileName;
    }

    public static function deleteImage($path, $disk = 'public')
    {
        return Storage::disk($disk)->delete($path);
    }
}
