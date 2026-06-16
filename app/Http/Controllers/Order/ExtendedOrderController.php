<?php

namespace App\Http\Controllers\Order;

use App\Model\Order\OrderInvoiceRelation;
use Lang;
use Exception;
use Illuminate\Support\Str;
use Logger;
use App\License\Services\LicenseService;
use App\License\Services\InstallationService;
use Auth;
use DB;
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
            $execute = $this->executeOrder($invoiceid);

            //only for cloud
            $invoice = Invoice::find($invoiceid);
            $cloud_domain = $invoice->cloud_domain;
            if (! empty($cloud_domain)) {
                $user_id = $invoice->user_id;
                $cloudProductIds = CloudProducts::pluck('cloud_product');
                $orderNumber = Order::whereIn('id', OrderInvoiceRelation::where('invoice_id', $invoiceid)->pluck('order_id'))
                    ->whereIn('product', $cloudProductIds)
                    ->value('number');
                if ($orderNumber) {
                    new TenantController(new Client, new FaveoCloud())->createTenant(new Request(['orderNo' => $orderNumber, 'domain' => $cloud_domain, 'userInfo' => $user_id]));
                }
            }

            return back()->with('success', Lang::get('message.saved-successfully'));
        } catch (Exception $ex) {
            return back()->with('fails', $ex->getMessage());
        }
    }

    /**
     * generate serial key and add no of agents in the last 4 digits og the 16 string/digit serial key .
     *
     * @param  int  $productid
     * @param  int  $agents  No Of Agents
     * @return string The Final Serial Key after adding no of agents in the last 4 digits
     *
     * @throws Exception
     */
    public function generateSerialKey(int $productid, $agents)
    {
        try {
            $len = strlen($agents);
            $lastFour = match ((string) $len) {
                '1' => '000'.$agents,
                '2' => '00'.$agents,
                '3' => '0'.$agents,
                '4' => $agents,
                default => '0000',
            };
            $str = strtoupper(Str::random(12));
            $licCode = $str.$lastFour;

            return $licCode;
        } catch (Exception $ex) {
            Logger::exception($ex);

            throw new Exception($ex->getMessage());
        }
    }

    public function generateNumber()
    {
        try {
            return random_int('10000000', '99999999');
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
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
        $ipAndDomain = LicenseService::parseIpAndDomain($order->domain);
        $l_expiry = strtotime((string) $licenseExpiry) > 1 ? date('Y-m-d', strtotime((string) $licenseExpiry)) : '';
        $u_expiry = strtotime((string) $updatesExpiry) > 1 ? date('Y-m-d', strtotime((string) $updatesExpiry)) : '';
        $s_expiry = strtotime((string) $supportExpiry) > 1 ? date('Y-m-d', strtotime((string) $supportExpiry)) : '';
        $licenseService = resolve(LicenseService::class);
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

        //Remove old installations so the install slots are freed for the new allowed domains
        resolve(InstallationService::class)->deleteByLicenseCode($licenseCode);

        return ['message' => 'success', 'update' => __('message.license_domain_updated')];
    }

    public function reissueLicense(Request $request)
    {
        $request->validate(['id' => ['required']]);

        try {
            $order = Order::with('subscription')->findOrFail($request->input('id'));

            if (Auth::user()->role !== 'admin' && $order->client != Auth::id()) {
                return errorResponse(__('message.reissue_license_invalid_modification_data'), 403);
            }

            $licenseCode = $order->serial_key;

            DB::transaction(function () use ($order, $licenseCode): void {
                // Clear the bound domain so the license can be activated afresh.
                $order->domain = '';
                $order->save();

                $licenseService = resolve(LicenseService::class);
                $existingLicense = $licenseService->findByCode($licenseCode);

                if ($existingLicense) {
                    $subscription = $order->subscription;

                    // Domain is now empty, so the binding (domain/ip/require-domain) resets.
                    $licenseService->update($existingLicense->id, [
                        'license_order_number' => $order->number,
                        'license_require_domain' => 0,
                        'license_domain' => '',
                        'license_ip' => '',
                        'license_expire_date' => $this->toLicenseDate($subscription?->ends_at) ?: $existingLicense->license_expire_date,
                        'license_updates_date' => $this->toLicenseDate($subscription?->update_ends_at) ?: $existingLicense->license_updates_date,
                        'license_support_date' => $this->toLicenseDate($subscription?->support_ends_at) ?: $existingLicense->license_support_date,
                    ]);
                }

                // Delete existing installations so the slots are freed and the
                // user can re-install on a new domain. The install limit check
                // counts rows regardless of installation_status, so deactivating
                // (status => 0) would NOT free the slot — they must be removed.
                resolve(InstallationService::class)
                    ->deleteByLicenseCode($licenseCode);
            });

            return successResponse(__('message.license_reissued'));
        } catch (Exception $ex) {
            return errorResponse($ex->getMessage());
        }
    }

    /**
     * Normalise a subscription date into the Y-m-d license format,
     * returning '' when the date is empty/unset.
     */
    private function toLicenseDate($date): string
    {
        return $date && strtotime((string) $date) > 1 ? date('Y-m-d', strtotime((string) $date)) : '';
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
