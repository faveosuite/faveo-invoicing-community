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
}
