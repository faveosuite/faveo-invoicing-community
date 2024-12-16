<?php

namespace Yajra\DataTables\Installer\Tests;

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Yajra\DataTables\ButtonsServiceProvider;

abstract class TestCase extends BaseTestCase
{
    use LazilyRefreshDatabase;

    protected function getPackageProviders($app): array
    {
        return [
            ButtonsServiceProvider::class,
        ];
    }
}
