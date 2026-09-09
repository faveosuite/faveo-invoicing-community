<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Http\Controllers\AutoUpdate;

use App\Http\Controllers\AutoUpdate\AutoUpdateController;
use App\License\Services\VersionService;
use Mockery;
use Tests\TestCase;

class AutoUpdateControllerTest extends TestCase
{
    private VersionService $versionServiceMock;

    private AutoUpdateController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->versionServiceMock = Mockery::mock(VersionService::class);
        $this->controller = new AutoUpdateController($this->versionServiceMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_edit_version_throws_when_product_not_found(): void
    {
        $this->expectException(\Exception::class);

        $this->controller->editVersion('1.0.0', 'nonexistent-sku-xyz');
    }
}
