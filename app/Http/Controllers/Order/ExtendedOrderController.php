<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tenancy\TenantController;
use App\Model\Common\FaveoCloud;
use App\Model\Order\Invoice;
use App\Model\Order\Order;
use App\Model\Product\CloudProducts;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class ExtendedOrderController extends Controller
{
    /**
     * Create orders.
     *
     * @param  Request  $request
     * @return type
     */
    public function orderExecute(Request $request)
    {
        try {
            $invoiceid = $request->input('invoiceid');
            $execute = $this->executeOrder($invoiceid, 'executed', true);

            //only for cloud
            $invoice = Invoice::find($invoiceid);
            $cloud_domain = $invoice->cloud_domain;
            if (! empty($cloud_domain)) {
                $user_id = $invoice->user_id;
                $cloudProductIds = CloudProducts::pluck('cloud_product');
                $orderNumber = Order::whereIn('id', \App\Model\Order\OrderInvoiceRelation::where('invoice_id', $invoiceid)->pluck('order_id'))
                    ->whereIn('product', $cloudProductIds)
                    ->value('number');
                if ($orderNumber) {
                    new TenantController(new Client, new FaveoCloud())->createTenant(new Request(['orderNo' => $orderNumber, 'domain' => $cloud_domain, 'userInfo' => $user_id]));
                }
            }

            if ($execute == 'success') {
                return redirect()->back()->with('success', \Lang::get('message.saved-successfully'));
            } else {
                return redirect()->back()->with('fails', \Lang::get('message.not-saved-successfully'));
            }
        } catch (\Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    /**
     * generate serial key and add no of agents in the last 4 digits og the 16 string/digit serial key .
     *
     * @param  int  $productid
     * @param  int  $agents  No Of Agents
     * @return string The Final Serial Key after adding no of agents in the last 4 digits
     *
     * @throws \Exception
     */
    public function generateSerialKey(int $productid, $agents)
    {
        try {
            $len = strlen($agents);
            switch ($len) {//Get Last Four digits based on No.Of Agents
                case '1':
                    $lastFour = '000'.$agents;
                    break;
                case '2':

                    $lastFour = '00'.$agents;
                    break;
                case '3':
                    $lastFour = '0'.$agents;
                    break;
                case '4':
                    $lastFour = $agents;

                    break;
                default:
                    $lastFour = '0000';
                    break;
            }
            $str = strtoupper(str_random(12));
            $licCode = $str.$lastFour;

            return $licCode;
        } catch (\Exception $ex) {
            \Logger::exception($ex);

            throw new \Exception($ex->getMessage());
        }
    }

    public function generateNumber()
    {
        try {
            return rand('10000000', '99999999');
        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage());
        }
    }

    public function changeDomain(Request $request)
    {
        $domain = '';
        $arrayOfDomains = [];
        $allDomains = $request->input('domain');
        $seperateDomains = explode(',', $allDomains); //Bifurcate the domains here
        $allowedDomains = $this->getAllowedDomains($seperateDomains);
        $id = $request->input('id');
        $order = Order::findorFail($id);
        $licenseCode = $order->serial_key;
        $order->domain = implode(',', $allowedDomains);
        $order->save();
        $licenseExpiry = $order->subscription->ends_at;
        $updatesExpiry = $order->subscription->update_ends_at;
        $supportExpiry = $order->subscription->support_ends_at;
        $ipAndDomain = \App\License\Services\LicenseService::parseIpAndDomain($order->domain);
        $l_expiry = strtotime($licenseExpiry) > 1 ? date('Y-m-d', strtotime($licenseExpiry)) : '';
        $u_expiry = strtotime($updatesExpiry) > 1 ? date('Y-m-d', strtotime($updatesExpiry)) : '';
        $s_expiry = strtotime($supportExpiry) > 1 ? date('Y-m-d', strtotime($supportExpiry)) : '';
        $licenseService = app(\App\License\Services\LicenseService::class);
        $existingLicense = $licenseService->findByCode($licenseCode);
        if ($existingLicense) {
            $licenseService->update($existingLicense->id, [
                'license_order_number' => $order->number,
                'license_require_domain' => $ipAndDomain['requireDomain'],
                'license_expire_date' => $l_expiry ?: $existingLicense->license_expire_date,
                'license_updates_date' => $u_expiry ?: $existingLicense->license_updates_date,
                'license_support_date' => $s_expiry ?: $existingLicense->license_support_date,
                'license_domain' => $ipAndDomain['domain'],
                'license_ip' => $ipAndDomain['ip'],
            ]);
        }
        //Now make Installation status as inactive
        app(\App\License\Services\InstallationService::class)->updateByLicenseCode($licenseCode, ['installation_status' => 0]);

        return ['message' => 'success', 'update' => __('message.license_domain_updated')];
    }

    public function reissueLicense(Request $request)
    {
        $order = Order::findorFail($request->input('id'));
        if (\Auth::user()->role != 'admin' && $order->client != \Auth::user()->id) {
            return errorResponse(__('message.reissue_license_invalid_modification_data'));
        }
        $order->domain = '';
        $licenseCode = $order->serial_key;
        $order->save();
        $licenseExpiry = $order->subscription->ends_at;
        $updatesExpiry = $order->subscription->update_ends_at;
        $supportExpiry = $order->subscription->support_ends_at;
        $ipAndDomain = \App\License\Services\LicenseService::parseIpAndDomain($order->domain);
        $l_expiry = strtotime($licenseExpiry) > 1 ? date('Y-m-d', strtotime($licenseExpiry)) : '';
        $u_expiry = strtotime($updatesExpiry) > 1 ? date('Y-m-d', strtotime($updatesExpiry)) : '';
        $s_expiry = strtotime($supportExpiry) > 1 ? date('Y-m-d', strtotime($supportExpiry)) : '';
        $licenseService = app(\App\License\Services\LicenseService::class);
        $existingLicense = $licenseService->findByCode($licenseCode);
        if ($existingLicense) {
            $licenseService->update($existingLicense->id, [
                'license_order_number' => $order->number,
                'license_require_domain' => $ipAndDomain['requireDomain'],
                'license_expire_date' => $l_expiry ?: $existingLicense->license_expire_date,
                'license_updates_date' => $u_expiry ?: $existingLicense->license_updates_date,
                'license_support_date' => $s_expiry ?: $existingLicense->license_support_date,
                'license_domain' => $ipAndDomain['domain'],
                'license_ip' => $ipAndDomain['ip'],
            ]);
        }
        //Now make Installation status as inactive
        app(\App\License\Services\InstallationService::class)->updateByLicenseCode($licenseCode, ['installation_status' => 0]);

        return ['message' => 'success', 'update' => __('message.license_reissued')];
    }

    public function getAllowedDomains($seperateDomains)
    {
        $needle = 'www';
        foreach ($seperateDomains as $domain) {
            $allowedDomains[] = $domain;
        }

        return  $allowedDomains;
    }
}
