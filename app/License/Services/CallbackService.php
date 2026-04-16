<?php

namespace App\License\Services;

use App\License\Models\Installation;
use App\License\Models\License;
use App\License\Models\LicenseBannedHost;
use App\License\Models\LicenseCallback;
use App\License\Models\LicenseNotification;
use App\License\Models\LicenseWhitelistIp;
use Illuminate\Http\Request;

/**
 * Callback service — processes external license/version callbacks.
 * Used by AflCallbacks and AfuCallbacks controllers.
 *
 * All responses use integer status values (0/1/2) matching the original app.
 */
class CallbackService
{
    protected LicenseService $licenseService;
    protected InstallationService $installationService;
    protected VersionService $versionService;

    public function __construct(
        LicenseService $licenseService,
        InstallationService $installationService,
        VersionService $versionService
    ) {
        $this->licenseService = $licenseService;
        $this->installationService = $installationService;
        $this->versionService = $versionService;
    }

    /**
     * Process license verification callback.
     */
    public function processLicenseVerification(Request $request): array
    {
        $licenseCode = $request->input('license_code');
        $ip = $request->ip();
        $domain = $request->input('root_url', $request->input('domain'));

        // Check banned hosts
        if ($this->isHostBanned($ip)) {
            return $this->getResponse('notification_host_banned');
        }

        // Log the callback
        $this->logCallback($licenseCode, $ip, $domain);

        // Find license
        $license = License::where('license_code', $licenseCode)->first();
        if (! $license) {
            return $this->getResponse('notification_license_not_found');
        }

        // Check status (integer values)
        if ($license->license_status == 0) {
            return $this->getResponse('notification_license_cancelled');
        }

        if ($license->license_status == 2) {
            return $this->getResponse('notification_license_suspended');
        }

        // Check expiration
        if ($license->license_expire_date && $license->license_expire_date < now()->format('Y-m-d')) {
            return $this->getResponse('notification_license_expired');
        }

        // Check domain mismatch
        if ($license->license_require_domain && ! empty($license->license_domain)) {
            $domains = array_map('trim', explode(',', $license->license_domain));
            $domainValid = false;
            foreach ($domains as $d) {
                if (stripos($domain, $d) !== false) {
                    $domainValid = true;
                    break;
                }
            }
            if (! $domainValid) {
                return $this->getResponse('notification_invalid_domain');
            }
        }

        // Check IP mismatch
        if (! empty($license->license_ip)) {
            $ips = array_map('trim', explode(',', $license->license_ip));
            if (! in_array($ip, $ips)) {
                return $this->getResponse('notification_invalid_ip');
            }
        }

        return $this->getResponse('notification_license_ok', [
            'license_code' => $license->license_code,
            'product_id' => $license->product_id,
            'license_expire_date' => $license->license_expire_date,
            'license_updates_date' => $license->license_updates_date,
            'license_support_date' => $license->license_support_date,
        ]);
    }

    /**
     * Process license installation callback.
     */
    public function processLicenseInstallation(Request $request): array
    {
        $licenseCode = $request->input('license_code');
        $ip = $request->ip();
        $domain = $request->input('root_url', $request->input('domain'));

        // Check banned hosts
        if ($this->isHostBanned($ip)) {
            return $this->getResponse('notification_host_banned');
        }

        // Verify license exists
        $license = License::where('license_code', $licenseCode)->first();
        if (! $license) {
            return $this->getResponse('notification_license_not_found');
        }

        // Check installation limit
        $activeCount = $this->installationService->countActiveInstallations($licenseCode);
        if ($license->license_limit > 0 && $activeCount >= $license->license_limit) {
            return $this->getResponse('notification_license_limit');
        }

        // Register installation
        $this->installationService->register([
            'product_id' => $license->product_id,
            'user_id' => $license->user_id ?? 0,
            'license_code' => $licenseCode,
            'installation_ip' => $ip,
            'installation_domain' => $domain,
            'installation_status' => 1,
        ]);

        return $this->getResponse('notification_license_ok');
    }

    /**
     * Check if IP is banned.
     */
    public function isHostBanned(string $ip): bool
    {
        return LicenseBannedHost::where('banned_host_ip', $ip)->exists();
    }

    /**
     * Check if IP is whitelisted.
     */
    public function isIpWhitelisted(string $ip): bool
    {
        return LicenseWhitelistIp::where('whitelist_host_ip', $ip)->exists();
    }

    /**
     * Log callback.
     */
    protected function logCallback(string $licenseCode, string $ip, ?string $domain): void
    {
        LicenseCallback::create([
            'license_code' => $licenseCode,
            'callback_ip' => $ip,
            'callback_domain' => $domain,
            'callback_date_time' => now(),
            'callback_status' => 1,
        ]);
    }

    /**
     * Get license notification response.
     */
    protected function getResponse(string $notificationKey, array $data = []): array
    {
        $notification = LicenseNotification::first();
        $message = $notification ? ($notification->{$notificationKey} ?? $notificationKey) : $notificationKey;

        return array_merge([
            'notification_case' => $notificationKey,
            'notification_text' => $message,
            'status' => str_contains($notificationKey, '_ok') ? 'ok' : 'error',
        ], $data);
    }
}
