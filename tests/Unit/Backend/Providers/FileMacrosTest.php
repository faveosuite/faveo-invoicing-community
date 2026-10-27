<?php

namespace Tests\Unit\Backend\Providers;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * File::cleanDirectoryFiles / filterFiles / safeGet, registered in AppServiceProvider::fileMacros().
 * Works in a throwaway temp folder that tearDown removes.
 */
class FileMacrosTest extends TestCase
{
    private string $dir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dir = sys_get_temp_dir().'/file_macros_'.uniqid();
        File::makeDirectory($this->dir.'/keep_dir', recursive: true);
        File::makeDirectory($this->dir.'/drop_dir');
        File::put($this->dir.'/keep.txt', 'k');
        File::put($this->dir.'/drop.txt', 'd');
        File::put($this->dir.'/drop_dir/inner.txt', 'i');
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->dir);
        parent::tearDown();
    }

    public function test_clean_directory_files_keeps_only_excluded_files_and_folders(): void
    {
        // Exclusions are matched by basename, so full paths work too.
        File::cleanDirectoryFiles($this->dir, ['/somewhere/keep.txt'], [$this->dir.'/keep_dir']);

        $this->assertSame(['keep.txt'], array_map(fn ($f) => $f->getFilename(), File::files($this->dir)));
        $this->assertSame([$this->dir.'/keep_dir'], File::directories($this->dir));
    }

    public function test_clean_directory_files_ignores_a_missing_directory(): void
    {
        File::cleanDirectoryFiles($this->dir.'/missing');

        $this->assertCount(2, File::files($this->dir));
    }

    public function test_filter_files_applies_the_callback_and_handles_a_missing_directory(): void
    {
        $kept = File::filterFiles($this->dir, fn ($file): bool => str_starts_with($file->getFilename(), 'keep'));

        $this->assertSame(['keep.txt'], $kept->map(fn ($f) => $f->getFilename())->values()->all());
        $this->assertTrue(File::filterFiles($this->dir.'/missing', fn (): bool => true)->isEmpty());
    }

    public function test_safe_get_reads_raw_or_unserialized_and_returns_null_when_missing(): void
    {
        File::put($this->dir.'/data.ser', serialize(['a' => 1]));

        $this->assertSame('k', File::safeGet($this->dir.'/keep.txt'));
        $this->assertSame(['a' => 1], File::safeGet($this->dir.'/data.ser', true));
        $this->assertNull(File::safeGet($this->dir.'/missing.txt'));
    }
}
