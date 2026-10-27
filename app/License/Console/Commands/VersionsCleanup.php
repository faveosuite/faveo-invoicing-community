<?php

namespace App\License\Console\Commands;

use App\Model\Mailjob\Condition;
use App\Model\Product\ProductUpload;
use Illuminate\Console\Command;

class VersionsCleanup extends Command
{
    protected $signature = 'app:versions-cleanup';

    protected $description = 'Version Cleanup';

    public function handle(): void
    {
        $days = Condition::dayCount('licenseVersionsCleanup');
        if ($days === null) {
            return;
        }

        ProductUpload::where('status', 0)
            ->where('updated_at', '<', now()->subDays($days))
            ->delete();
    }
}
