<?php

namespace App\Traits\Upload;

use App\Facades\Attach;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Pion\Laravel\ChunkUpload\Exceptions\UploadMissingFileException;
use Pion\Laravel\ChunkUpload\Handler\AbstractHandler;
use Pion\Laravel\ChunkUpload\Handler\HandlerFactory;
use Pion\Laravel\ChunkUpload\Receiver\FileReceiver;
use Pion\Laravel\ChunkUpload\Save\AbstractSave;
use ZipArchive;

trait ChunkUpload
{
    public function uploadFile(Request $request): JsonResponse
    {
        try {
            $receiver = new FileReceiver('file', $request, HandlerFactory::classFromRequest($request));

            if ($receiver->isUploaded() === false) {
                throw new UploadMissingFileException;
            }

            $save = $receiver->receive();
            // check if the upload has finished (in chunk mode it will send smaller files)

            if ($save === false || ! ($save instanceof AbstractSave)) {
                return response()->json(__('message.file_invalid'), 500);
            }

            if ($save->isFinished()) {
                $file = $save->getFile();
                $filePath = $file->getPathname();
                $zip = new ZipArchive;
                $res = $zip->open($filePath);
                if ($res === true && $zip->numFiles > 0) {
                    return $this->saveFile($save->getFile());
                }

                unlink($filePath);

                // nosemgrep: php.lang.security.unlink-use.unlink-use
                return response()->json(__('message.file_invalid'), 500);

                // save the file and return any response you need, current example uses `move` function. If you are
                // not using move, you need to manually delete the file by unlink($save->getFile()->getPathname())
            }

            // we are in chunk mode, lets send the current progress
            /** @var AbstractHandler $handler */
            $handler = $save->handler();

            return response()->json([
                'done' => $handler->getPercentageDone(),
                'status' => true,
            ]);
        } catch (Exception $exception) {
            $response = ['success' => 'false', 'message' => $exception->getMessage()];

            return response()->json($exception->getMessage(), 500);
        }
    }

    /**
     * Saves the file.
     *
     * @return JsonResponse
     */
    protected function saveFile(UploadedFile $file)
    {
        $fileName = Attach::createFilename($file);
        $filePath = Attach::put('products/', $file, null, true);

        return response()->json([
            'path' => $filePath,
            'name' => $fileName,
        ]);
    }
}
