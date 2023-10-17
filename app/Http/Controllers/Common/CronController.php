<?php

namespace App\Http\Controllers\Common;

use App\ApiKey;
use App\Auto_renewal;
use App\Http\Controllers\License\LicensePermissionsController;
use App\Http\Controllers\Order\BaseRenewController;
use App\Model\Common\Setting;
use App\Model\Common\StatusSetting;
use App\Model\Common\Template;
use App\Model\Mailjob\ExpiryMailDay;
use App\Model\Order\Invoice;
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

class CronController extends BaseCronController
{
    protected $subscription;

    protected $order;

    protected $user;

    protected $template;

    protected $invoice;

    public function __construct()
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
    }

    public function getExpiredInfoByOrderId($orderid)
    {
        $yesterday = new Carbon('today');
        $sub = $this->sub
                ->where('order_id', $orderid)
                ->where('update_ends_at', '!=', '0000-00-00 00:00:00')
                ->whereNotNull('update_ends_at')
                ->where('update_ends_at', '<', $yesterday)
                ->first();

        return $sub;
    }

    public function getAllDaysExpiryUsers($day)
    {
        $sub = $this->getAllDaysExpiryInfo($day);
        //dd($sub->get());
        $users = [];
        if ($sub->get()->count() > 0) {
            foreach ($sub->get() as $key => $value) {
                $users[$key]['users'] = $this->sub->find($value->id)->user()->get();
                $users[$key]['orders'] = $this->sub->find($value->id)->order()->get();
                $users[$key]['subscription'] = $value;
            }
        }

        return $users;
    }

    public function get15DaysExpiryUsers()
    {
        $sub = $this->get15DaysExpiryInfo();
        $users = [];
        if ($sub->get()->count() > 0) {
            foreach ($sub->get() as $key => $value) {
                $users[$key]['users'] = $this->sub->find($value->id)->user()->get();
                $users[$key]['orders'] = $this->sub->find($value->id)->order()->get();
                $users[$key]['subscription'] = $value;
            }
        }

        return $users;
    }

    public function getOneDayExpiryUsers()
    {
        $sub = $this->getOneDayExpiryInfo();
        $users = [];
        if ($sub->get()->count() > 0) {
            foreach ($sub->get() as $key => $value) {
                $users[$key]['users'] = $this->sub->find($value->id)->user()->get();
                $users[$key]['orders'] = $this->sub->find($value->id)->order()->get();
                $users[$key]['subscription'] = $value;
            }
        }

        return $users;
    }

    public function getOnDayExpiryUsers()
    {
        $sub = $this->getOnDayExpiryInfo();
        $users = [];
        if ($sub->get()->count() > 0) {
            foreach ($sub->get() as $key => $value) {
                $users[$key]['users'] = $this->sub->find($value->id)->user()->get();
                $users[$key]['orders'] = $this->sub->find($value->id)->order()->get();
                $users[$key]['subscription'] = $value;
            }
        }

        return $users;
    }

    public function getExpiredUsers()
    {
        $sub = $this->getExpiredInfo();
        $users = [];
        if ($sub->get()->count() > 0) {
            foreach ($sub->get() as $key => $value) {
                $users[$key]['users'] = $this->sub->find($value->id)->user()->get();
                $users[$key]['orders'] = $this->sub->find($value->id)->order()->get();
                $users[$key]['subscription'] = $value;
            }
        }

        return $users;
    }

    public function get30DaysOrders()
    {
        $users = [];
        $users = $this->get30DaysExpiryUsers();
        if (count($users) > 0) {
            return $users[0]['orders'];
        }

        return $users;
    }

    public function get15DaysOrders()
    {
        $users = [];
        $users = $this->get15DaysExpiryUsers();
        if (count($users) > 0) {
            return $users[0]['orders'];
        }

        return $users;
    }

    public function get1DaysOrders()
    {
        $users = [];
        $users = $this->getOneDayExpiryUsers();
        if (count($users) > 0) {
            return $users[0]['orders'];
        }

        return $users;
    }

    public function get0DaysOrders()
    {
        $users = [];
        $users = $this->getOnDayExpiryUsers();
        if (count($users) > 0) {
            return $users[0]['orders'];
        }

        return $users;
    }

    public function getPlus1Orders()
    {
        $users = [];
        $users = $this->getExpiredUsers();
        if (count($users) > 0) {
            return $users[0]['orders'];
        }

        return $users;
    }

    public function get15DaysSubscription()
    {
        $users = [];
        $users = $this->get15DaysExpiryUsers();
        if (count($users) > 0) {
            return $users[0]['subscription'];
        }

        return $users;
    }

    public function get1DaysSubscription()
    {
        $users = [];
        $users = $this->getOneDayExpiryUsers();
        if (count($users) > 0) {
            return $users[0]['subscription'];
        }

        return $users;
    }

    public function get0DaysSubscription()
    {
        $users = [];
        $users = $this->getOnDayExpiryUsers();
        if (count($users) > 0) {
            return $users[0]['subscription'];
        }

        return $users;
    }

    public function getPlus1Subscription()
    {
        $users = [];
        $users = $this->getExpiredUsers();
        if (count($users) > 0) {
            return $users[0]['subscription'];
        }

        return $users;
    }

    public function getUsers()
    {
        $users = [];
        if (count($this->get30DaysUsers())) {
            array_push($users, $this->get30DaysUsers());
        }
        if (count($this->get15DaysUsers())) {
            array_push($users, $this->get15DaysUsers());
        }
        if (count($this->get1DaysUsers())) {
            array_push($users, $this->get1DaysUsers());
        }
        if (count($this->get0DaysUsers())) {
            array_push($users, $this->get0DaysUsers());
        }
        if (count($this->getPlus1Users())) {
            array_push($users, $this->getPlus1Users());
        }

        return $users;
    }

    public function getSubscriptions($days)
    {
        $decodedData = json_decode($days[0]);

        if ($decodedData === null && json_last_error() !== JSON_ERROR_NONE) {
            return [];
        }

        $subscriptions = [];
        foreach ($decodedData as $day) {
            $day = (int) $day;

            // Calculate the start and end dates
            $startDate = Carbon::now()->toDateString();
            $endDate = Carbon::now()->addDays($day);

            $subscriptionsForDay = Subscription::whereBetween('update_ends_at', [$startDate, $endDate])
                ->where('is_subscribed', '0')
                ->get()
                ->toArray(); // Convert the collection to an array

            $subscriptions = array_merge($subscriptions, $subscriptionsForDay);
        }

        $uniqueSubscriptions = array_map('unserialize', array_unique(array_map('serialize', $subscriptions)));

        return $uniqueSubscriptions;
    }

    public function getautoSubscriptions($days)
    {
        $decodedData = json_decode($days[0]);

        if ($decodedData === null && json_last_error() !== JSON_ERROR_NONE) {
            return [];
        }

        $subscriptions = [];
        foreach ($decodedData as $day) {
            $day = (int) $day;

            // Calculate the start and end dates
            $startDate = Carbon::now()->toDateString();
            $endDate = Carbon::now()->addDays($day);

            $subscriptionsForDay = Subscription::whereBetween('update_ends_at', [$startDate, $endDate])
                ->where('is_subscribed', '1')
                ->get()
                ->toArray(); // Convert the collection to an array

            $subscriptions = array_merge($subscriptions, $subscriptionsForDay);
        }

        $uniqueSubscriptions = array_map('unserialize', array_unique(array_map('serialize', $subscriptions)));

        return $uniqueSubscriptions;
    }

    public function getPostSubscriptions($days)
    {
        $decodedData = json_decode($days[0]);

        if ($decodedData === null && json_last_error() !== JSON_ERROR_NONE) {
            return [];
        }

        $subscriptions = [];

        // Calculate the end date as today
        $endDate = Carbon::now()->toDateString();

        foreach ($decodedData as $day) {
            $day = (int) $day;

            // Calculate the start date based on the specific day value from $decodedData
            $startDate = Carbon::now()->subDays($day)->toDateString(); // Use $day here

            $subscriptionsForDay = Subscription::whereBetween('update_ends_at', [$startDate, $endDate])
                ->get()
                ->toArray();

            $subscriptions = array_merge($subscriptions, $subscriptionsForDay);
        }

        $uniqueSubscriptions = array_map('unserialize', array_unique(array_map('serialize', $subscriptions)));

        return $uniqueSubscriptions;
    }

    public function eachSubscription()
    {
        $status = StatusSetting::value('expiry_mail');
        if ($status == 1) {
            $allDays = ExpiryMailDay::pluck('days')->toArray();
            $sub = $this->getSubscriptions($allDays);
            foreach ($sub as $value) {
                $value = (object) $value;
                $userid = $value->user_id;
                $user = $this->getUserById($userid);
                $end = $value->update_ends_at;
                $order = $this->getOrderById($value->order_id);
                $invoice = $this->getInvoiceByOrderId($value->order_id);
                $item = $this->getInvoiceItemByInvoiceId($invoice->id);
                $product = $item->product_name;
                if (emailSendingStatus()) {
                    $this->mail($user, $end, $product, $order, $value->id);
                }
            }
        }
    }

    public function autoRenewalExpiryNotify()
    {
        $status = StatusSetting::value('subs_expirymail');
        if ($status == 1) {
            $Days = ExpiryMailDay::pluck('autorenewal_days')->toArray();
            $cron = new AutorenewalCronController();
            $Autosub = $this->getautoSubscriptions($Days);
            foreach ($Autosub as $value) {
                $value = (object) $value;
                $userid = $value->user_id;
                $user = $this->getUserById($userid);
                $end = $value->update_ends_at;
                $order = $this->getOrderById($value->order_id);
                $invoice = $this->getInvoiceByOrderId($value->order_id);
                $item = $this->getInvoiceItemByInvoiceId($invoice->id);
                $product = $item->product_name;
                if (emailSendingStatus()) {
                    $this->Auto_renewalMail($user, $end, $product, $order, $value->id);
                }
            }
        }
    }

    public function postRenewalNotify()
    {
        $status = StatusSetting::value('post_expirymail');
        if ($status == 1) {
            $periods = ExpiryMailDay::pluck('postexpiry_days')->toArray();
            $cron = new AutorenewalCronController();
            $postSub = $this->getPostSubscriptions($periods);
            foreach ($postSub as $value) {
                $value = (object) $value;
                $userid = $value->user_id;
                $user = $this->getUserById($userid);
                $end = $value->update_ends_at;
                $order = \App\Model\Order\Order::find($value->order_id);
                if ($order && $order->order_status == 'executed') {
                    $order = $this->getOrderById($value->order_id);
                    $invoice = $this->getInvoiceByOrderId($value->order_id);
                    $item = $this->getInvoiceItemByInvoiceId($invoice->id);
                    $product = $item->product_name;
                    if (emailSendingStatus()) {
                        $this->Expiredsub_Mail($user, $end, $product, $order, $value->id);
                    }
                }
            }
        }
    }

    public function getOnDayExpiryInfoSubs()
    {
        $yesterday = new Carbon('yesterday');
        $tomorrow = new Carbon('tomorrow');
        $daybefore = new Carbon('-2 days');
        $today = new Carbon('today');
        $sub = Subscription::whereNotNull('update_ends_at')
            ->where('is_subscribed', 1)
            ->whereBetween('update_ends_at', [$yesterday, $tomorrow])
            ->orwhereBetween('support_ends_at', [$yesterday, $tomorrow])
            ->orwhereBetween('update_ends_at', [$daybefore, $today]);

        return $sub;
    }

    public function autoRenewal()
    {
        ini_set('memory_limit', '-1');
        try {
            $subscriptions_detail = $this->getOnDayExpiryInfoSubs()->get();
            foreach ($subscriptions_detail as $subscription) {
                $userid = $subscription->user_id;
                $end = $subscription->update_ends_at;
                $order = $this->getOrderById($subscription->order_id);
                $oldinvoice = $this->getInvoiceByOrderId($subscription->order_id);
                $item = $this->getInvoiceItemByInvoiceId($oldinvoice->id);
                // $product = $item->product_name;
                $product_details = Product::where('name', $item->product_name)->first();

                $plan = Plan::where('product', $product_details->id)->first('days');
                $oldcurrency = $oldinvoice->currency;

                $stripeSecretKey = ApiKey::pluck('stripe_secret')->first();
                $stripe = new \Stripe\StripeClient($stripeSecretKey);

                $user = \DB::table('users')->where('id', $userid)->first();
                $customer_id = Auto_renewal::where('user_id', $userid)->latest()->value('customer_id');
                $planid = Plan::where('product', $product_details->id)->value('id');
                $cost = ($product_details->type == '4') ? $oldinvoice->grand_total : PlanPrice::where('plan_id', $planid)->where('currency', $oldcurrency)->value('renew_price');
                //razorpay sunscription status

                $subscriptionId = Subscription::where('id', $subscription->id)->value('subscribe_id');
                $rzp_sta = Subscription::where('id', $subscription->id)->value('rzp_subscription');

                if ($subscriptionId != null) {
                    $authenticatesubs = $this->authenticatesubs($subscriptionId, $subscription, $cost, $user);
                    if ($rzp_sta != '0') {
                        $activeSubs = $this->activeSubs($subscriptionId, $subscription);
                        $key_id = ApiKey::pluck('rzp_key')->first();
                        $secret = ApiKey::pluck('rzp_secret')->first();
                        $api = new Api($key_id, $secret);

                        $invoiceCount = $api->invoice->all(['subscription_id'=> $subscriptionId]);
                        if ($invoiceCount['count'] > 99) {
                            $updateCount = $api->subscription->fetch($subscriptionId)->update(['remaining_count' => 100]);
                        }
                    }
                }
                $subscription = $subscription->refresh();
                $productType = Product::find($subscription->product_id);
                $price = PlanPrice::where('plan_id', $subscription->plan_id)->value('renew_price');
                if ($productType->type == '4' && $price == '0') {
                    Subscription::where('id', $subscription->id)->update(['is_subscribed' => 0]);
                }
                $subscription = $subscription->refresh();
                $status = $subscription->is_subscribed;
                if ($status == '1' && $subscription->rzp_subscription == '0') {
                    //create invoice
                    $renewController = new BaseRenewController();
                    $invoice = $renewController->generateInvoice($product_details, $user, $order->id, $plan->id, $cost, $code = '', $item->agents, $oldcurrency);
                    $cost = Invoice::where('id', $invoice->invoice_id)->value('grand_total');
                    $currency = Invoice::where('id', $invoice->invoice_id)->value('currency');
                    $zero_decimalCurrency = ['BIF', 'CLP', 'DJF', 'GNF', 'JPY', 'KMF', 'KRW', 'MGA', 'PYG', 'RWF', 'UGX', 'VND', 'VUV', 'XAF', 'XOF', 'XPF'];
                    $three_decimalCurrency = ['BHD', 'JOD', 'KWD', 'OMR', 'TND'];
                    if (in_array($currency, $three_decimalCurrency)) {
                        $unit_cost = round((int) $cost + 1) * 1000;
                    } elseif (in_array($currency, $zero_decimalCurrency)) {
                        $unit_cost = round((int) $cost + 1);
                    } else {
                        $unit_cost = round((int) $cost + 1) * 100;
                    }

                    //create product
                    $product = $stripe->products->create([
                        'name' => $product_details->name,
                    ]);
                    $product_id = $product['id'];

                    //define product price and recurring interval

                    $price = $stripe->prices->create([
                        'unit_amount' => $unit_cost,
                        'currency' => $currency,
                        'recurring' => ['interval' => 'day', 'interval_count' => $plan->days],
                        'product' => $product_id,
                    ]);
                    $price_id = $price['id'];

                    //CREATE SUBSCRIPTION

                    $stripe_subscription = $stripe->subscriptions->create([
                        'customer' => $customer_id,
                        'items' => [
                            ['price' => $price_id],
                        ],
                    ]);
                    if ($stripe_subscription['status'] == 'active') {
                        //Afer Renew
                        Subscription::where('id', $subscription->id)->update(['subscribe_id' => $stripe_subscription['id'], 'autoRenew_status' => 'Success']);
                        $sub = $this->successRenew($invoice, $subscription, $payment_method = 'stripe', $currency);
                        $this->postRazorpayPayment($invoice, $payment_method = 'stripe');
                        if ($cost && emailSendingStatus()) {
                            $this->sendPaymentSuccessMail($sub, $currency, $cost, $user, $invoice->product_name, $order->number);
                            $this->PaymentSuccessMailtoAdmin($invoice, $cost, $user, $invoice->product_name, $template = null, $order, $payment = 'stripe');
                        }
                    }
                }
            }
        } catch (\Cartalyst\Stripe\Exception\ApiLimitExceededException|\Cartalyst\Stripe\Exception\BadRequestException|\Cartalyst\Stripe\Exception\MissingParameterException|\Cartalyst\Stripe\Exception\NotFoundException|\Cartalyst\Stripe\Exception\ServerErrorException|\Cartalyst\Stripe\Exception\StripeException|\Cartalyst\Stripe\Exception\UnauthorizedException $e) {
            $this->sendFailedPayment($cost, $e->getMessage(), $user, $number, $end, $currency, $order, $product_details, $invoice, $payment = 'stripe');
        } catch (\Cartalyst\Stripe\Exception\CardErrorException $e) {
            if (emailSendingStatus()) {
                $this->sendFailedPayment($cost, $e->getMessage(), $user, $order->number, $end, $currency, $order, $product_details, $invoice, $payment = 'stripe');
            }
            \Session::put('amount', $amount);
            \Session::put('error', $e->getMessage());

            return redirect()->route('checkout');
        } catch (\Exception $ex) {
            $subscription = $subscription->refresh();
            $this->razorpay_payment($cost, $plan->days, $product_details->name, $invoice, $currency, $subscription, $user, $order, $end, $product_details);
        }
    }

    public function razorpay_payment($cost, $days, $product_name, $invoice, $currency, $subscription, $user, $order, $end, $product_details)
    {
        try {
            $subscription = $subscription->refresh();
            $status = $subscription->rzp_subscription;
            $today = new DateTime();

            $subscriptionEndDate = new DateTime($subscription->ends_at);
            if ($status == '0') {
                $key_id = ApiKey::pluck('rzp_key')->first();
                $secret = ApiKey::pluck('rzp_secret')->first();
                $amount = $cost;
                // $count = Subscription::where('id', $subscription->id)->value('rzp_subattempts');
                $update_end = $subscription->update_ends_at;
                $api = new Api($key_id, $secret);
                // $paymentId = \DB::table('rzp_payments')->where('user_id', $user->id)->latest()->value('payment_id');
                $rzp_plan = $api->plan->create(['period' => 'monthly',
                    'interval' => round((int) $days / 30),
                    'item' => [
                        'name' => $product_name,
                        'amount' => round((int) $cost) * 100,
                        'currency' => $currency, ],

                ]
                );

                $rzp_subscriptionLink = $api->subscription->create([
                    'plan_id' => $rzp_plan['id'],
                    'total_count' => 100,
                    'quantity' => 1,
                    'expire_by' => Carbon::parse($update_end)->addDays(1)->timestamp,
                    'start_at' =>  Carbon::parse($update_end)->addDays(round((int) $days))->timestamp,

                    'customer_notify' => 1,
                    'addons' => [['item'=>[
                        'name' => $product_name,
                        'amount' => round((int) $cost) * 100,
                        'currency' => $currency]]],
                    'notify_info'=>[
                        'notify_phone' => $user->mobile,
                        'notify_email'=> $user->email,
                    ]]);
                Subscription::where('id', $subscription->id)->update(['subscribe_id' => $rzp_subscriptionLink['id'], 'autoRenew_status' => 'Pending']);
            }
        } catch (\Razorpay\Api\Errors\SignatureVerificationError|\Razorpay\Api\Errors\BadRequestError|\Razorpay\Api\Errors\GatewayError|\Razorpay\Api\Errors\ServerError $e) {
            $this->sendFailedPayment($cost, $e->getMessage(), $user, $order->number, $end, $currency, $order, $product_details, $invoice, $stripe = 'razorpay');
        } catch (\Exception $e) {
            if (emailSendingStatus()) {
                $this->sendFailedPayment($cost, $e->getMessage(), $user, $order->number, $end, $currency, $order, $product_details, $invoice, $payment = 'razorpay');
            }
        }
    }

    public function authenticatesubs($subId, $subscription, $cost, $user)
    {
        try {
            $key_id = ApiKey::pluck('rzp_key')->first();
            $secret = ApiKey::pluck('rzp_secret')->first();
            $api = new Api($key_id, $secret);
            if ($subscription->autoRenew_status == 'Pending') {
                $subscriptionStatus = $api->subscription->fetch($subId);
                if ($subscriptionStatus['status'] == 'authenticated') {
                    // Subscription::where('id', $subscription->id)->update(['subscribe_id' => $subId, 'autoRenew_status' => 'Success', 'rzp_subscription' => '1']);
                    $subscription = Subscription::find($subscription->id);

                    $subscription->subscribe_id = $subId;
                    $subscription->autoRenew_status = 'Success';
                    $subscription->rzp_subscription = '1';
                    $subscription->save();
                    // $subscription = Subscription::find($subscription->id);

                    $product_name = Product::where('id', $subscription->product_id)->value('name');
                    $invoiceid = \DB::table('order_invoice_relations')->where('order_id', $subscription->order_id)->latest()->value('invoice_id');
                    $invoiceItem = \DB::table('invoice_items')->where('invoice_id', $invoiceid)->where('product_name', $product_name)->first();
                    $invoice = Invoice::where('id', $invoiceItem->invoice_id)->where('status', 'pending')->first();
                    if ($invoice) {
                        $this->successRenew($invoiceItem, $subscription, $payment_method = 'Razorpay', $invoice->currency);
                        $this->postRazorpayPayment($invoiceItem, $payment_method = 'Razorpay');
                        $order = Order::where('id', $subscription->order_id)->value('number');
                        $this->PaymentSuccessMailtoAdmin($invoice, $cost, $user, $product_name, $template = null, $order, $payment = 'razorpay');
                    }

                    return $subscription;
                }
            }
        } catch(\Exception $ex) {
            echo $ex->getMessage();
        }
    }

    public function activeSubs($subId, $subscription)
    {
        try {
            $key_id = ApiKey::pluck('rzp_key')->first();
            $secret = ApiKey::pluck('rzp_secret')->first();
            $api = new Api($key_id, $secret);
            $subscriptionStatus = $api->subscription->fetch($subId);
            if ($subscriptionStatus['status'] == 'active') {
                $invoices = $api->invoice->all(['subscription_id' => $subId]);

                // Find the most recent paid invoice
                $recentInvoice = null;

                foreach ($invoices->items as $invoice) {
                    if ($invoice->status === 'paid') {
                        $recentInvoice = $invoice;
                        break;
                    }
                }
                if ($recentInvoice) {
                    $product_details = Product::where('id', $subscription->product_id)->first();
                    $user = \DB::table('users')->where('id', $subscription->user_id)->first();
                    $order = $this->order->find($subscription->order_id);
                    $plan = Plan::where('product', $product_details->id)->first('days');
                    $oldinvoice = $this->getInvoiceByOrderId($subscription->order_id);
                    $item = $this->getInvoiceItemByInvoiceId($oldinvoice->id);
                    $planid = Plan::where('product', $product_details->id)->value('id');
                    $cost = PlanPrice::where('plan_id', $planid)->where('currency', $oldinvoice->currency)->value('renew_price');
                    $renewController = new BaseRenewController();
                    $invoiceItem = $renewController->generateInvoice($product_details, $user, $order->id, $plan->id, $cost, $code = '', $item->agents, $oldinvoice->currency);
                }
                $this->successRenew($invoiceItem, $subscription, $payment_method = 'Razorpay', $oldinvoice->currency);
                $this->postRazorpayPayment($invoiceItem, $payment_method = 'Razorpay');
                $order = Order::where('id', $subscription->order_id)->value('number');
                $this->PaymentSuccessMailtoAdmin($oldinvoice, $cost, $user, $product_details->name, $template = null, $order, $payment = 'razorpay');
            }
        } catch(\Exception $ex) {
            echo $ex->getMessage();
        }
    }

    public function sendFailedPayment($total, $exceptionMessage, $user, $number, $end, $currency, $order, $product_details, $invoice, $payment)
    {
        $contact = getContactData();
        //check in the settings
        $settings = new \App\Model\Common\Setting();
        $setting = $settings::find(1);

        Subscription::where('order_id', $order->id)->update(['autoRenew_status' => 'Failed', 'is_subscribed' => '0']);

        $mail = new \App\Http\Controllers\Common\PhpMailController();
        $mailer = $mail->setMailConfig($setting);
        //template
        $templates = new \App\Model\Common\Template();
        $temp_id = $setting->payment_failed;

        $template = $templates->where('id', $temp_id)->first();
        $url = url("autopaynow/$invoice->invoice_id");
        $type = '';
        $replace = ['name' => ucfirst($user->first_name).' '.ucfirst($user->last_name),
            'product' => $product_details->name,
            'total' => currencyFormat($total, $code = $currency),
            'number' => $number,
            'expiry' => date('d-m-Y', strtotime($end)),
            'exception' => $exceptionMessage,
            'url' => $url,
            'contact' => $contact['contact'],
            'logo' => $contact['logo'], ];
        $type = '';

        if ($template) {
            $type_id = $template->type;
            $temp_type = new \App\Model\Common\TemplateType();
            $type = $temp_type->where('id', $type_id)->first()->name;
        }

        $mail->SendEmail($setting->email, $user->email, $template->data, $template->name, $replace, $type);
        $this->FailedPaymenttoAdmin($invoice, $total, $product_details->name, $exceptionMessage, $user, $template->name, $order, $payment);
    }

    public static function sendPaymentSuccessMail($sub, $currency, $total, $user, $product, $number)
    {
        $future_expiry = Subscription::find($sub);
        $contact = getContactData();
        //check in the settings
        $settings = new \App\Model\Common\Setting();
        $setting = $settings::find(1);

        $mail = new \App\Http\Controllers\Common\PhpMailController();
        //template
        $templates = new \App\Model\Common\Template();
        $temp_id = $setting->payment_successfull;

        $template = $templates->where('id', $temp_id)->first();
        $date = date_create($future_expiry->update_ends_at);
        $end = date_format($date, 'l, F j, Y ');

        $replace = [
            'name' => ucfirst($user->first_name).' '.ucfirst($user->last_name),
            'product' => $product,
            'total' => currencyFormat($total, $code = $currency),
            'number' => $number,
            'contact' => $contact['contact'],
            'logo' => $contact['logo'],
            'future_expiry' => $end,
        ];

        $type = '';
        if ($template) {
            $type_id = $template->type;
            $temp_type = new \App\Model\Common\TemplateType();
            $type = $temp_type->where('id', $type_id)->first()->name;
        }
        $mail->SendEmail($setting->email, $user->email, $template->data, $template->name, $replace, $type);
    }

    public function successRenew($invoice, $subscription, $payment_method, $currency)
    {
        try {
            $processingFee = $this->getProcessingFee($payment_method, $currency);
            // $invoice->processing_fee = $processingFee;
            Invoice::where('id', $invoice->invoice_id)->update(['processing_fee' => $processingFee, 'status' => 'success']);
            // $invoice->status = 'success';
            // $invoice->save();
            $id = $subscription->id;
            $planid = $subscription->plan_id;
            $plan = $this->plan->find($planid);
            $days = $plan->days;
            $sub = $this->sub->find($id);
            $permissions = LicensePermissionsController::getPermissionsForProduct($sub->product_id);
            $licenseExpiry = $this->getExpiryDate($permissions['generateLicenseExpiryDate'], $sub, $days);
            $updatesExpiry = $this->getUpdatesExpiryDate($permissions['generateUpdatesxpiryDate'], $sub, $days);
            $supportExpiry = $this->getSupportExpiryDate($permissions['generateSupportExpiryDate'], $sub, $days);
            $sub->ends_at = $licenseExpiry;
            $sub->update_ends_at = $updatesExpiry;
            $sub->support_ends_at = $supportExpiry;
            $sub->autoRenew_status = 'Success';
            $sub->save();
            if (Order::where('id', $sub->order_id)->value('license_mode') == 'File') {
                Order::where('id', $sub->order_id)->update(['is_downloadable' => 0]);
            } else {
                $licenseStatus = StatusSetting::pluck('license_status')->first();
                if ($licenseStatus == 1) {
                    $this->editDateInAPL($sub, $updatesExpiry, $licenseExpiry, $supportExpiry);
                }
            }

            return $id;
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    //Update License Expiry Date
    public function getExpiryDate($permissions, $sub, $days)
    {
        $expiry_date = '';
        if ($days > 0 && $permissions == 1) {
            $date = \Carbon\Carbon::parse($sub->ends_at);
            $expiry_date = $date->addDays($days);
        }

        return $expiry_date;
    }

    //Update Updates Expiry Date
    public function getUpdatesExpiryDate($permissions, $sub, $days)
    {
        $expiry_date = '';
        if ($days > 0 && $permissions == 1) {
            $date = \Carbon\Carbon::parse($sub->update_ends_at);
            $expiry_date = $date->addDays($days);
        }

        return $expiry_date;
    }

    //Update Support Expiry Date
    public function getSupportExpiryDate($permissions, $sub, $days)
    {
        $expiry_date = '';
        if ($days > 0 && $permissions == 1) {
            $date = \Carbon\Carbon::parse($sub->support_ends_at);
            $expiry_date = $date->addDays($days);
        }

        return $expiry_date;
    }

    public function editDateInAPL($sub, $updatesExpiry, $licenseExpiry, $supportExpiry)
    {
        $productId = $sub->product_id;
        $domain = $sub->order->domain;
        $orderNo = $sub->order->number;
        $licenseCode = $sub->order->serial_key;
        $expiryDate = $updatesExpiry ? Carbon::parse($updatesExpiry)->format('Y-m-d') : '';
        $licenseExpiry = $licenseExpiry ? Carbon::parse($licenseExpiry)->format('Y-m-d') : '';
        $supportExpiry = $supportExpiry ? Carbon::parse($supportExpiry)->format('Y-m-d') : '';
        $noOfAllowedInstallation = '';
        $getInstallPreference = '';
        $cont = new \App\Http\Controllers\License\LicenseController();
        $noOfAllowedInstallation = $cont->getNoOfAllowedInstallation($licenseCode, $productId);
        $getInstallPreference = $cont->getInstallPreference($licenseCode, $productId);
        $updateLicensedDomain = $cont->updateExpirationDate($licenseCode, $expiryDate, $productId, $domain, $orderNo, $licenseExpiry, $supportExpiry, $noOfAllowedInstallation, $getInstallPreference);
    }

    public function postRazorpayPayment($invoice, $payment_method)
    {
        try {
            $invoice = Invoice::where('id', $invoice->invoice_id)->first();

            $payment_status = 'success';
            $payment_date = \Carbon\Carbon::now()->toDateTimeString();

            $invoice = Invoice::find($invoice->id);

            $invoice_status = 'success';

            $payment = $this->payment->create([
                'invoice_id' => $invoice->id,
                'user_id' => $invoice->user_id,
                'amount' => $invoice->grand_total,
                'payment_method' => $payment_method,
                'payment_status' => $payment_status,
                'created_at' => $payment_date,
            ]);
            $all_payments = $this->payment
            ->where('invoice_id', $invoice->id)
            ->where('payment_status', 'success')
            ->pluck('amount')->toArray();
            $total_paid = array_sum($all_payments);
            if ($total_paid >= $invoice->grand_total) {
                $invoice_status = 'success';
            }

            return $payment;
        } catch (\Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    private function getProcessingFee($paymentMethod, $currency)
    {
        if ($paymentMethod) {
            $de = $paymentMethod == 'razorpay' ? 0 : \DB::table(strtolower($paymentMethod))->where('currencies', $currency)->value('processing_fee');
            \DB::table(strtolower($paymentMethod))->where('currencies', $currency)->value('processing_fee');
        }
    }

    public function PaymentSuccessMailtoAdmin($invoice, $total, $user, $productName, $template, $order, $payment)
    {
        $amount = currencyFormat($total, getCurrencyForClient($user->country));
        $setting = Setting::find(1);
        $paymentSuccessdata = 'Payment for '.$productName.' of '.$user->currency.' '.$total.' successful by '.$user->first_name.' '.$user->last_name.' Email: '.$user->email;

        $mail = new \App\Http\Controllers\Common\PhpMailController();
        $mail->SendEmail($setting->email, $setting->company_email, $paymentSuccessdata, 'Payment Successful ');
        $mail->payment_log($user->email, $payment, 'success', $order->number, null, $amount, 'Product renew');
    }

    public function FailedPaymenttoAdmin($invoice, $total, $productName, $exceptionMessage, $user, $template, $order, $payment)
    {
        $amount = currencyFormat($total, getCurrencyForClient($user->country));
        $setting = Setting::find(1);
        $paymentFailData = 'Payment for'.' '.'of'.' '.$user->currency.' '.$total.' '.'failed by'.' '.$user->first_name.' '.$user->last_name.' '.'. User Email:'.' '.$user->email.'<br>'.'Reason:'.$exceptionMessage;
        $mail = new \App\Http\Controllers\Common\PhpMailController();
        $mail->SendEmail($setting->email, $setting->company_email, $paymentFailData, 'Payment failed ');
        $mail->payment_log($user->email, $payment, 'failed', $order->number, $exceptionMessage, $amount, 'Product renew');
    }
}
