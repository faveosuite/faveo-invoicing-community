<?php

namespace App\Plugins\Payment_module\Razorpay\Controllers;

use App\ApiKey;
use App\Http\Controllers\Controller;
use App\Http\Controllers\SyncBillingToLatestVersion;
use App\Model\Common\StatusSetting;
use App\Plugins\Razorpay\Model\RazorpayPayment;
use Illuminate\Http\Request;
use Razorpay\Api\Api;

class SettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin', ['except' => ['postPaymentWithRazorpay']]);
    }

    public function Settings()
    {
        try {
            $razorpay1 = new RazorpayPayment();
            // //dd($ccavanue);
            $razorpay = $razorpay1->where('id', '1')->first();

            if (! $razorpay) {
                (new SyncBillingToLatestVersion)->sync();
            }
            $allCurrencies = RazorpayPayment::pluck('currencies', 'id')->toArray();
            $rzpkey = new ApiKey();
            $rzpKeys = $rzpkey->select('rzp_key', 'rzp_secret', 'apilayer_key', 'razorpay_processing_fee')->first();
            $baseCurrency = RazorpayPayment::pluck('base_currency')->toArray();
            $path = app_path().'/Plugins/Payment_module/Razorpay/views';
            \View::addNamespace('plugins', $path);

            return view('plugins::settings', compact('razorpay', 'baseCurrency', 'allCurrencies', 'rzpKeys'));
        } catch (\Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    public function updateApiKey(Request $request)
    {
        try {
            $rzp_key = $request->input('rzp_key');
            $rzp_secret = $request->input('rzp_secret');
            $api = new Api($rzp_key, $rzp_secret);
            $orderData = [
                'receipt' => '3456',
                'amount' => 2000 * 100, // 2000 rupees in paise
                'currency' => 'INR',
                'payment_capture' => 1, // auto capture
            ];

            $razorpayOrder = $api->order->create($orderData);
            $status = $request->input('status');
            $apilayer_key = $request->input('apilayer_key');
            StatusSetting::find(1)->update(['rzp_status' => $status]);
            ApiKey::find(1)->update(['rzp_key' => $rzp_key, 'rzp_secret' => $rzp_secret, 'apilayer_key' => $apilayer_key, 'razorpay_processing_fee' => $request->input('processing_fee')]);

            return successResponse(['success' => 'true', 'message' => __('message.razorpay_settings_updated_successfully')]);
        } catch (\Razorpay\Api\Errors\BadRequestError $e) {
            return errorResponse($e->getMessage());
        } catch (\Exception $e) {
            return errorResponse($e->getMessage());
        }

        // $apilayer_key = $request->input('apilayer_key');
        // $status = $request->input('status');
        // StatusSetting::find(1)->update(['rzp_status'=>$status]);
        // ApiKey::find(1)->update(['rzp_key'=>$rzp_key, 'rzp_secret'=>$rzp_secret, 'apilayer_key'=>$apilayer_key]);

        // return successResponse(['success'=>'true', 'message'=>'Razorpay Settings updated successfully']);
    }
}
