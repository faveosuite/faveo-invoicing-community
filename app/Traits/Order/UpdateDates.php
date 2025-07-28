<?php

namespace App\Traits\Order;

use App\Http\Controllers\License\LicensePermissionsController;
use App\Model\Common\StatusSetting;
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
                Subscription::where('order_id', $request->input('orderid'))->update(['update_ends_at' => $date]);
                $checkUpdateStatus = StatusSetting::first()->pluck('license_status')->first();
                if ($checkUpdateStatus == 1) {
                    $this->editUpdateDateInAPL($request->input('orderid'), $date, $licenseSupportExpiry);
                }
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
        $noOfAllowedInstallation = '';
        $getInstallPreference = '';
        $licenseStatus = StatusSetting::pluck('license_status')->first();
        if ($licenseStatus == 1) {
            $cont = new \App\Http\Controllers\License\LicenseController();
            $noOfAllowedInstallation = $cont->getNoOfAllowedInstallation($order->serial_key, $order->product);
            $getInstallPreference = $cont->getInstallPreference($order->serial_key, $order->product);
        }
        $updateLicensedDomain = $cont->updateExpirationDate($order->serial_key, $expiryDate, $order->product, $order->domain, $order->number, $licenseExpiry, $supportExpiry, $noOfAllowedInstallation, $getInstallPreference);
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
                Subscription::where('order_id', $request->input('orderid'))->update(['ends_at' => $date]);
                $checkUpdateStatus = StatusSetting::first()->pluck('license_status')->first();
                if ($checkUpdateStatus == 1) {
                    $this->editLicenseDateInAPL($request->input('orderid'), $date, $updatesSupportExpiry);
                }
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
        $noOfAllowedInstallation = '';
        $getInstallPreference = '';
        $licenseStatus = StatusSetting::pluck('license_status')->first();
        if ($licenseStatus == 1) {
            $cont = new \App\Http\Controllers\License\LicenseController();
            $noOfAllowedInstallation = $cont->getNoOfAllowedInstallation($order->serial_key, $order->product);
            $getInstallPreference = $cont->getInstallPreference($order->serial_key, $order->product);
        }
        $updateLicensedDomain = $cont->updateExpirationDate($order->serial_key, $expiryDate, $order->product, $order->domain, $order->number, $licenseExpiry, $supportExpiry, $noOfAllowedInstallation, $getInstallPreference);
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
                Subscription::where('order_id', $request->input('orderid'))->update(['support_ends_at' => $date]);
                $checkUpdateStatus = StatusSetting::first()->pluck('license_status')->first();
                if ($checkUpdateStatus == 1) {
                    $this->editSupportDateInAPL($request->input('orderid'), $date, $updatesLicenseExpiry);
                }
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
        $noOfAllowedInstallation = '';
        $getInstallPreference = '';
        $licenseStatus = StatusSetting::pluck('license_status')->first();
        if ($licenseStatus == 1) {
            $cont = new \App\Http\Controllers\License\LicenseController();
            $noOfAllowedInstallation = $cont->getNoOfAllowedInstallation($order->serial_key, $order->product);
            $getInstallPreference = $cont->getInstallPreference($order->serial_key, $order->product);
        }
        $updateLicensedDomain = $cont->updateExpirationDate($order->serial_key, $expiryDate, $order->product, $order->domain, $order->number, $licenseExpiry, $supportExpiry, $noOfAllowedInstallation, $getInstallPreference);
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
        $cont = new \App\Http\Controllers\License\LicenseController();
        $getInstallPreference = $cont->getInstallPreference($order->serial_key, $order->product);
        $updateLicensedDomain = $cont->updateLicensedDomain($order->serial_key, $order->domain, $order->product, $licenseExpiry, $expiryDate, $supportExpiry, $order->number, $request->input('limit'), $getInstallPreference);

        return ['message' => 'success', 'update' => 'Installation Limit Updated'];
    }
}
