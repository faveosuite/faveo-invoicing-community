<?php

namespace App\Traits\Order;

use App\Http\Controllers\License\LicensePermissionsController;
use App\Model\Order\Order;
use App\Model\Product\Subscription;
use Illuminate\Http\Request;

////////////////////////////////////////////////////////////////////////////
////////////// TRAIT FOR UPDATING DATES FOR ORDER/INVOICE //////////////////
////////////////////////////////////////////////////////////////////////////

trait UpdateDates
{
    /*
    Edit Updates Expiry Date In aDmin panel
     */
    public function editUpdateExpiry(Request $request)
    {
        $this->validate($request, [
            'date' => 'required',
        ]);

        try {
            $productId = Subscription::where('order_id', $request->input('orderid'))->pluck('product_id')->first();
            $licenseSupportExpiry = Subscription::where('order_id', $request->input('orderid'))
            ->select('ends_at', 'support_ends_at')->first();
            $permissions = LicensePermissionsController::getPermissionsForProduct($productId);
            if ($permissions['generateUpdatesxpiryDate'] == 1) {
                $newDate = $request->input('date');
                $date = \DateTime::createFromFormat('m/d/Y', $newDate);
                $date = $date->format('Y-m-d H:i:s');

                $subscription = Subscription::where('order_id', $request->input('orderid'))->first();

                if ($subscription) {
                    $subscription->update_ends_at = $date;
                    $subscription->save();
                }
                $this->editUpdateDateInAPL($request->input('orderid'), $date, $licenseSupportExpiry);
            }

            if (Order::where('id', $request->get('orderid'))->value('license_mode') == 'File') {
                Order::where('id', $request->get('orderid'))->update(['is_downloadable' => 0]);
            }

            return ['message' => 'success', 'update' => 'Updates Expiry Date Updated Successfully'];
        } catch (\Exception $ex) {
            $result = [$ex->getMessage()];

            return response()->json(compact('result'), 500);
        }
    }

    //Update Updates Expry in Licensing
    public function editUpdateDateInAPL($orderId, $expiryDate, $licenseSupportExpiry)
    {
        $order = Order::find($orderId);
        $licenseExpiry = strtotime($licenseSupportExpiry->ends_at) > 1 ? date('Y-m-d', strtotime($licenseSupportExpiry->ends_at)) : '';
        $supportExpiry = strtotime($licenseSupportExpiry->support_ends_at) > 1 ? date('Y-m-d', strtotime($licenseSupportExpiry->support_ends_at)) : '';
        $expiryDate = strtotime($expiryDate) > 1 ? date('Y-m-d', strtotime($expiryDate)) : '';
        $installService = app(\App\Modules\License\Services\InstallationService::class);
        $licenseService = app(\App\Modules\License\Services\LicenseService::class);
        $noOfAllowedInstallation = $installService->countActiveInstallations($order->serial_key);
        $ipAndDomain = \App\Modules\License\Services\LicenseService::parseIpAndDomain($order->domain);
        $existingLicense = $licenseService->findByCode($order->serial_key);
        if ($existingLicense) {
            $licenseService->update($existingLicense->id, [
                'license_order_number' => $order->number,
                'license_domain' => $ipAndDomain['domain'],
                'license_ip' => $ipAndDomain['ip'],
                'license_require_domain' => $ipAndDomain['requireDomain'],
                'license_expire_date' => $licenseExpiry,
                'license_updates_date' => $expiryDate,
                'license_support_date' => $supportExpiry,
                'license_limit' => $noOfAllowedInstallation ?: 2,
            ]);
        }
    }

    /*
    Edit License Expiry Date In aDmin panel
     */
    public function editLicenseExpiry(Request $request)
    {
        $this->validate($request, [
            'date' => 'required',
        ]);

        try {
            $productId = Subscription::where('order_id', $request->input('orderid'))->pluck('product_id')->first();
            $updatesSupportExpiry = Subscription::where('order_id', $request->input('orderid'))
            ->select('update_ends_at', 'support_ends_at')->first();
            $permissions = LicensePermissionsController::getPermissionsForProduct($productId);
            if ($permissions['generateLicenseExpiryDate'] == 1) {
                $newDate = $request->input('date');
                $date = \DateTime::createFromFormat('m/d/Y', $newDate);
                $date = $date->format('Y-m-d H:i:s');

                $subscription = Subscription::where('order_id', $request->input('orderid'))->first();

                if ($subscription) {
                    $subscription->ends_at = $date;
                    $subscription->save();
                }

                $this->editLicenseDateInAPL($request->input('orderid'), $date, $updatesSupportExpiry);
            }

            if (Order::where('id', $request->get('orderid'))->value('license_mode') == 'File') {
                Order::where('id', $request->get('orderid'))->update(['is_downloadable' => 0]);
            }

            return ['message' => 'success', 'update' => 'License Expiry Date Updated Successfully'];
        } catch (\Exception $ex) {
            $result = [$ex->getMessage()];

            return response()->json(compact('result'), 500);
        }
    }

    //Update License Expiry in Licensing
    public function editLicenseDateInAPL($orderId, $date, $updatesSupportExpiry)
    {
        $order = Order::find($orderId);
        $expiryDate = strtotime($updatesSupportExpiry->update_ends_at) > 1 ? date('Y-m-d', strtotime($updatesSupportExpiry->update_ends_at)) : '';
        $supportExpiry = strtotime($updatesSupportExpiry->support_ends_at) > 1 ? date('Y-m-d', strtotime($updatesSupportExpiry->support_ends_at)) : '';
        $licenseExpiry = strtotime($date) > 1 ? date('Y-m-d', strtotime($date)) : '';
        $installService = app(\App\Modules\License\Services\InstallationService::class);
        $licenseService = app(\App\Modules\License\Services\LicenseService::class);
        $noOfAllowedInstallation = $installService->countActiveInstallations($order->serial_key);
        $ipAndDomain = \App\Modules\License\Services\LicenseService::parseIpAndDomain($order->domain);
        $existingLicense = $licenseService->findByCode($order->serial_key);
        if ($existingLicense) {
            $licenseService->update($existingLicense->id, [
                'license_order_number' => $order->number,
                'license_domain' => $ipAndDomain['domain'],
                'license_ip' => $ipAndDomain['ip'],
                'license_require_domain' => $ipAndDomain['requireDomain'],
                'license_expire_date' => $licenseExpiry,
                'license_updates_date' => $expiryDate,
                'license_support_date' => $supportExpiry,
                'license_limit' => $noOfAllowedInstallation ?: 2,
            ]);
        }
    }

    /*
    Edit Support Expiry Date In aDmin panel
     */
    public function editSupportExpiry(Request $request)
    {
        $this->validate($request, [
            'date' => 'required',
        ]);

        try {
            $productId = Subscription::where('order_id', $request->input('orderid'))->pluck('product_id')->first();
            $updatesLicenseExpiry = Subscription::where('order_id', $request->input('orderid'))
            ->select('update_ends_at', 'ends_at')->first();
            $permissions = LicensePermissionsController::getPermissionsForProduct($productId);
            if ($permissions['generateSupportExpiryDate'] == 1) {
                $newDate = $request->input('date');
                $date = \DateTime::createFromFormat('m/d/Y', $newDate);
                $date = $date->format('Y-m-d H:i:s');

                $subscription = Subscription::where('order_id', $request->input('orderid'))->first();

                if ($subscription) {
                    $subscription->support_ends_at = $date;
                    $subscription->save();
                }

                $this->editSupportDateInAPL($request->input('orderid'), $date, $updatesLicenseExpiry);
            }

            if (Order::where('id', $request->get('orderid'))->value('license_mode') == 'File') {
                Order::where('id', $request->get('orderid'))->update(['is_downloadable' => 0]);
            }

            return ['message' => 'success', 'update' => 'Support Expiry Date Updated Successfully'];
        } catch (\Exception $ex) {
            $result = [$ex->getMessage()];

            return response()->json(compact('result'), 500);
        }
    }

    //Update Support Expiry in Licensing
    public function editSupportDateInAPL($orderId, $date, $updatesLicenseExpiry)
    {
        $order = Order::find($orderId);
        $expiryDate = strtotime($updatesLicenseExpiry->update_ends_at) > 1 ? date('Y-m-d', strtotime($updatesLicenseExpiry->update_ends_at)) : '';
        $licenseExpiry = strtotime($updatesLicenseExpiry->ends_at) > 1 ? date('Y-m-d', strtotime($updatesLicenseExpiry->ends_at)) : '';
        $supportExpiry = strtotime($date) > 1 ? date('Y-m-d', strtotime($date)) : '';
        $installService = app(\App\Modules\License\Services\InstallationService::class);
        $licenseService = app(\App\Modules\License\Services\LicenseService::class);
        $noOfAllowedInstallation = $installService->countActiveInstallations($order->serial_key);
        $ipAndDomain = \App\Modules\License\Services\LicenseService::parseIpAndDomain($order->domain);
        $existingLicense = $licenseService->findByCode($order->serial_key);
        if ($existingLicense) {
            $licenseService->update($existingLicense->id, [
                'license_order_number' => $order->number,
                'license_domain' => $ipAndDomain['domain'],
                'license_ip' => $ipAndDomain['ip'],
                'license_require_domain' => $ipAndDomain['requireDomain'],
                'license_expire_date' => $licenseExpiry,
                'license_updates_date' => $expiryDate,
                'license_support_date' => $supportExpiry,
                'license_limit' => $noOfAllowedInstallation ?: 2,
            ]);
        }
    }

    /**
     * Update Installation Limit in licensing.
     *
     * @author Ashutosh Pathak <ashutosh.pathak@ladybirdweb.com>
     *
     * @date   2019-08-08T11:02:50+0530
     *
     * @param  Request
     * @return [type]
     */
    public function editInstallationLimit(Request $request)
    {
        $this->validate($request, [
            'limit' => 'required|numeric',
        ]);
        $order = Order::find($request->input('orderid'));
        $productId = Subscription::where('order_id', $request->input('orderid'))->pluck('product_id')->first();
        $updatesLicenseExpiry = Subscription::where('order_id', $request->input('orderid'))
            ->select('update_ends_at', 'ends_at', 'support_ends_at')->first();
        $expiryDate = $updatesLicenseExpiry->update_ends_at;
        $licenseExpiry = $updatesLicenseExpiry->ends_at;
        $supportExpiry = $updatesLicenseExpiry->support_ends_at;
        $licenseService = app(\App\Modules\License\Services\LicenseService::class);
        $ipAndDomain = \App\Modules\License\Services\LicenseService::parseIpAndDomain($order->domain);
        $l_expiry = strtotime($licenseExpiry) > 1 ? date('Y-m-d', strtotime($licenseExpiry)) : '';
        $u_expiry = strtotime($expiryDate) > 1 ? date('Y-m-d', strtotime($expiryDate)) : '';
        $s_expiry = strtotime($supportExpiry) > 1 ? date('Y-m-d', strtotime($supportExpiry)) : '';
        $existingLicense = $licenseService->findByCode($order->serial_key);
        if ($existingLicense) {
            $licenseService->update($existingLicense->id, [
                'license_order_number' => $order->number,
                'license_require_domain' => $ipAndDomain['requireDomain'],
                'license_expire_date' => $l_expiry ?: $existingLicense->license_expire_date,
                'license_updates_date' => $u_expiry ?: $existingLicense->license_updates_date,
                'license_support_date' => $s_expiry ?: $existingLicense->license_support_date,
                'license_domain' => $ipAndDomain['domain'],
                'license_ip' => $ipAndDomain['ip'],
                'license_limit' => $request->input('limit'),
            ]);
        }

        return ['message' => 'success', 'update' => 'Installation Limit Updated'];
    }
}
