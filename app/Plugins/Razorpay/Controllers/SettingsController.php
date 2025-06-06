<?php

namespace App\Plugins\Razorpay\Controllers;

use App\ApiKey;
use App\Http\Controllers\Controller;
use App\Http\Controllers\SyncBillingToLatestVersion;
use App\Model\Common\FaveoCloud;
use App\Model\Common\Setting;
use App\Model\Common\StatusSetting;
use App\Plugins\Razorpay\Model\RazorpayPayment;
use Cartalyst\Stripe\Laravel\Facades\Stripe;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Razorpay\Api\Api;
use Schema;
use Validator;

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
            if (! Schema::hasTable('razorpay')) {
                Schema::create('razorpay', function ($table) {
                    $table->increments('id');
                    $table->string('image_url');
                    $table->string('processing_fee');
                    $table->string('base_currency');
                    $table->string('currencies');
                    $table->timestamps();
                });
            }

            $razorpay1 = new RazorpayPayment();
            // //dd($ccavanue);
            $razorpay = $razorpay1->where('id', '1')->first();

            if (! $razorpay) {
                (new SyncBillingToLatestVersion)->sync();
            }
            $allCurrencies = RazorpayPayment::pluck('currencies', 'id')->toArray();
            $rzpkey = new ApiKey();
            $rzpKeys = $rzpkey->select('rzp_key', 'rzp_secret', 'apilayer_key')->first();
            $baseCurrency = RazorpayPayment::pluck('base_currency')->toArray();
            $path = app_path().'/Plugins/Razorpay/views';
            \View::addNamespace('plugins', $path);

            return view('plugins::settings', compact('razorpay', 'baseCurrency', 'allCurrencies', 'rzpKeys'));
        } catch (\Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    public function postSettings(Request $request)
    {
        $this->validate($request, [
            'business' => 'required',
            'cmd' => 'required',
            'paypal_url' => 'required|url',
            'success_url' => 'url',
            'cancel_url' => 'url',
            'notify_url' => 'url',
            'currencies' => 'required',
        ], [
            'business.required' => __('validation.razorpay_val.business_required'),
            'cmd.required' => __('validation.razorpay_val.cmd_required'),
            'paypal_url.required' => __('validation.razorpay_val.paypal_url_required'),
            'paypal_url.url' => __('validation.razorpay_val.paypal_url_invalid'),
            'success_url.url' => __('validation.razorpay_val.success_url_invalid'),
            'cancel_url.url' => __('validation.razorpay_val.cancel_url_invalid'),
            'notify_url.url' => __('validation.razorpay_val.notify_url_invalid'),
            'currencies.required' => __('validation.razorpay_val.currencies_required'),
        ]);

        try {
            $ccavanue1 = new Paypal();
            $ccavanue = $ccavanue1->where('id', '1')->first();
            $ccavanue->fill($request->input())->save();

            return redirect()->back()->with('success', \Lang::get('message.updated-successfully'));
        } catch (\Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    public function changeBaseCurrency(Request $request)
    {
        $baseCurrency = Stripe::where('id', $request->input('b_currency'))->pluck('currencies')->first();
        $allCurrencies = Stripe::select('base_currency', 'id')->get();
        foreach ($allCurrencies as $currencies) {
            Stripe::where('id', $currencies->id)->update(['base_currency' => $baseCurrency]);
        }

        return ['message' => 'success', 'update' => 'Base Currency Updated'];
    }

    /*
    * Update Razorpay Details In Database
    */
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
            ApiKey::find(1)->update(['rzp_key' => $rzp_key, 'rzp_secret' => $rzp_secret, 'apilayer_key' => $apilayer_key]);

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

    /**
     * success response method.
     *
     * @return \Illuminate\Http\Response
     */
    public function postPaymentWithRazorpay(Request $request)
    {
        $validator = Validator::make($request->all(), [
        ]);
        $input = $request->all();
        $validation = [
            'card_no' => 'required',
            'exp_month' => 'required',
            'exp_year' => 'required',
            'cvv' => 'required',
        ];

        $this->validate($request, $validation);
        $stripeSecretKey = ApiKey::pluck('stripe_secret')->first();
        $stripe = Stripe::make($stripeSecretKey);
        try {
            $invoice = \Session::get('invoice');
            $invoiceTotal = \Session::get('totalToBePaid');
            $amount = rounding(\Cart::getTotal());
            if (! $amount) {//During renewal
                if (rounding($request->input('amount')) != rounding($invoiceTotal)) {
                    throw new \Exception(__('message.invalid_modification'));
                }
                $amount = rounding($request->input('amount'));
            }
            $stripeSecretKey = ApiKey::pluck('stripe_secret')->first();
            $stripe = Stripe::make($stripeSecretKey);
            $token = $stripe->tokens()->create([
                'card' => [
                    'number' => $request->get('card_no'),
                    'exp_month' => $request->get('exp_month'),
                    'exp_year' => $request->get('exp_year'),
                    'cvc' => $request->get('cvv'),
                ],
            ]);
            if (! isset($token['id'])) {
                \Session::put('error', __('message.stripe_token_not_generated_correctly'));

                return redirect()->route('stripform');
            }
            $customer = $stripe->customers()->create([
                'name' => \Auth::user()->first_name.' '.\Auth::user()->last_name,
                'email' => \Auth::user()->email,
                'address' => [
                    'line1' => \Auth::user()->address,
                    'postal_code' => \Auth::user()->zip,
                    'city' => \Auth::user()->town,
                    'state' => \Auth::user()->state,
                    'country' => \Auth::user()->country,
                ],
            ]);
            $stripeCustomerId = $customer['id'];
            $currency = strtolower($invoice->currency);
            $card = $stripe->cards()->create($stripeCustomerId, $token['id']);
            $charge = $stripe->charges()->create([
                'customer' => $customer['id'],
                'currency' => $currency,
                'amount' => $amount,
                'description' => 'Add in wallet',
            ]);
            if ($charge['status'] == 'succeeded') {
                //Change order Status as Success if payment is Successful
                $stateCode = \Auth::user()->state;
                $cont = new \App\Http\Controllers\RazorpayController();
                $state = $cont->getState($stateCode);
                $currency = $cont->getCurrency();

                $control = new \App\Http\Controllers\Order\RenewController();
                $cloud = new \App\Http\Controllers\Tenancy\CloudExtraActivities(new Client, new FaveoCloud());

                //After Regular Payment
                if ($control->checkRenew($invoice->is_renewed) === false) {
                    $checkout_controller = new \App\Http\Controllers\Front\CheckoutController();
                    $checkout_controller->checkoutAction($invoice);

                    $view = $cont->getViewMessageAfterPayment($invoice, $state, $currency);
                    $status = $view['status'];
                    $message = $view['message'];
                } elseif ($cloud->checkAgentAlteration()) {
                    $subId = \Session::get('AgentAlteration'); // use if needed in future
                    $newAgents = \Session::get('newAgents');
                    $orderId = \Session::get('orderId');
                    $installationPath = \Session::get('installation_path');
                    $productId = \Session::get('product_id');
                    $oldLicense = \Session::get('oldLicense');
                    $view = $cont->getViewMessageAfterPayment($invoice, $state, $currency);
                    $status = $view['status'];
                    $message = $view['message'];
                    $cloud->doTheAgentAltering($newAgents, $oldLicense, $orderId, $installationPath, $productId);
                } elseif ($cloud->checkUpgradeDowngrade()) {
                    $checkout_controller = new \App\Http\Controllers\Front\CheckoutController();
                    $checkout_controller->checkoutAction($invoice);
                    $oldLicense = \Session::get('upgradeOldLicense');
                    $installationPath = \Session::get('upgradeInstallationPath');
                    $productId = \Session::get('upgradeProductId');
                    $licenseCode = \Session::get('upgradeSerialKey');
                    $cloud->doTheProductUpgradeDowngrade($licenseCode, $installationPath, $productId, $oldLicense);
                    $view = $cont->getViewMessageAfterPayment($invoice, $state, $currency);
                    $status = $view['status'];
                    $message = $view['message'];
                } else {
                    //Afer Renew
                    $control->successRenew($invoice);
                    $payment = new \App\Http\Controllers\Order\InvoiceController();
                    $payment->postRazorpayPayment($invoice);
                    if ($invoice->grand_total && emailSendingStatus()) {
                        $this->sendPaymentSuccessMailtoAdmin($invoice->currency, $invoice->grand_total, \Auth::user(), $invoice->invoiceItem()->first()->product_name);
                    }
                    if (\Session::has('AgentAlterationRenew')) {
                        $newAgents = \Session::get('newAgentsRenew');
                        $orderId = \Session::get('orderIdRenew');
                        $installationPath = \Session::get('installation_pathRenew');
                        $productId = \Session::get('product_idRenew');
                        $oldLicense = \Session::get('oldLicenseRenew');
                        $cloud->doTheAgentAltering($newAgents, $oldLicense, $orderId, $installationPath, $productId);
                    }
                    $view = $cont->getViewMessageAfterRenew($invoice, $state, $currency);
                    $status = $view['status'];
                    $message = $view['message'];
                }
                \Session::forget('items');
                \Session::forget('code');
                \Session::forget('codevalue');
                \Session::forget('totalToBePaid');
                \Session::forget('invoice');
                \Session::forget('cart_currency');
                \Cart::removeCartCondition('Processing fee');

                return redirect('checkout')->with($status, $message);
            } else {
                return redirect('checkout')->with('fails', __('message.payment_declined_try_other_gateway'));
            }
        } catch (\Cartalyst\Stripe\Exception\ApiLimitExceededException|\Cartalyst\Stripe\Exception\BadRequestException|\Cartalyst\Stripe\Exception\MissingParameterException|\Cartalyst\Stripe\Exception\NotFoundException|\Cartalyst\Stripe\Exception\ServerErrorException|\Cartalyst\Stripe\Exception\StripeException|\Cartalyst\Stripe\Exception\UnauthorizedException $e) {
            if (emailSendingStatus()) {
                $this->sendFailedPaymenttoAdmin($request['amount'], $e->getMessage());
            }

            return redirect('checkout')->with('fails', __('message.payment_declined_error', ['error' => $e->getMessage()]));
        } catch (\Cartalyst\Stripe\Exception\CardErrorException $e) {
            if (emailSendingStatus()) {
                $this->sendFailedPaymenttoAdmin($request['amount'], $e->getMessage());
            }
            \Session::put('amount', $request['amount']);
            \Session::put('error', $e->getMessage());

            return redirect()->route('checkout');
        } catch (\Exception $e) {
            return redirect('checkout')->with('fails', __('message.payment_declined_error', ['error' => $e->getMessage()]));
        }
    }

    public static function sendFailedPaymenttoAdmin($amount, $exceptionMessage)
    {
        $setting = Setting::find(1);
        $paymentFailData = 'Payment for'.' '.'of'.' '.\Auth::user()->currency.' '.$amount.' '.'failed by'.' '.\Auth::user()->first_name.' '.\Auth::user()->last_name.' '.'. User Email:'.' '.\Auth::user()->email.'<br>'.'Reason:'.$exceptionMessage;

        $mail = new \App\Http\Controllers\Common\PhpMailController();
        $mail->sendEmail($setting->email, $setting->company_email, $paymentFailData, 'Payment failed ');
    }

    public static function sendPaymentSuccessMailtoAdmin($currency, $total, $user, $productName)
    {
        $setting = Setting::find(1);
        $templateController = new \App\Http\Controllers\Common\TemplateController();
        $paymentSuccessdata = 'Payment for'.' '.$productName.' '.'of'.' '.$currency.' '.$total.' '.'successful by'.' '.$user->first_name.' '.$user->last_name.' '.'Email:'.' '.$user->email;

        $mail = new \App\Http\Controllers\Common\PhpMailController();
        $mail->sendEmail($setting->email, $setting->company_email, $paymentSuccessdata, 'Payment Successful ');
    }
}
