<?php

namespace App\Helper;

use App\FileSystemSettings;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Storage;

class AttachmentHelper
{
    public function put(string $directory, \Illuminate\Http\UploadedFile $contents, ?string $disk = null, mixed $uniqueFilename = null, string $visibility = 'private'): string|false
    {
        $adapter = $this->getStorageAdapter($disk);

        if (isS3Enabled()) {
            $visibility = 'private';
        }

        $fileUniqueName = $uniqueFilename
            ? $this->createFilename($contents)
            : $contents->getClientOriginalName();

        $sanitizedFileName = Str::ascii($fileUniqueName);

        $fileUniqueName = $sanitizedFileName ?: $fileUniqueName;

        return $adapter->putFileAs($directory, $contents, $fileUniqueName, ['visibility' => $visibility]);
    }

    public function delete(string $path, ?string $disk = null): bool
    {
        return $this->getStorageAdapter($disk)->delete($path);
    }

    public function deleteDirectory(string $path, ?string $disk = null): bool
    {
        return $this->getStorageAdapter($disk)->deleteDirectory($path);
    }

    public function download(string $path, ?string $disk = null): \Illuminate\Http\RedirectResponse|\Symfony\Component\HttpFoundation\StreamedResponse
    {
        $adapter = $this->getStorageAdapter($disk);

        $filename = Str::ascii(basename((string) $path)) ?: basename((string) $path);

        if (isS3Enabled()) {
            return redirect($adapter->temporaryUrl($path, now()->addHour()));
        }

        return $adapter->download($path, $filename);
    }

    private function getStorageAdapter(?string $disk = null): \Illuminate\Contracts\Filesystem\Filesystem
    {
        $disk = $disk ?: FileSystemSettings::value('disk');

        if (! $disk) {
            throw new Exception(trans('message.attach_helper_no_default_disk'));
        }

        return Storage::disk($disk);
    }

    /**
     * Create unique filename for uploaded file.
     */
    public function createFilename(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();
        $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME); // Filename without extension

        $safeName = Str::slug($filename) ?: 'file';

        // Add timestamp hash to name of the file
        return $safeName.'_'.md5((string) time()).'.'.$extension;
    }

    public function getUrlPath(string $path, ?string $disk = null): string
    {
        $adapter = $this->getStorageAdapter($disk);

        if (isS3Enabled()) {
            return asset('preview-file?path='.$path);
        }

        return asset($adapter->url($path));
    }

    public function exists(string $path, ?string $disk = null): bool
    {
        $adapter = $this->getStorageAdapter($disk);

        return $adapter->exists($path);
    }

    public function readStream(string $path, ?string $disk = null): mixed
    {
        $adapter = $this->getStorageAdapter($disk);

        return $adapter->readStream($path);
    }

    /**
     * get file meta data.
     *
     *
     * @throws Exception
     */
    public function getMetadata(string $path, ?string $disk = null): array
    {
        $disk = $this->getStorageAdapter($disk);

        return [
            'type' => $disk->mimeType($path),
            'path' => $path,
            'timestamp' => $disk->lastModified($path),
            'size' => $disk->size($path),
        ];
    }
}
