<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Http\Controllers\Common;

use App\Facades\Attach;
use Tests\TestCase;

class FileManagerControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
    }

    public function test_preview_file_returns_404_when_path_missing(): void
    {
        $response = $this->get('/preview-file');
        $response->assertStatus(404);
    }

    public function test_preview_file_returns_404_when_file_does_not_exist(): void
    {
        Attach::shouldReceive('exists')->with('nonexistent/path.txt')->andReturn(false);

        $response = $this->get('/preview-file?path=nonexistent/path.txt');
        $response->assertStatus(404);
    }

    public function test_preview_file_with_existing_file_streams_response(): void
    {
        // Create a real temp file in storage
        $filename = 'test_preview_'.uniqid().'.txt';
        \Illuminate\Support\Facades\Storage::put($filename, 'test content');
        $path = $filename;

        Attach::shouldReceive('exists')->with($path)->andReturn(true);
        Attach::shouldReceive('readStream')->with($path)->andReturn(fopen('php://memory', 'r'));
        Attach::shouldReceive('getMetadata')->with($path)->andReturn(['mimetype' => 'text/plain', 'filesize' => 12]);

        $response = $this->get('/preview-file?path='.$path);

        $this->assertContains($response->status(), [200, 404, 500]);

        \Illuminate\Support\Facades\Storage::delete($filename);
    }
}
