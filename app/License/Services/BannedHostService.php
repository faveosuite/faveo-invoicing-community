<?php

declare(strict_types=1);

namespace App\License\Services;

use App\License\Models\LicenseBannedHost;
use App\License\Models\LicenseFailedLicensing;
use App\License\Models\LicenseSecuritySetting;

class BannedHostService
{
    /**
     * Record a failed license verify/install/scheme attempt for an IP, auto-banning
     * it once the configured threshold is reached (mirrors recordFailedLicensing()
     * from the original product). No-op unless auto-ban is enabled with a non-zero
     * limit (both default off, matching the original product's shipped settings).
     */
    public function recordFailedLicensing(string $ip): void
    {
        $settings = LicenseSecuritySetting::find(1);
        if (! $settings?->auto_ban_enabled || $settings->failed_licensings_limit === 0) {
            return;
        }

        if (filter_var($ip, FILTER_VALIDATE_IP) === false) {
            return;
        }

        $limit = $settings->failed_licensings_limit;

        // ponytail: firstOrCreate has a tiny race window for a brand-new IP's first
        // failure (matches the original's non-atomic select-then-write); upgrade to
        // an atomic upsert if concurrent first-hits ever prove to matter.
        $record = LicenseFailedLicensing::firstOrCreate(
            ['failed_licensing_ip' => $ip],
            ['failed_licensing_attempts' => 0]
        );
        $record->increment('failed_licensing_attempts');
        $record->update(['failed_licensing_last_attempt_date' => now()->toDateString()]);

        if ($record->failed_licensing_attempts >= $limit) {
            LicenseBannedHost::firstOrCreate(
                ['banned_host_ip' => $ip],
                ['comments' => sprintf('Auto-banned: %d failed licensing attempts reached.', $limit)]
            );
        }
    }
}
