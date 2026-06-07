<?php

namespace App\Services;

use App\Http\Controllers\License\LicensePermissionsController;
use App\Model\Order\Order;
use App\Model\Product\Subscription;
use Carbon\Carbon;

class SubscriptionRenewalService
{
    /**
     * Extend subscription dates by the plan's days, then sync with the license server.
     *
     * @param bool $fromNowIfExpired  true for auto-renewal: if the date has already passed,
     *                                extend from now() instead of the expired date.
     *                                false for manual renewal: always extend from the stored date.
     */
    public function extendDates(Subscription $sub, int $days, bool $fromNowIfExpired = false): void
    {
        $permissions = LicensePermissionsController::getPermissionsForProduct($sub->product_id);

        $licenseExpiry = $this->computeExpiry($permissions['generateLicenseExpiryDate'], $sub->ends_at, $days, $fromNowIfExpired);
        $updatesExpiry = $this->computeExpiry($permissions['generateUpdatesxpiryDate'], $sub->update_ends_at, $days, $fromNowIfExpired);
        $supportExpiry = $this->computeExpiry($permissions['generateSupportExpiryDate'], $sub->support_ends_at, $days, $fromNowIfExpired);

        $sub->ends_at         = $licenseExpiry;
        $sub->update_ends_at  = $updatesExpiry;
        $sub->support_ends_at = $supportExpiry;
        $sub->save();

        $order = Order::find($sub->order_id);

        if ($order?->license_mode === 'File') {
            $order->update(['is_downloadable' => 0]);
        } else {
            $this->syncLicenseServer($sub, $licenseExpiry, $updatesExpiry, $supportExpiry);
        }
    }

    private function computeExpiry(bool $permission, $currentDate, int $days, bool $fromNowIfExpired): ?string
    {
        if (! $permission || $days <= 0 || ! $currentDate) {
            return $currentDate;
        }

        $date = Carbon::parse($currentDate);

        if ($fromNowIfExpired && $date->isPast()) {
            $date = now();
        }

        return $date->addDays($days)->toDateTimeString();
    }

    private const PERMISSION_MAP = [
        'ends_at'         => 'generateLicenseExpiryDate',
        'update_ends_at'  => 'generateUpdatesxpiryDate',
        'support_ends_at' => 'generateSupportExpiryDate',
    ];

    /**
     * Set a specific date field manually (admin panel).
     * Checks permission, saves, and syncs license server.
     */
    public function setDate(Subscription $sub, string $field, string $date): void
    {
        $permissions = LicensePermissionsController::getPermissionsForProduct($sub->product_id);

        if (! ($permissions[self::PERMISSION_MAP[$field]] ?? false)) {
            return;
        }

        $sub->$field = $date;
        $sub->save();

        $order = Order::find($sub->order_id);

        if ($order?->license_mode === 'File') {
            $order->update(['is_downloadable' => 0]);
        } else {
            $this->syncLicense($sub);
        }
    }

    public function updateInstallationLimit(Subscription $sub, int $limit): void
    {
        $licenseService  = app(\App\License\Services\LicenseService::class);
        $order           = Order::find($sub->order_id);
        $ipAndDomain     = \App\License\Services\LicenseService::parseIpAndDomain($order->domain);
        $existingLicense = $licenseService->findByCode($order->serial_key);

        if (! $existingLicense) {
            return;
        }

        $licenseService->update($existingLicense->id, [
            'license_order_number'   => $order->number,
            'license_domain'         => $ipAndDomain['domain'],
            'license_ip'             => $ipAndDomain['ip'],
            'license_require_domain' => $ipAndDomain['requireDomain'],
            'license_expire_date'    => $sub->ends_at ? Carbon::parse($sub->ends_at)->format('Y-m-d') : $existingLicense->license_expire_date,
            'license_updates_date'   => $sub->update_ends_at ? Carbon::parse($sub->update_ends_at)->format('Y-m-d') : $existingLicense->license_updates_date,
            'license_support_date'   => $sub->support_ends_at ? Carbon::parse($sub->support_ends_at)->format('Y-m-d') : $existingLicense->license_support_date,
            'license_limit'          => $limit,
        ]);
    }

    public function syncLicense(Subscription $sub): void
    {
        $this->syncLicenseServer(
            $sub,
            $sub->ends_at,
            $sub->update_ends_at,
            $sub->support_ends_at
        );
    }

    private function syncLicenseServer(Subscription $sub, $licenseExpiry, $updatesExpiry, $supportExpiry): void
    {
        $installService = app(\App\License\Services\InstallationService::class);
        $licenseService = app(\App\License\Services\LicenseService::class);

        $licenseCode    = $sub->order->serial_key;
        $domain         = $sub->order->domain;
        $orderNo        = $sub->order->number;
        $ipAndDomain    = \App\License\Services\LicenseService::parseIpAndDomain($domain);
        $existingLicense = $licenseService->findByCode($licenseCode);

        if (! $existingLicense) {
            return;
        }

        $licenseService->update($existingLicense->id, [
            'license_order_number'    => $orderNo,
            'license_domain'          => $ipAndDomain['domain'],
            'license_ip'              => $ipAndDomain['ip'],
            'license_require_domain'  => $ipAndDomain['requireDomain'],
            'license_expire_date'     => $licenseExpiry ? Carbon::parse($licenseExpiry)->format('Y-m-d') : '',
            'license_updates_date'    => $updatesExpiry ? Carbon::parse($updatesExpiry)->format('Y-m-d') : '',
            'license_support_date'    => $supportExpiry ? Carbon::parse($supportExpiry)->format('Y-m-d') : '',
            'license_limit'           => $installService->countActiveInstallations($licenseCode) ?: 2,
        ]);
    }
}
