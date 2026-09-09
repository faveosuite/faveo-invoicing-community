<?php

namespace Tests\Unit\Backend\Console\Commands;

use App\Services\Seo\SeoFileGenerator;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery;
use Tests\DBTestCase;

class GenerateSeoFilesTest extends DBTestCase
{
    use DatabaseTransactions;

    public function test_handle_delegates_to_seo_file_generator(): void
    {
        $generator = Mockery::mock(SeoFileGenerator::class);
        $generator->shouldReceive('generateAll')->once();
        $this->app->instance(SeoFileGenerator::class, $generator);

        $this->artisan('seo:generate-files')
            ->expectsOutput('seo:generate-files Command Run successfully!')
            ->assertExitCode(0);
    }
}
