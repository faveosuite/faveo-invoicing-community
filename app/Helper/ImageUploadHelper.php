<?php

namespace App\Helper;

use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ImageUploadHelper
{
    /**
     * Stores the files in default disk.
     *
     * @throws Exception
     */
    public static function saveImageToStorage(UploadedFile $image, mixed $directory, mixed $disk = 'public'): string
    {
        $originalName = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
        $originalName = str_replace(' ', '_', $originalName);

        $extension = $image->getClientOriginalExtension();
        $fileName = $originalName.'_'.now()->format('Ymd').'.'.$extension;

        Storage::disk($disk)->putFileAs($directory, $image, $fileName);

        return $fileName;
    }

    public static function deleteImage(mixed $path, mixed $disk = 'public'): mixed
    {
        return Storage::disk($disk)->delete($path);
    }
}
