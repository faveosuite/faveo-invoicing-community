<?php

declare(strict_types=1);

namespace Yajra\DataTables\Concerns;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

trait WithUploadAction
{
    /**
     * Handle uploading of file.
     */
    public function upload(Request $request): JsonResponse
    {
        $field = $request->input('uploadField');

        if (! $field || ! is_string($field)) {
            $field = 'file';
        }

        try {
            $storage = $this->getDisk();
            $rules = $this->uploadRules();
            $fieldRules = ['upload' => $rules[$field] ?? []];

            $this->validate($request, $fieldRules, $this->messages(), $this->attributes());

            /** @var UploadedFile $uploadedFile */
            $uploadedFile = $request->file('upload');
            $id = $this->storeUploadedFile($field, $uploadedFile);

            $id = $this->uploaded($id);

            return new JsonResponse([
                'action' => $this->action,
                'data' => [],
                'files' => [
                    'files' => [
                        $id => [
                            'filename' => $id,
                            'original_name' => $uploadedFile->getClientOriginalName(),
                            'size' => $uploadedFile->getSize(),
                            'directory' => $this->getUploadDirectory(),
                            'disk' => $this->disk,
                            'url' => $storage->url($id),
                        ],
                    ],
                ],
                'upload' => [
                    'id' => $id,
                ],
            ]);
        } catch (ValidationException $exception) {
            return new JsonResponse([
                'action' => $this->action,
                'data' => [],
                'fieldErrors' => [
                    [
                        'name' => $field,
                        'status' => str_replace('upload', $field, (string) $exception->errors()['upload'][0]),
                    ],
                ],
            ]);
        }
    }

    protected function getDisk(): Filesystem|FilesystemAdapter
    {
        return Storage::disk($this->disk);
    }

    /**
     * Upload validation rules.
     */
    public function uploadRules(): array
    {
        return [];
    }

    protected function storeUploadedFile(string $field, UploadedFile $uploadedFile): string
    {
        $filename = $this->getUploadedFilename($field, $uploadedFile);

        $path = $this->getDisk()->putFileAs($this->getUploadDirectory(), $uploadedFile, $filename);

        if (! is_string($path)) {
            throw ValidationException::withMessages([
                'upload' => 'Failed to store uploaded file.',
            ]);
        }

        return $path;
    }

    protected function getUploadedFilename(string $field, UploadedFile $uploadedFile): string
    {
        return date('Ymd_His').'_'.$uploadedFile->getClientOriginalName();
    }

    protected function getUploadDirectory(): string
    {
        return $this->uploadDir;
    }

    /**
     * Upload event hook.
     */
    public function uploaded(string $id): string
    {
        return $id;
    }
}
