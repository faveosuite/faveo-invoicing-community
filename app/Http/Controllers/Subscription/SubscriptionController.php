<?php

namespace App\Http\Controllers\Subscription;

use App\ApiKey;
use App\Auto_renewal;
use App\Http\Controllers\Common\CronController;
use App\Http\Controllers\ConcretePostSubscriptionHandleController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Order\BaseRenewController;
use App\Http\Controllers\RazorpayController;
use App\Model\Common\Country;
use App\Model\Common\StatusSetting;
use App\Model\Common\Template;
use App\Model\Common\TemplateType;
use App\Model\Mailjob\ExpiryMailDay;
use App\Model\Order\Invoice;
use App\Model\Order\InvoiceItem;
use App\Model\Order\Order;
use App\Model\Order\Payment;
use App\Model\Payment\Plan;
use App\Model\Payment\PlanPrice;
use App\Model\Product\Product;
use App\Model\Product\Subscription;
use App\Plugins\Stripe\Controllers\SettingsController;
use App\User;
use Carbon\Carbon;
use DateTime;
use Razorpay\Api\Api;

class SubscriptionController extends Controller
{
    protected $subscription;

    protected $order;

    protected $user;

    protected $template;

    protected $invoice;

    protected $PostSubscriptionHandle;

    public function __construct(ConcretePostSubscriptionHandleController $PostSubscriptionHandle)
    {
        $subscription = new Subscription();
        $this->sub = $subscription;

        $plan = new Plan();
        $this->plan = $plan;

        $order = new Order();
        $this->order = $order;

        $user = new User();
        $this->user = $user;

        $template = new Template();
        $this->template = $template;

        $invoice = new Invoice();
        $this->invoice = $invoice;

        $payment = new Payment();
        $this->payment = $payment;

        $stripeController = new SettingsController();
        $this->stripeController = $stripeController;

        $this->PostSubscriptionHandle = $PostSubscriptionHandle;
    }

    public function getOnDayExpiryInfoSubs()
    {
        $status = StatusSetting::value('subs_expirymail');
        if ($status == 1) {
            $daysArray = ExpiryMailDay::pluck('autorenewal_days')->toArray();

            if (empty($daysArray) || empty($daysArray[0])) {
                return [];
            }

            $decodedData = json_decode($daysArray[0]);

            if ($decodedData === null && json_last_error() !== JSON_ERROR_NONE) {
                return [];
            }

            $subscriptions = [];
            foreach ($decodedData as $day) {
                $day = (int) $day;
                $endDate = Carbon::now()->addDays($day)->toDateString();
                $subscriptionsForDay = Subscription::query()
                    ->select([
                        'subscriptions.*',
                        'orders.id as order_id',
                        'orders.*',
                        'subscriptions.id as id',
                    ])
                    ->join('orders', 'subscriptions.order_id', '=', 'orders.id')
                    ->where(function ($query) use ($endDate) {
                        $query->where('subscriptions.update_ends_at', 'LIKE', $endDate.'%')
                            ->orWhere('subscriptions.support_ends_at', 'LIKE', $endDate.'%')
                            ->orWhere('subscriptions.ends_at', 'LIKE', $endDate.'%');
                    })
                    ->where(function ($query) {
                        $query->where(function ($q) {
                            $q->where('orders.order_status', 'executed')
                                ->where('subscriptions.is_subscribed', 1);
                        })->orWhere(function ($q) {
                            $q->where('subscriptions.rzp_subscription', 1)
                                ->orWhere('subscriptions.autoRenew_status', 1);
                        });
                    })
                    ->get()
                    ->toArray();
                $subscriptions = array_merge($subscriptions, $subscriptionsForDay);
            }

            $uniqueSubscriptions = array_map('unserialize', array_unique(array_map('serialize', $subscriptions)));

            return $uniqueSubscriptions;
        }

        return [];
    }

    public function getCreatedSubscription()
    {
        $daysArray = ExpiryMailDay::pluck('autorenewal_days')->toArray();

        if (empty($daysArray) || empty($daysArray[0])) {
            return [];
        }

        $decodedData = json_decode($daysArray[0]);

        if ($decodedData === null && json_last_error() !== JSON_ERROR_NONE) {
            return [];
        }

        $subscriptions = [];
        foreach ($decodedData as $day) {
            $day = (int) $day;
            $startDate = Carbon::now()->toDateString();
            $endDate = Carbon::now()->addDays($day)->toDateString();

            $subscriptionsForDay = Subscription::where(function ($query) use ($endDate) {
                $query->where('update_ends_at', 'LIKE', $endDate.'%')
                    ->orWhere('support_ends_at', 'LIKE', $endDate.'%')
                    ->orWhere('ends_at', 'LIKE', $endDate.'%');
            })
                ->where(function ($query) {
                    $query->where('is_subscribed', 2)
                        ->orWhere('autoRenew_status', 2);
                })
                ->get()
                ->toArray();

            $subscriptions = array_merge($subscriptions, $subscriptionsForDay);
        }

        $uniqueSubscriptions = array_map('unserialize', array_unique(array_map('serialize', $subscriptions)));

        return $uniqueSubscriptions;

        return [];
    }

    public function autoRenewal()
    {
        ini_set('memory_limit', '-1');
        try {
            $createdSubscription = $this->getCreatedSubscription();
            foreach ($createdSubscription as $sub) {
                $sub = (object) $sub;
                $this->checkSubscriptionStatus($sub);
            }
            //Retrieve expired subscription details
            $subscriptions_detail = $this->getOnDayExpiryInfoSubs();
            foreach ($subscriptions_detail as $subscription) {
                $subscription = (object) $subscription;
                $userid = $subscription->user_id;
                $end = $subscription->update_ends_at;
                $cronController = new CronController();
                $order = $cronController->getOrderById($subscription->order_id);
                $oldinvoice = $cronController->getInvoiceByOrderId($subscription->order_id);
                $item = $cronController->getInvoiceItemByInvoiceId($oldinvoice->id);
                $product_details = Product::where('id', $subscription->product_id)->first();
                $payment_method = $subscription->autoRenew_status != '0' ? 'stripe' : ($subscription->rzp_subscription != '0' ? 'razorpay' : null);

                $plan = Plan::where('id', $subscription->plan_id)->first('days');

                $user = \DB::table('users')->where('id', $userid)->first();
                $oldcurrency = getCurrencyForClient($user->country);
                $countryId = Country::where('country_code_char2', $user->country)->value('country_id');
                $stripe_payment_details = Auto_renewal::where('user_id', $userid)->where('order_id', $subscription->order_id)->where('payment_method', 'stripe')->latest()->first(['customer_id', 'payment_intent_id']);
                $planid = Plan::where('product', $product_details->id)->value('id');

//                $subscription = Subscription::where('id', $subscription->id)->first();
                $productType = Product::find($subscription->product_id);
                $countryids = \App\Model\Common\Country::where('country_code_char2', $user->country)->first();
                $currency = getCurrencyForClient($user->country);
                $country = $countryids->country_id;

                $priceRow = PlanPrice::where('plan_id', $subscription->plan_id)
                    ->where('currency', $currency)
                    ->where('country_id', $country)
                    ->first();

                if (empty($priceRow)) {
                    $priceRow = PlanPrice::where('plan_id', $subscription->plan_id)
                        ->where('currency', $currency)
                        ->where('country_id', 0)
                        ->first();
                }

                $price = $priceRow->renew_price ?? 0;

                if(in_array($subscription->product_id, cloudPopupProducts()) || $product_details->can_modify_agent){
                    $noOfAgents = $priceRow->no_of_agents;
                    $priceForAgents = $price/$noOfAgents;
                    $cost = $this->getPriceforCloud($order, $priceForAgents);
                }
                else{
                    $cost = $price;
                }

                // add processing fee for stripe payment
                if ($payment_method == 'stripe') {
                    $processingFee = \DB::table(strtolower('stripe'))->where('currencies', $currency)->value('processing_fee');
                    $processingFee = (float) $processingFee / 100;
                    $price = $cost + ($cost * $processingFee);
                }

                if ($this->shouldCancelSubscription($product_details, $price)) {
                    Subscription::where('id', $subscription->id)->update(['is_subscribed' => 0]);
                }

                //Do not create invoices for invoices that are already unpaid
                if ($subscription->autoRenew_status != '2' || $subscription->rzp_subscription != '2' && $price > 0) {
                    $findInvoiceid = \DB::table('order_invoice_relations')
                                        ->where('order_id', $subscription->order_id)
                                        ->latest()
                                        ->value('invoice_id');

                    $existingInvoiceItem = \DB::table('invoice_items')
                                              ->where('invoice_id', $findInvoiceid)
                                              ->where('product_id', $subscription->product_id)
                                              ->first();

                    if ($existingInvoiceItem) {
                        $isUnpaid = Invoice::where('id', $existingInvoiceItem->invoice_id)
                                           ->where('status', 'pending')
                                           ->where('is_renewed', '1')  // Corrected the typo from 'is_renewded' to 'is_renewed'
                                           ->latest('created_at')
                                           ->first();
                    } else {
                        $isUnpaid = null;
                    }

                    if (! $isUnpaid) {
                        $renewController = new BaseRenewController();
                        $invoice = $renewController->generateInvoice($product_details, $user, $order->id, $plan->id, $cost, $code = '', $item->agents, $oldcurrency);
                    } else {
                        $invoice = InvoiceItem::where('invoice_id', $isUnpaid->id)
                                              ->latest('created_at')
                                              ->first();
                    }

                    $cost = Invoice::where('id', $invoice->invoice_id)->value('grand_total');
                    $currency = Invoice::where('id', $invoice->invoice_id)->value('currency');
                }
                $unit_cost = $this->PostSubscriptionHandle->calculateUnitCost($currency, $cost);

                if ($price > 0) {
                    //Check the invoice status for active subscription
                    $this->validateInvoiceForActiveSubscriptionStatus($subscription, $currency, $cost, $user, $order, $product_details);

                    //Create subscription status enabled users
                    $this->createSubscriptionsForEnabledUsers($stripe_payment_details, $product_details, $unit_cost, $currency, $plan, $subscription, $invoice, $order, $user, $cost, $end);
                }
            }
        } catch (\Exception $ex) {
            $this->PostSubscriptionHandle->sendFailedPayment($cost, $ex->getMessage(), $user, $order->number, $end, $currency, $order, $product_details, $invoice, $payment_method);
        }
    }

    private function shouldCancelSubscription($productDetails, $price)
    {
        return ($productDetails->type == '4' && $price == '0') || ($price == '0' && $productDetails->type != '4');
    }

    public function mailSendToActiveStripeSubscription($subscription, $product_details, $unit_cost, $currency, $plan, $url, $user)
    {
        $contact = getContactData();
        //check in the settings
        $settings = new \App\Model\Common\Setting();
        $setting = $settings::find(1);

        $mail = new \App\Http\Controllers\Common\PhpMailController();
        //template
        $templates = new \App\Model\Common\Template();
        $temp_id = TemplateType::where('name', 'stripe_subscription_authentication')->value('id');

        $template = $templates->where('type', $temp_id)->first();
        $today = new DateTime();
        $today->modify('+1 day');
        $expiry_date = $today->format('d M Y');
        $update_ends_at = new DateTime($subscription->update_ends_at);
        $date = $update_ends_at->format('d M Y');
        $order = Order::where('id', $subscription->order_id)->first();

        $replace = [
            'name' => ucfirst($user->first_name).' '.ucfirst($user->last_name),
            'product' => $product_details->name,
            'total' => currencyFormat($unit_cost, $code = $currency),
            'contact' => $contact['contact'],
            'logo' => $contact['logo'],
            'expiry_date' => $expiry_date,
            'reply_email' => $setting->company_email,
            'application_title' => $setting->title,
            'company_title' => $setting->company,
            'url' => $url,
            'number' => $order->number,
            'date' => $date,
        ];

        $type = '';
        if ($template) {
            $type_id = $template->type;
            $temp_type = new \App\Model\Common\TemplateType();
            $type = $temp_type->where('id', $type_id)->first()->name;
        }
        $mail->SendEmail($setting->email, $user->email, $template->data, $template->name, $replace, $type);
    }

    public function checkSubscriptionStatus($subscription)
    {
        try {
            if ($subscription) {
                $product_name = Product::where('id', $subscription->product_id)->value('name');
                $invoiceid = \DB::table('order_invoice_relations')->where('order_id', $subscription->order_id)->latest()->value('invoice_id');
                $invoiceItem = \DB::table('invoice_items')->where('invoice_id', $invoiceid)->where('product_id', $subscription->product_id)->first();
                $invoice = Invoice::where('id', $invoiceItem->invoice_id)->where('status', 'pending')->first();
                if ($invoice) {
                    $order = Order::where('id', $subscription->order_id)->first();
                    $cost = $invoice->grand_total;
                    $user = User::find($subscription->user_id);
                    //Check Razorpay subscription status
                    if ($subscription->subscribe_id && $subscription->rzp_subscription == '2') {
                        $key_id = ApiKey::pluck('rzp_key')->first();
                        $secret = ApiKey::pluck('rzp_secret')->first();
                        $api = new Api($key_id, $secret);
                        $subscriptionStatus = $api->subscription->fetch($subscription->subscribe_id);

                        //check subscription status based on that do the need full
                        match ($subscriptionStatus->status) {
                            'authenticated' => $this->handleAuthenticatedSubscriptionforRzp($subscription, $invoiceItem, $invoice, $cost, $user, $order, $product_name),
                            'expired' => $this->handleExpiredSubscriptionforRzp($subscription),
                            default => null
                        };
                    } elseif ($subscription->subscribe_id && $subscription->autoRenew_status == '2') {
                        $stripeSecretKey = ApiKey::pluck('stripe_secret')->first();
                        $stripe = new \Stripe\StripeClient($stripeSecretKey);
                        \Stripe\Stripe::setApiKey($stripeSecretKey);
                        $subscriptionStatus = \Stripe\Subscription::retrieve($subscription->subscribe_id);

                        //check subscription status based on that do the need full
                        match ($subscriptionStatus->status) {
                            'active' => $this->handleAuthenticatedSubscriptionforStripe($subscription, $invoiceItem, $invoice, $cost, $user, $order, $product_name),
                            'incomplete_expired' => $this->handleExpiredSubscriptionforStripe($subscription),
                            default => null
                        };
                    }
                }
            }
        } catch (\Exception $ex) {
            echo $ex->getMessage();
        }
    }

    public function handleAuthenticatedSubscriptionforRzp($subscription, $invoiceItem, $invoice, $cost, $user, $order, $product_name)
    {
        $subscription = Subscription::find($subscription->id);
        $subscription->rzp_subscription = '3';
        $subscription->save();

        if ($invoice) {
            $sub = $this->PostSubscriptionHandle->successRenew($invoiceItem, $subscription, $payment_method = 'Razorpay', $invoice->currency);
            $this->PostSubscriptionHandle->postRazorpayPayment($invoiceItem, $payment_method = 'Razorpay');
            $this->PostSubscriptionHandle->sendPaymentSuccessMail($sub, $invoice->currency, $cost, $user, $product_name, $order->number);
            $this->PostSubscriptionHandle->PaymentSuccessMailtoAdmin($invoice, $cost, $user, $product_name, $template = null, $order, $payment = 'razorpay');
        }
    }

    public function handleExpiredSubscriptionforRzp($subscription)
    {
        Subscription::where('id', $subscription->id)->update(['rzp_subscription' => '1', 'subscribe_id' => '']);
    }

    public function handleAuthenticatedSubscriptionforStripe($subscription, $invoiceItem, $invoice, $cost, $user, $order, $product_name)
    {
        Subscription::where('id', $subscription->id)->update(['autoRenew_status' => '3']);

        if ($invoice) {
            $sub = $this->PostSubscriptionHandle->successRenew($invoiceItem, $subscription, $payment_method = 'Razorpay', $invoice->currency);
            $this->PostSubscriptionHandle->postRazorpayPayment($invoiceItem, $payment_method = 'Razorpay');
            $this->PostSubscriptionHandle->sendPaymentSuccessMail($sub, $invoice->currency, $cost, $user, $product_name, $order->number);
            $this->PostSubscriptionHandle->PaymentSuccessMailtoAdmin($invoice, $cost, $user, $product_name, $template = null, $order, $payment = 'razorpay');
        }
    }

    public function handleExpiredSubscriptionforStripe($subscription)
    {
        Subscription::where('id', $subscription->id)->update(['autoRenew_status' => '1', 'subscribe_id' => '']);
    }

    public function getPriceforCloud($order, $price)
    {
        $numberofAgents = (int) ltrim(substr($order->serial_key, -4), '0');
        $finalPrice = $numberofAgents * $price;

        return $finalPrice;
    }

    private function validateInvoiceForActiveSubscriptionStatus($subscription, $currency, $cost, $user, $order, $product_details)
    {
        if ($subscription->is_subscribed != '1') {
            return;
        }

        if ($subscription->is_subscribed == '1' && $subscription->autoRenew_status == '3') {
            $this->processStripeSubscription($subscription, $currency, $cost, $user, $order, $product_details);
        } elseif ($subscription->is_subscribed == '1' && $subscription->rzp_subscription == '3') {
            $this->processRazorpaySubscription($subscription, $currency, $cost, $user, $order, $product_details);
        }
    }

    private function processStripeSubscription($subscription, $currency, $cost, $user, $order, $product_details)
    {
        $product_name = Product::where('id', $subscription->product_id)->value('name');
        $invoiceid = \DB::table('order_invoice_relations')->where('order_id', $subscription->order_id)->latest()->value('invoice_id');
        $invoiceItem = \DB::table('invoice_items')->where('invoice_id', $invoiceid)->where('product_id', $subscription->product_id)->first();
        $invoice = Invoice::where('id', $invoiceItem->invoice_id)->where('status', 'pending')->first();
        $stripeSecretKey = ApiKey::pluck('stripe_secret')->first();
        $stripe = new \Stripe\StripeClient($stripeSecretKey);
        \Stripe\Stripe::setApiKey($stripeSecretKey);

        $subscriptionStatus = \Stripe\Subscription::retrieve($subscription->subscribe_id);
        $latestInvoiceId = $subscriptionStatus->latest_invoice;
        $latestInvoice = \Stripe\Invoice::retrieve($latestInvoiceId);

        if ($latestInvoice->status != 'paid') {
            return;
        }

        $createdDate = Carbon::createFromTimestamp($latestInvoice->created)->startOfDay();
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();

        if (! $createdDate->eq($today)) {
            return;
        }
        $invoiceCost = $this->calculateReverseUnitCost($currency, $latestInvoice->amount);
        $cost = $cost == intval($invoiceCost) ? $cost : intval($invoiceCost);
        Invoice::where('id', $invoiceItem->invoice_id)->where('status', 'pending')->update(['grand_total' => $cost]);
        \DB::table('invoice_items')->where('invoice_id', $invoiceid)->where('product_id', $subscription->product_id)->update(['regular_price' => $cost]);
        // Refresh the invoice and invoice item instances
        $invoice = Invoice::find($invoiceItem->invoice_id);
        $invoiceItem = InvoiceItem::where('invoice_id', $invoiceid)->where('product_id', $subscription->product_id)->first();
        $this->PostSubscriptionHandle->successRenew($invoiceItem, $subscription, 'Razorpay', $invoice->currency);
        $sub = $this->PostSubscriptionHandle->successRenew($invoice, $subscription, 'stripe', $currency);
        $this->PostSubscriptionHandle->postRazorpayPayment($invoice, 'stripe');

        if ($cost && emailSendingStatus()) {
            $this->PostSubscriptionHandle->sendPaymentSuccessMail($sub, $currency, $cost, $user, $product_details->name, $order->number);
            $this->PostSubscriptionHandle->PaymentSuccessMailtoAdmin($invoice, $cost, $user, $product_details->name, null, $order, 'stripe');
        }
    }

    private function processRazorpaySubscription($subscription, $currency, $cost, $user, $order, $product_details)
    {
        $key_id = ApiKey::pluck('rzp_key')->first();
        $secret = ApiKey::pluck('rzp_secret')->first();
        $api = new Api($key_id, $secret);
        $subscriptionStatus = $api->subscription->fetch($subscription->subscribe_id);

        if ($subscriptionStatus->status != 'active') {
            return;
        }
        $invoices = $api->invoice->all(['subscription_id' => $subscriptionStatus['id']]);

        $recentInvoice = null;
        $today = date('Y-m-d');
        $yesterday = Carbon::yesterday()->format('Y-m-d');
        $product_name = Product::where('id', $subscription->product_id)->value('name');
        $invoiceid = \DB::table('order_invoice_relations')->where('order_id', $subscription->order_id)->latest()->value('invoice_id');
        $invoiceItem = \DB::table('invoice_items')->where('invoice_id', $invoiceid)->where('product_id', $subscription->product_id)->first();
        $invoice = Invoice::where('id', $invoiceItem->invoice_id)->where('status', 'pending')->first();
        $order = Order::where('id', $subscription->order_id)->first();

        foreach ($invoices->items as $invoices) {
            if ($invoices->status === 'paid' && date('Y-m-d', $invoices->paid_at) === $today) {
                $recentInvoice = $invoices;
                break;
            }
        }

        if ($recentInvoice) {
            $invoiceCost = $this->calculateReverseUnitCost($currency, $invoices->amount);
            $cost = $cost == intval($invoiceCost) ? $cost : intval($invoiceCost);
            Invoice::where('id', $invoiceItem->invoice_id)->where('status', 'pending')->update(['grand_total' => $cost]);
            \DB::table('invoice_items')->where('invoice_id', $invoiceid)->where('product_id', $subscription->product_id)->update(['regular_price' => $cost]);
            // Refresh the invoice and invoice item instances
            $invoice = Invoice::find($invoiceItem->invoice_id);
            $invoiceItem = InvoiceItem::where('invoice_id', $invoiceid)->where('product_id', $subscription->product_id)->first();
            $this->PostSubscriptionHandle->successRenew($invoiceItem, $subscription, 'Razorpay', $invoice->currency);
            $this->PostSubscriptionHandle->postRazorpayPayment($invoiceItem, 'Razorpay');
            $this->PostSubscriptionHandle->sendPaymentSuccessMail($subscription->id, $currency, $cost, $user, $product_name, $order->number);
            $this->PostSubscriptionHandle->PaymentSuccessMailtoAdmin($invoice, $cost, $user, $product_name, null, $order, 'razorpay');
        }
    }

    public function createSubscriptionsForEnabledUsers($stripe_payment_details, $product_details, $unit_cost, $currency, $plan, $subscription, $invoice, $order, $user, $cost, $end)
    {
        if ($subscription->is_subscribed == '1') {
            if ($subscription->autoRenew_status == '1') {
                $this->handleStripeSubscription($stripe_payment_details, $product_details, $unit_cost, $currency, $plan, $subscription, $invoice, $order, $user, $cost);
            } elseif ($subscription->rzp_subscription == '1') {
                $this->handleRazorpaySubscription($unit_cost, $plan, $product_details, $invoice, $currency, $subscription, $user, $order, $end);
            }
        }
    }

    private function handleStripeSubscription($stripe_payment_details, $product_details, $unit_cost, $currency, $plan, $subscription, $invoice, $order, $user, $cost)
    {
        $stripeController = new SettingsController();
        $stripeResponse = $stripeController->handleStripeAutoPay($stripe_payment_details, $product_details, $unit_cost, $currency, $plan);

        if ($stripeResponse->status == 'active') {
            $this->updateSubscriptionAndSendEmails($stripeResponse, $invoice, $subscription, $cost, $user, $product_details, $order, 'stripe', $currency);
        } elseif ($stripeResponse->status == 'incomplete') {
            $this->handleIncompleteStripePayment($stripeResponse, $invoice, $subscription, $currency, $plan, $product_details, $cost, $user);
        }
    }

    private function handleIncompleteStripePayment($stripeResponse, $invoice, $subscription, $currency, $plan, $product_details, $cost, $user)
    {
        $latestInvoiceId = $stripeResponse->latest_invoice;
        $latestInvoice = \Stripe\Invoice::retrieve($latestInvoiceId);
        $url = $latestInvoice->hosted_invoice_url;

        if ($url) {
            $this->mailSendToActiveStripeSubscription($subscription, $product_details, $cost, $currency, $plan, $url, $user);
            Subscription::where('id', $subscription->id)->update(['subscribe_id' => $stripeResponse->id, 'autoRenew_status' => '2']);
        }
    }

    private function handleRazorpaySubscription($unit_cost, $plan, $product_details, $invoice, $currency, $subscription, $user, $order, $end)
    {
        $razorpayController = new RazorpayController();
        $rzpResponse = $razorpayController->handleRzpAutoPay($unit_cost, $plan->days, $product_details->name, $invoice, $currency, $subscription, $user, $order, $end, $product_details);

        if ($rzpResponse->status == 'created') {
            $cost = $this->calculateReverseUnitCost($currency, $unit_cost);
            $this->mailSendToActiveStripeSubscription($subscription, $product_details, $cost, $currency, $plan, $rzpResponse->short_url, $user);
            Subscription::where('id', $subscription->id)->update(['subscribe_id' => $rzpResponse->id, 'rzp_subscription' => '2']);
        }
    }

    private function updateSubscriptionAndSendEmails($response, $invoice, $subscription, $cost, $user, $product_details, $order, $payment_method, $currency)
    {
        Subscription::where('id', $subscription->id)->update(['subscribe_id' => $response->id, 'autoRenew_status' => '3']);
        $sub = $this->PostSubscriptionHandle->successRenew($invoice, $subscription, $payment_method, $currency);
        $this->PostSubscriptionHandle->postRazorpayPayment($invoice, $payment_method);

        if ($cost && emailSendingStatus()) {
            $this->PostSubscriptionHandle->sendPaymentSuccessMail($sub, $currency, $cost, $user, $product_details->name, $order->number);
            $this->PostSubscriptionHandle->PaymentSuccessMailtoAdmin($invoice, $cost, $user, $product_details->name, $template = null, $order, $payment_method);
        }
    }

    public function calculateReverseUnitCost($currency, $cost)
    {
        $decimalPlaces = [
            'BIF' => 0, 'CLP' => 0, 'DJF' => 0, 'GNF' => 0, 'JPY' => 0,
            'KMF' => 0, 'KRW' => 0, 'MGA' => 0, 'PYG' => 0, 'RWF' => 0,
            'UGX' => 0, 'VND' => 0, 'VUV' => 0, 'XAF' => 0, 'XOF' => 0,
            'XPF' => 0, 'BHD' => 3, 'JOD' => 3, 'KWD' => 3, 'OMR' => 3,
            'TND' => 3,
        ];

        $decimalPlacesForCurrency = $decimalPlaces[$currency] ?? 2;

        if ($decimalPlacesForCurrency === 0) {
            $unit_cost = round((int) $cost);
        } elseif ($decimalPlacesForCurrency === 3) {
            $unit_cost = round((int) $cost) / 1000;
        } else {
            $unit_cost = round((int) $cost) / 100;
        }

        return $unit_cost;
    }
}
