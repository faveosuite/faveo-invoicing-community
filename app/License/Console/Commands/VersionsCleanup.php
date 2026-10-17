<?php

namespace App\License\Console\Commands;

use App\Model\Mailjob\ExpiryMailDay;
use App\Model\Product\ProductUpload;
use Illuminate\Console\Command;

class VersionsCleanup extends Command
{
    protected $signature = 'app:versions-cleanup';

    protected $description = 'Version Cleanup';

    public function handle(): void
    {
        $days = ExpiryMailDay::value('license_versions_cleanup_days');
        if ($days === null) {
            return;
        }

        ProductUpload::where('status', 0)
            ->where('updated_at', '<', now()->subDays($days))
            ->delete();
    }
}
