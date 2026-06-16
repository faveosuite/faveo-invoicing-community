<?php

namespace App\Plugins\Razorpay\Controllers;

use Exception;
use App\Jobs\CancelGatewaySubscriptionsJob;
use Razorpay\Api\Errors\BadRequestError;
use App\ApiKey;
use App\Http\Controllers\Controller;
use App\Model\Common\StatusSetting;
use App\Services\Payment\ProcessingFee;
use Illuminate\Http\Request;
use Razorpay\Api\Api;

class SettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    public function getSettings()
    {
        try {
            $keys = ApiKey::select('rzp_key', 'rzp_secret', 'apilayer_key', 'razorpay_webhook_secret')->first();
            $status = StatusSetting::select('razorpay_auto_renewal')->first();

            return successResponse('', [
                'rzp_key' => $keys->rzp_key ?? '',
                'rzp_secret' => $keys->rzp_secret ?? '',
                'apilayer_key' => $keys->apilayer_key ?? '',
                'webhook_secret' => $keys->razorpay_webhook_secret ?? '',
                'processing_fee' => (string) ProcessingFee::percent('razorpay'),
                'auto_renewal' => (bool) ($status->razorpay_auto_renewal ?? false),
                'webhook_url' => url('webhook/razorpay'),
            ]);
        } catch (Exception $e) {
            return errorResponse($e->getMessage());
        }
    }

    public function updateApiKey(Request $request)
    {
        $request->validate([
            'rzp_key' => ['required', 'string'],
            'rzp_secret' => ['required', 'string'],
            'webhook_secret' => ['nullable', 'string'],
            'processing_fee' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'auto_renewal' => ['nullable', 'boolean'],
        ], [
            'rzp_key.required' => __('message.razorpay_key_required'),
            'rzp_secret.required' => __('message.razorpay_secret_required'),
        ]);

        try {
            $api = new Api($request->input('rzp_key'), $request->input('rzp_secret'));
            $api->order->create(['receipt' => 'key-validation', 'amount' => 2000 * 100, 'currency' => 'INR', 'payment_capture' => 1]);

            ApiKey::find(1)->update([
                'rzp_key' => $request->input('rzp_key'),
                'rzp_secret' => $request->input('rzp_secret'),
                'apilayer_key' => $request->input('apilayer_key'),
                'razorpay_webhook_secret' => $request->input('webhook_secret'),
            ]);

            ProcessingFee::store('razorpay', (float) $request->input('processing_fee', 0));

            if ($request->filled('status')) {
                StatusSetting::find(1)->update(['rzp_status' => $request->input('status')]);
            }

            if ($request->has('auto_renewal')) {
                $enabling = $request->boolean('auto_renewal');
                StatusSetting::find(1)->update(['razorpay_auto_renewal' => $enabling ? 1 : 0]);

                if (! $enabling) {
                    dispatch(new CancelGatewaySubscriptionsJob('razorpay'));
                }
            }

            return successResponse(__('message.razorpay_settings_updated_successfully'));
        } catch (BadRequestError|Exception $e) {
            return errorResponse($e->getMessage());
        }
    }
}
