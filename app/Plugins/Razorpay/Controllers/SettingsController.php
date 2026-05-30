<?php

namespace App\Plugins\Razorpay\Controllers;

use App\ApiKey;
use App\Http\Controllers\Controller;
use App\Model\Common\StatusSetting;
use Illuminate\Http\Request;
use Razorpay\Api\Api;

/**
 * Razorpay admin settings.
 *
 * The SPA invoice-payment flow lives in the standalone payment package
 * (App\Plugins\Payment, via App\Services\Payment\InvoicePaymentService). This
 * controller only handles admin configuration: reading (getSettings) and
 * updating (updateApiKey) the API keys.
 */
class SettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin', ['except' => []]);
    }

    public function getSettings()
    {
        try {
            $rzpKeys = ApiKey::select('rzp_key', 'rzp_secret', 'apilayer_key')->first();

            return successResponse('', [
                'rzp_key' => $rzpKeys->rzp_key ?? '',
                'rzp_secret' => $rzpKeys->rzp_secret ?? '',
                'apilayer_key' => $rzpKeys->apilayer_key ?? '',
            ]);
        } catch (\Exception $e) {
            return errorResponse($e->getMessage());
        }
    }

    /**
     * Validate the Razorpay keys (by creating a throwaway order — Razorpay has
     * no dedicated key-check endpoint) and store them.
     */
    public function updateApiKey(Request $request)
    {
        $request->validate([
            'rzp_key' => 'required|string',
            'rzp_secret' => 'required|string',
        ], [
            'rzp_key.required' => __('message.razorpay_key_required'),
            'rzp_secret.required' => __('message.razorpay_secret_required'),
        ]);

        try {
            $rzp_key = $request->input('rzp_key');
            $rzp_secret = $request->input('rzp_secret');

            $api = new Api($rzp_key, $rzp_secret);
            $api->order->create([
                'receipt' => 'key-validation',
                'amount' => 2000 * 100, // 2000 INR in paise
                'currency' => 'INR',
                'payment_capture' => 1,
            ]);

            ApiKey::find(1)->update([
                'rzp_key' => $rzp_key,
                'rzp_secret' => $rzp_secret,
                'apilayer_key' => $request->input('apilayer_key'),
            ]);

            // Only touch the enable/disable flag when the caller actually sends it,
            // so saving keys can't silently disable the gateway.
            if ($request->filled('status')) {
                StatusSetting::find(1)->update(['rzp_status' => $request->input('status')]);
            }

            return successResponse(__('message.razorpay_settings_updated_successfully'));
        } catch (\Razorpay\Api\Errors\BadRequestError $e) {
            return errorResponse($e->getMessage());
        } catch (\Exception $e) {
            return errorResponse($e->getMessage());
        }
    }
}
