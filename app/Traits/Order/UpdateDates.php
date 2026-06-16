<?php

namespace App\Traits\Order;

use Exception;
use Illuminate\Support\Facades\Date;
use App\Model\Product\Subscription;
use App\Services\SubscriptionRenewalService;
use Illuminate\Http\Request;

trait UpdateDates
{
    public function editUpdateExpiry(Request $request)
    {
        $this->validate($request, ['date' => 'required']);

        try {
            $sub = Subscription::where('order_id', $request->input('orderid'))->firstOrFail();
            resolve(SubscriptionRenewalService::class)->setDate($sub, 'update_ends_at', $this->parseDate($request->input('date')));

            return ['message' => 'success', 'update' => 'Updates Expiry Date Updated Successfully'];
        } catch (Exception $ex) {
            return response()->json(['result' => [$ex->getMessage()]], 500);
        }
    }

    public function editLicenseExpiry(Request $request)
    {
        $this->validate($request, ['date' => 'required']);

        try {
            $sub = Subscription::where('order_id', $request->input('orderid'))->firstOrFail();
            resolve(SubscriptionRenewalService::class)->setDate($sub, 'ends_at', $this->parseDate($request->input('date')));

            return ['message' => 'success', 'update' => 'License Expiry Date Updated Successfully'];
        } catch (Exception $ex) {
            return response()->json(['result' => [$ex->getMessage()]], 500);
        }
    }

    public function editSupportExpiry(Request $request)
    {
        $this->validate($request, ['date' => 'required']);

        try {
            $sub = Subscription::where('order_id', $request->input('orderid'))->firstOrFail();
            resolve(SubscriptionRenewalService::class)->setDate($sub, 'support_ends_at', $this->parseDate($request->input('date')));

            return ['message' => 'success', 'update' => 'Support Expiry Date Updated Successfully'];
        } catch (Exception $ex) {
            return response()->json(['result' => [$ex->getMessage()]], 500);
        }
    }

    public function editInstallationLimit(Request $request)
    {
        $this->validate($request, ['limit' => 'required|numeric']);

        $sub = Subscription::where('order_id', $request->input('orderid'))->firstOrFail();
        resolve(SubscriptionRenewalService::class)->updateInstallationLimit($sub, (int) $request->input('limit'));

        return ['message' => 'success', 'update' => 'Installation Limit Updated'];
    }

    private function parseDate(string $date): string
    {
        return Date::createFromFormat('m/d/Y', $date)->format('Y-m-d H:i:s');
    }
}
