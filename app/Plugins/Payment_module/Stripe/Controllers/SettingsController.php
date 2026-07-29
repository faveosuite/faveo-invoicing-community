<?php

namespace App\Plugins\Payment_module\Stripe\Controllers;

use App\ApiKey;
use App\Http\Controllers\Controller;
use App\Http\Controllers\SyncBillingToLatestVersion;
use App\Plugins\Stripe\Model\StripePayment;
use App\Traits\Payment\PostPaymentHandle;
use Cartalyst\Stripe\Laravel\Facades\Stripe;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    use PostPaymentHandle;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin', ['except' => ['postPaymentWithStripe']]);
    }

    public function updateApiKey(Request $request)
    {
        $request->validate([
            'stripe_secret' => 'required|string',
            'stripe_key' => 'required|string',
        ], [
            'stripe_secret.required' => __('message.stripe_secret_required'),
            'stripe_key.required' => __('message.stripe_key_required'),
        ]);

        try {
            $stripe = Stripe::make($request->input('stripe_secret'));
            $response = $stripe->customers()->create(['description' => 'Test Customer to Validate Secret Key']);
            $stripe_secret = $request->input('stripe_secret');
            ApiKey::find(1)->update([
                'stripe_secret' => $request->input('stripe_secret'),
                'stripe_key' => $request->input('stripe_key'),
                'stripe_processing_fee' => $request->input('processing_fee'),
            ]);

            return successResponse(['success' => 'true', 'message' => __('message.stripe_settings_updated_successfully')]);
        } catch (\Cartalyst\Stripe\Exception\UnauthorizedException  $e) {
            return errorResponse($e->getMessage());
        } catch (\Exception $e) {
            return errorResponse($e->getMessage());
        }
    }

    public function Settings()
    {
        try {
            $stripe1 = new StripePayment();
            // //dd($ccavanue);
            $stripe = $stripe1->where('id', '1')->first();

            if (! $stripe) {
                (new SyncBillingToLatestVersion)->sync();
            }
            $allCurrencies = StripePayment::pluck('currencies', 'id')->toArray();
            $apikey = new ApiKey();
            $stripeKeys = $apikey->select('stripe_key', 'stripe_secret', 'stripe_processing_fee')->first();
            $baseCurrency = StripePayment::pluck('base_currency')->toArray();
            $path = app_path().'/Plugins/Payment_module/Stripe/views';
            \View::addNamespace('Plugins', $path);

            return view('Plugins::settings', compact('stripe', 'baseCurrency', 'allCurrencies', 'stripeKeys'));
        } catch (\Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

//    public function postSettings(Request $request)
//    {
//        $this->validate($request, [
//            'business' => 'required',
//            'cmd' => 'required',
//            'paypal_url' => 'required|url',
//            'success_url' => 'url',
//            'cancel_url' => 'url',
//            'notify_url' => 'url',
//            'currencies' => 'required',
//        ], [
//            'business.required' => __('validation.razorpay_val.business_required'),
//            'cmd.required' => __('validation.razorpay_val.cmd_required'),
//            'paypal_url.required' => __('validation.razorpay_val.paypal_url_required'),
//            'paypal_url.url' => __('validation.razorpay_val.paypal_url_invalid'),
//            'success_url.url' => __('validation.razorpay_val.success_url_invalid'),
//            'cancel_url.url' => __('validation.razorpay_val.cancel_url_invalid'),
//            'notify_url.url' => __('validation.razorpay_val.notify_url_invalid'),
//            'currencies.required' => __('validation.razorpay_val.currencies_required'),
//        ]);
//
//        try {
//            $ccavanue1 = new Paypal();
//            $ccavanue = $ccavanue1->where('id', '1')->first();
//            $ccavanue->fill($request->input())->save();
//
//            return redirect()->back()->with('success', \Lang::get('message.updated-successfully'));
//        } catch (\Exception $ex) {
//            return redirect()->back()->with('fails', $ex->getMessage());
//        }
//    }
}
