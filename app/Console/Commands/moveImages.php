<?php

namespace App\Console\Commands;

use App\Console\LoggableCommand;
use Artisan;
use Illuminate\Support\Facades\File;

class moveImages extends LoggableCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'move:images';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Move images from public to storage';

    /**
     * Execute the console command.
     */
    public function handleAndLog(): void
    {
        // Define the source directories and corresponding destination directories
        $directories = [
            public_path('admin/images') => storage_path('app/public/admin/images'),
            public_path('client/images') => storage_path('app/public/client/images'),
            public_path('common/images') => storage_path('app/public/common/images'),
            public_path('images') => storage_path('app/public/images'),
            public_path('installer/img') => storage_path('app/public/installer/img'),
            public_path('lb-faveo') => storage_path('app/public/lb-faveo'),
        ];

        foreach ($directories as $sourceDirectory => $destinationDirectory) {
            if (File::isDirectory($sourceDirectory)) {
                File::copyDirectory($sourceDirectory, $destinationDirectory);
                $this->info(sprintf('Images copied from %s to %s', $sourceDirectory, $destinationDirectory));
            }
        }

        // Create storage links
        Artisan::call('storage:link');
        $this->info('Storage links created.');

        $this->info('All images copied successfully.');
    }
}
