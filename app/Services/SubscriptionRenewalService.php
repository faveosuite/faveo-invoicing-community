<?php

namespace App\Services;

use App\Http\Controllers\License\LicensePermissionsController;
use App\License\Services\InstallationService;
use App\License\Services\LicenseService;
use App\Model\Order\Order;
use App\Model\Product\Subscription;
use Illuminate\Support\Facades\Date;
use Logger;
use Throwable;

class SubscriptionRenewalService
{
    /**
     * Extend subscription dates by the plan's days, then sync with the license server.
     *
     * @param  bool  $fromNowIfExpired  true for auto-renewal: if the date has already passed,
     *                                  extend from now() instead of the expired date.
     *                                  false for manual renewal: always extend from the stored date.
     */
    public function extendDates(Subscription $sub, int $days, bool $fromNowIfExpired = false): void
    {
        $permissions = LicensePermissionsController::getPermissionsForProduct($sub->product_id);

        $licenseExpiry = $this->computeExpiry($permissions['generateLicenseExpiryDate'], $sub->ends_at, $days, $fromNowIfExpired);
        $updatesExpiry = $this->computeExpiry($permissions['generateUpdatesxpiryDate'], $sub->update_ends_at, $days, $fromNowIfExpired);
        $supportExpiry = $this->computeExpiry($permissions['generateSupportExpiryDate'], $sub->support_ends_at, $days, $fromNowIfExpired);

        $sub->ends_at = $licenseExpiry ? Date::parse($licenseExpiry) : null;
        $sub->update_ends_at = $updatesExpiry ? Date::parse($updatesExpiry) : null;
        $sub->support_ends_at = $supportExpiry ? Date::parse($supportExpiry) : null;
        $sub->save();

        $this->syncLicenseServer($sub, $licenseExpiry, $updatesExpiry, $supportExpiry);
    }

    private function computeExpiry(bool $permission, mixed $currentDate, int $days, bool $fromNowIfExpired): ?string
    {
        if (! $permission || $days <= 0 || ! $currentDate) {
            return $currentDate;
        }

        $date = Date::parse($currentDate);

        if ($fromNowIfExpired && $date->isPast()) {
            $date = now();
        }

        return $date->addDays($days)->toDateTimeString();
    }

    private const array PERMISSION_MAP = [
        'ends_at' => 'generateLicenseExpiryDate',
        'update_ends_at' => 'generateUpdatesxpiryDate',
        'support_ends_at' => 'generateSupportExpiryDate',
    ];

    /**
     * Set a specific date field manually (admin panel).
     * Checks permission, saves, and syncs license server.
     *
     * @return bool Whether the field was actually permitted and written —
     *              callers must surface this, not assume success.
     */
    public function setDate(Subscription $sub, string $field, string $date): bool
    {
        $permissions = LicensePermissionsController::getPermissionsForProduct($sub->product_id);

        if (! ($permissions[self::PERMISSION_MAP[$field]] ?? false)) {
            return false;
        }

        $sub->$field = $date;
        $sub->save();

        $this->syncLicense($sub);

        return true;
    }

    public function updateInstallationLimit(Subscription $sub, int $limit): void
    {
        $licenseService = resolve(LicenseService::class);
        /** @var Order $order */
        $order = Order::find($sub->order_id);
        $ipAndDomain = LicenseService::parseIpAndDomain($order->domain);
        $existingLicense = $licenseService->findByCode($order->serial_key);

        if (! $existingLicense) {
            return;
        }

        $licenseService->update($existingLicense->id, [
            'license_order_number' => $order->number,
            'license_domain' => $ipAndDomain['domain'],
            'license_ip' => $ipAndDomain['ip'],
            'license_require_domain' => $ipAndDomain['requireDomain'],
            'license_expire_date' => $sub->ends_at ? Date::parse($sub->ends_at)->format('Y-m-d') : $existingLicense->license_expire_date,
            'license_updates_date' => $sub->update_ends_at ? Date::parse($sub->update_ends_at)->format('Y-m-d') : $existingLicense->license_updates_date,
            'license_support_date' => $sub->support_ends_at ? Date::parse($sub->support_ends_at)->format('Y-m-d') : $existingLicense->license_support_date,
            'license_limit' => $limit,
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

    /**
     * Never lets a failure here propagate — by the time this runs, the caller
     * may have already recorded a real gateway charge (e.g. a renewal invoice
     * marked paid), and a license-server hiccup must not undo that.
     */
    private function syncLicenseServer(Subscription $sub, mixed $licenseExpiry, mixed $updatesExpiry, mixed $supportExpiry): void
    {
        try {
            $installService = resolve(InstallationService::class);
            $licenseService = resolve(LicenseService::class);

            /** @var Order $subOrder */
            $subOrder = $sub->order;
            $licenseCode = $subOrder->serial_key;
            $domain = $subOrder->domain;
            $orderNo = $subOrder->number;
            $ipAndDomain = LicenseService::parseIpAndDomain($domain);
            $existingLicense = $licenseService->findByCode($licenseCode);

            if (! $existingLicense) {
                return;
            }

            $licenseService->update($existingLicense->id, [
                'license_order_number' => $orderNo,
                'license_domain' => $ipAndDomain['domain'],
                'license_ip' => $ipAndDomain['ip'],
                'license_require_domain' => $ipAndDomain['requireDomain'],
                'license_expire_date' => $licenseExpiry ? Date::parse($licenseExpiry)->format('Y-m-d') : '',
                'license_updates_date' => $updatesExpiry ? Date::parse($updatesExpiry)->format('Y-m-d') : '',
                'license_support_date' => $supportExpiry ? Date::parse($supportExpiry)->format('Y-m-d') : '',
                'license_limit' => $installService->countActiveInstallations($licenseCode) ?: 2,
            ]);
        } catch (Throwable $throwable) {
            Logger::exception($throwable);
        }
    }
}
