<?php

namespace Yajra\DataTables\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DataTablesPurgeExportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'datatables:purge-export';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove exported files that datatables-export generate.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        /** @var string $disk */
        $disk = config('datatables-export.disk', 'local');

        /** @var int $daysOld */
        $daysOld = config('datatables-export.purge.days', 1);

        $timestamp = now()->subDays($daysOld)->getTimestamp();

        collect(Storage::disk($disk)->files())
            ->each(function ($file) use ($timestamp, $disk) {
                $path = Storage::disk($disk)->path($file);
                if (File::lastModified($path) < $timestamp && Str::endsWith(strtolower($file), ['xlsx', 'csv'])) {
                    File::delete($path);
                }
            });

        $this->info('The command was successful. Export files are cleared!');
    }
}
