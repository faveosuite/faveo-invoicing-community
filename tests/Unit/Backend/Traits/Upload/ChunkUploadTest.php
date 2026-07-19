<?php

namespace Tests\Unit\Backend\Traits\Upload;

use App\Facades\Attach;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Tests\DBTestCase;
use ZipArchive;

class ChunkUploadTest extends DBTestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\App\Http\Middleware\Install::class);
        $this->getLoggedInUser('admin');
    }

    private function makeZipUpload(array $entries, string $filename = 'build.zip'): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'chunk_upload_');

        $zip = new ZipArchive;
        $zip->open($path, ZipArchive::OVERWRITE);
        foreach ($entries as $name => $content) {
            $zip->addFromString($name, $content);
        }
        $zip->close();

        return new UploadedFile($path, $filename, 'application/zip', null, true);
    }

    public function test_upload_file_accepts_a_valid_core_build_zip(): void
    {
        // saveFile() persists via Attach::put — mocked so the test never
        // writes into the real configured storage disk.
        Attach::shouldReceive('createFilename')->once()->andReturn('build.zip');
        Attach::shouldReceive('put')->once()->andReturn('products/build-stub.zip');

        $file = $this->makeZipUpload(['storage/faveoconfig.ini' => 'APL_SALT=x']);
        $sourcePath = $file->getPathname();

        $response = $this->post('/chunkupload', ['file' => $file]);

        $response->assertStatus(200);
        $data = json_decode((string) $response->getContent(), true);
        $this->assertSame('products/build-stub.zip', $data['path']);
        $this->assertSame('build.zip', $data['name']);

        // Attach::put is mocked, so it never consumes the source temp file.
        @unlink($sourcePath);
    }

    public function test_upload_file_rejects_a_zip_wrapped_in_a_single_folder(): void
    {
        $file = $this->makeZipUpload([
            'my-repo-main/storage/faveoconfig.ini' => 'APL_SALT=x',
            'my-repo-main/app/index.php' => '<?php',
        ]);

        $response = $this->post('/chunkupload', ['file' => $file]);

        $response->assertStatus(500);
        $data = json_decode((string) $response->getContent(), true);
        $this->assertFalse($data['success']);
    }

    public function test_upload_file_rejects_a_file_that_is_not_a_zip_at_all(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'not_a_zip_');
        file_put_contents($path, 'this is not a zip archive');
        $file = new UploadedFile($path, 'build.zip', 'application/zip', null, true);

        $response = $this->post('/chunkupload', ['file' => $file]);

        $response->assertStatus(500);
        $data = json_decode((string) $response->getContent(), true);
        $this->assertFalse($data['success']);
        $this->assertSame(trans('message.file_invalid'), $data['message']);
    }
}
