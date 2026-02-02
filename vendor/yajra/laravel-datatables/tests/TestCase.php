<?php

namespace Yajra\DataTables\Installer\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use Yajra\DataTables\ButtonsServiceProvider;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            ButtonsServiceProvider::class,
        ];
    }
}
