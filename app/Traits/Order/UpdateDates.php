<?php

namespace App\Traits\Order;

use App\Http\Controllers\License\LicenseController;
use App\Http\Controllers\License\LicensePermissionsController;
use App\Model\Common\StatusSetting;
use App\Model\Order\Order;
use App\Model\Product\Subscription;
use Carbon\Carbon;
use Illuminate\Http\Request;

trait UpdateDates
{
    private const UPDATE_EXPIRY = 'update_ends_at';
    private const LICENSE_EXPIRY = 'ends_at';
    private const SUPPORT_EXPIRY = 'support_ends_at';

    protected LicenseController $licenseController;


    /**
     * Edit Updates Expiry Date In Admin panel.
     */
    public function editUpdateExpiry(Request $request)
    {
        return $this->updateExpiryDate(
            $request,
            self::UPDATE_EXPIRY,
            'Updates Expiry Date Updated Successfully',
            'generateUpdatesxpiryDate'
        );
    }

    /**
     * Edit License Expiry Date In Admin panel.
     */
    public function editLicenseExpiry(Request $request)
    {
        return $this->updateExpiryDate(
            $request,
            self::LICENSE_EXPIRY,
            'License Expiry Date Updated Successfully',
            'generateLicenseExpiryDate'
        );
    }

    /**
     * Edit Support Expiry Date In Admin panel.
     */
    public function editSupportExpiry(Request $request)
    {
        return $this->updateExpiryDate(
            $request,
            self::SUPPORT_EXPIRY,
            'Support Expiry Date Updated Successfully',
            'generateSupportExpiryDate'
        );
    }

    /**
     * Generic method to update expiry dates.
     */
    private function updateExpiryDate(Request $request, $field, $successMessage, $permission)
    {
        $this->validate($request, ['date' => 'required']);

        try {
            $orderId = $request->input('orderid');
            $productId = $this->getProductId($orderId);

            $permissions = LicensePermissionsController::getPermissionsForProduct($productId);

            if ($permissions[$permission] !== 1) {
                return errorResponse(__('message.license_permission_denied'));
            }

            $newDate = $this->convertDate($request->input('date'));
            $subscription = $this->getSubscriptionData($orderId, $field);

            $sub = Subscription::where('order_id', $orderId)->firstOrFail();

            $sub->{$field} = $newDate;
            $sub->save();

            if ($this->shouldUpdateLicense()) {
                $this->updateLicenseDateInAPL($orderId, $newDate, $subscription, $field);
            }

            $this->handleFileLicense($orderId);

            return successResponse($successMessage);
        } catch (\Exception $ex) {
            return errorResponse($ex->getMessage());
        }
    }

    /**
     * Update Installation Limit in licensing.
     */
    public function editInstallationLimit(Request $request)
    {
        $this->validate($request, ['limit' => 'required|integer|min:1']);

        try {
            $order = Order::findOrFail($request->input('orderid'));
            $subscription = $this->getSubscriptionData($order->id, null);

            $this->licenseController->updateLicensedDomain(
                $order->serial_key,
                $order->domain,
                $order->product,
                $subscription->ends_at,
                $subscription->update_ends_at,
                $subscription->support_ends_at,
                $order->number,
                $request->input('limit'),
                $this->licenseController->getInstallPreference($order->serial_key, $order->product)
            );

            return successResponse('Installation Limit Updated');
        } catch (\Exception $ex) {
            return errorResponse($ex->getMessage());
        }
    }

    /**
     * Helper methods.
     */
    private function getProductId(int $orderId): int
    {
        return Subscription::where('order_id', $orderId)->value('product_id');
    }

    private function convertDate($date)
    {
        return Carbon::parse($date)->endOfDay();
    }

    private function getSubscriptionData($orderId, $excludeField = null)
    {
        $query = Subscription::where('order_id', $orderId)->select('update_ends_at', 'ends_at', 'support_ends_at');

        if ($excludeField) {
            $fields = ['update_ends_at', 'ends_at', 'support_ends_at'];
            $fields = array_filter($fields, fn ($field) => $field !== $excludeField);
            $query = Subscription::where('order_id', $orderId)->select(...$fields);
        }

        return $query->first();
    }

    private function shouldUpdateLicense(): bool
    {
        return StatusSetting::first()->license_status === 1;
    }

    private function handleFileLicense(int $orderId)
    {
        $order = Order::find($orderId);
        if ($order && $order->license_mode === 'File') {
            $order->update(['is_downloadable' => 0]);
        }
    }

    private function updateLicenseDateInAPL($orderId, $newDate, $subscriptionData, $field)
    {
        $order = Order::findOrFail($orderId);

        $dateMapping = [
            self::UPDATE_EXPIRY => [
                'licenseExpiry' => $subscriptionData->ends_at ?? '',
                'supportExpiry' => $subscriptionData->support_ends_at ?? '',
                'expiryDate' => $newDate,
            ],
            self::LICENSE_EXPIRY => [
                'expiryDate' => $subscriptionData->update_ends_at ?? '',
                'supportExpiry' => $subscriptionData->support_ends_at ?? '',
                'licenseExpiry' => $newDate,
            ],
            self::SUPPORT_EXPIRY => [
                'expiryDate' => $subscriptionData->update_ends_at ?? '',
                'licenseExpiry' => $subscriptionData->ends_at ?? '',
                'supportExpiry' => $newDate,
            ],
        ];

        $dates = $dateMapping[$field];

        $this->licenseController->updateExpirationDate(
            $order->serial_key,
            $this->formatDate($dates['expiryDate']),
            $order->product,
            $order->domain,
            $order->number,
            $this->formatDate($dates['licenseExpiry']),
            $this->formatDate($dates['supportExpiry']),
            $this->licenseController->getNoOfAllowedInstallation($order->serial_key, $order->product),
            $this->licenseController->getInstallPreference($order->serial_key, $order->product)
        );
    }

    private function formatDate($date)
    {
        if (empty($date) || strtotime($date) <= 1) {
            return '';
        }

        return date('Y-m-d', strtotime($date));
    }
}
