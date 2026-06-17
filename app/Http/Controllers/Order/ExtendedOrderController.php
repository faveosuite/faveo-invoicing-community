<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\License\Services\InstallationService;
use App\License\Services\LicenseService;
use App\Model\Order\Order;
use Auth;
use DB;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Logger;

class ExtendedOrderController extends Controller
{
    /**
     * generate serial key and add no of agents in the last 4 digits og the 16 string/digit serial key .
     *
     * @param  int  $agents  No Of Agents
     * @return string The Final Serial Key after adding no of agents in the last 4 digits
     *
     * @throws Exception
     */
    public function generateSerialKey(int $productid, $agents): string
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

            return $str.$lastFour;
        } catch (Exception $exception) {
            Logger::exception($exception);

            throw new Exception($exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    public function generateNumber(): int
    {
        try {
            return random_int('10000000', '99999999');
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage(), $exception->getCode(), $exception);
        }
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
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
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
}
