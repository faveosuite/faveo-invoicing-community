<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Http\Controllers\User;

use App\ExportDetail;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Tests\DBTestCase;
use ZipArchive;

/**
 * GET /download-exported-file/{id}. storage_path() points at a throwaway temp folder
 * (removed in tearDown), so neither the export nor the built zip touches real storage.
 */
class DownloadExportedFileTest extends DBTestCase
{
    private string $tmp;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\App\Http\Middleware\Install::class);
        $this->getLoggedInUser('admin');

        $this->tmp = sys_get_temp_dir().'/export_dl_'.uniqid();
        File::makeDirectory($this->tmp.'/storage/app/exports', recursive: true);
        File::makeDirectory($this->tmp.'/source/sub', recursive: true);
        File::put($this->tmp.'/source/users.csv', 'a,b');
        File::put($this->tmp.'/source/sub/orders.csv', 'c,d');
        $this->app->useStoragePath($this->tmp.'/storage');
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->tmp);
        parent::tearDown();
    }

    private function export(string $path, ?string $createdAt = null): ExportDetail
    {
        return ExportDetail::create([
            'user_id' => $this->user->id,
            'file' => 'report_'.uniqid(),
            'file_path' => $path,
            'name' => 'users',
            'created_at' => $createdAt ?? now(),
        ]);
    }

    /** @return list<string> */
    private function zipEntries(\Illuminate\Testing\TestResponse $response): array
    {
        $this->assertInstanceOf(BinaryFileResponse::class, $response->baseResponse);
        $zip = new ZipArchive;
        $zip->open($response->baseResponse->getFile()->getPathname());
        $names = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $names[] = $zip->getNameIndex($i);
        }
        $zip->close();
        sort($names);

        return $names;
    }

    public function test_a_directory_export_is_zipped_with_relative_paths(): void
    {
        $export = $this->export($this->tmp.'/source');

        $response = $this->get('/download-exported-file/'.$export->id)->assertOk();

        $this->assertSame(['sub/orders.csv', 'users.csv'], $this->zipEntries($response));
        $this->assertStringContainsString($export->file.'.zip', (string) $response->headers->get('content-disposition'));
    }

    public function test_a_single_file_export_is_zipped_by_its_name(): void
    {
        $export = $this->export($this->tmp.'/source/users.csv');

        $this->assertSame(['users.csv'], $this->zipEntries($this->get('/download-exported-file/'.$export->id)->assertOk()));
    }

    public function test_a_link_older_than_six_hours_has_expired(): void
    {
        $export = $this->export($this->tmp.'/source', now()->subHours(7)->toDateTimeString());

        $this->getJson('/download-exported-file/'.$export->id)->assertJson(['success' => false, 'message' => __('message.download_link_expired')]);
    }

    public function test_a_missing_export_file_is_reported(): void
    {
        $export = $this->export($this->tmp.'/nope');

        $this->getJson('/download-exported-file/'.$export->id)->assertJson(['success' => false, 'message' => __('message.file_not_found')]);
    }

    public function test_a_zip_that_cannot_be_opened_is_reported(): void
    {
        $export = $this->export($this->tmp.'/source');
        File::makeDirectory($this->tmp.'/storage/app/exports/'.$export->file.'.zip'); // a folder where the zip should go

        $this->getJson('/download-exported-file/'.$export->id)->assertJson(['success' => false, 'message' => __('message.failed_create_zip_file')]);
    }

    public function test_a_missing_export_folder_fails_on_close_as_a_general_export_error(): void
    {
        File::deleteDirectory($this->tmp.'/storage/app/exports');
        $export = $this->export($this->tmp.'/source');

        $this->getJson('/download-exported-file/'.$export->id)->assertJson(['success' => false, 'message' => __('message.export_failed').__('message.sorry_something_wrong')]);
    }

    public function test_an_unknown_export_is_not_found(): void
    {
        $this->getJson('/download-exported-file/999999999')->assertJson(['success' => false, 'message' => __('message.record_not_found')]);
    }
}
