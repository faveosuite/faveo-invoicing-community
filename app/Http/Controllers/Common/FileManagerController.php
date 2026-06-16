<?php

namespace App\Http\Controllers\Common;

use App\Facades\Attach;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FileManagerController extends Controller
{
    public function previewFile(Request $request)
    {
        $path = $request->input('path');

        if (! $path || ! Attach::exists($path)) {
            abort(404, __('message.file_not_exist'));
        }

        $fileStream = Attach::readStream($path);

        $fileMetadata = Attach::getMetadata($path);

        $fileName = basename((string) $path);

        return response()->stream(function () use ($fileStream): void {
            fpassthru($fileStream);
        }, 200, [
            'Content-Type' => $fileMetadata['type'],
            'Content-Disposition' => 'inline; filename="'.$fileName.'"',
        ]);
    }
}
