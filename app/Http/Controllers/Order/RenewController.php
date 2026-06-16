<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Tenancy\CloudExtraActivities;
use App\License\Models\Installation;
use App\License\Services\InstallationService;
use App\License\Services\LicenseService;
use App\Model\Common\FaveoCloud;
use App\Model\Order\Invoice;
use App\Model\Order\InvoiceItem;
use App\Model\Order\Order;
use App\Model\Order\OrderInvoiceRelation;
use App\Model\Payment\Plan;
use App\Model\Product\Product;
use App\Model\Product\Subscription;
use App\Services\SubscriptionRenewalService;
use App\Traits\TaxCalculation;
use App\User;
use Auth;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Date;
use Session;

class RenewController extends BaseRenewController
{
    use TaxCalculation;

    protected $sub;

    protected $plan;

    protected $order;

    protected $invoice;

    protected $item;

    protected $product;

    protected $user;

    public function __construct()
    {
        $sub = new Subscription();
        $this->sub = $sub;

        $plan = new Plan();
        $this->plan = $plan;

        $order = new Order();
        $this->order = $order;

        $invoice = new Invoice();
        $this->invoice = $invoice;

        $item = new InvoiceItem();
        $this->item = $item;

        $product = new Product();
        $this->product = $product;

        $user = new User();
        $this->user = $user;
    }

    //Renew From admin panel
    public function renewBySubId($id, $planid, $payment_method, $cost, $code, $isAgentIncrease = true, $agents = null)
    {
        try {
            $plan = $this->plan->find($planid);
            $days = $plan->days;
            $sub = $this->sub->find($id);
            $currency = userCurrencyAndPrice($sub->user_id, $plan)['currency'];
            if ($isAgentIncrease) {
                resolve(SubscriptionRenewalService::class)->extendDates($sub, $days);
            }

            $invoice = $this->invoiceBySubscriptionId($id, $planid, $cost, $currency, $agents);

            if ($isAgentIncrease) {
                return $sub;
            }

            return $invoice;
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    //Renewal from ClienT Panel
    public function successRenew(Invoice $invoice): void
    {
        try {
            $invoice->status = 'success';
            $invoice->save();

            $orderId = OrderInvoiceRelation::where('invoice_id', $invoice->id)->value('order_id');

            $newPlanId = InvoiceItem::where('invoice_id', $invoice->id)->value('plan_id');

            $sub = Subscription::where('order_id', $orderId)->firstOrFail();

            if ($newPlanId) {
                $sub->plan_id = $newPlanId;
                $sub->save();
            }

            $days = Plan::findOrFail($sub->plan_id)->days;

            resolve(SubscriptionRenewalService::class)->extendDates($sub, (int) $days);
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function editDateInAPL($sub, $updatesExpiry, $licenseExpiry, $supportExpiry)
    {
        $productId = $sub->product_id;
        $domain = $sub->order->domain;
        $orderNo = $sub->order->number;
        $licenseCode = $sub->order->serial_key;
        $expiryDate = $updatesExpiry ? Date::parse($updatesExpiry)->format('Y-m-d') : '';
        $licenseExpiry = $licenseExpiry ? Date::parse($licenseExpiry)->format('Y-m-d') : '';
        $supportExpiry = $supportExpiry ? Date::parse($supportExpiry)->format('Y-m-d') : '';
        $installService = resolve(InstallationService::class);
        $licenseService = resolve(LicenseService::class);
        $noOfAllowedInstallation = $installService->countActiveInstallations($licenseCode);
        $ipAndDomain = LicenseService::parseIpAndDomain($domain);
        $existingLicense = $licenseService->findByCode($licenseCode);
        if ($existingLicense) {
            $licenseService->update($existingLicense->id, [
                'license_order_number' => $orderNo,
                'license_domain' => $ipAndDomain['domain'],
                'license_ip' => $ipAndDomain['ip'],
                'license_require_domain' => $ipAndDomain['requireDomain'],
                'license_expire_date' => $licenseExpiry,
                'license_updates_date' => $expiryDate,
                'license_support_date' => $supportExpiry,
                'license_limit' => $noOfAllowedInstallation ?: 2,
            ]);
        }
    }

    //Tuesday, June 13, 2017 08:06 AM

    public function getProductById($id)
    {
        try {
            $product = $this->product->where('id', $id)->first();
            if ($product) {
                return $product;
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function getUserById($id)
    {
        try {
            $user = $this->user->where('id', $id)->first();
            if ($user) {
                return $user;
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function createOrderInvoiceRelation($orderid, $invoiceid)
    {
        try {
            $relation = new OrderInvoiceRelation();
            $relation->create([
                'order_id' => $orderid,
                'invoice_id' => $invoiceid,
            ]);
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function getPriceByProductId($productid, $userid)
    {
        try {
            $product = $this->getProductById($productid);
            if (! $product) {
                throw new Exception(__('message.product_removed_database'));
            }

            $currency = $this->getUserCurrencyById($userid);
            $price = $product->price()->where('currency', $currency)->first();
            if (! $price) {
                throw new Exception(__('message.price_removed_database'));
            }

            $cost = $price->sales_price;
            if (! $cost) {
                $cost = $price->regular_price;
            }

            return $cost;
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function tax($product, $cost, $user)
    {
        try {
            $controller = new InvoiceController();
            $tax = $this->calculateTax($product->id, $user->state, $user->country);
            $tax_name = $tax['name'];
            $tax_rate = $tax['value'];

            $grand_total = $controller->calculateTotal($tax_rate, $cost);

            return rounding($grand_total);
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    /*
        Renew From Admin Panel
     */
    public function renew($id, Request $request)
    {
        $this->validate($request, [
            'plan' => 'required',
            'payment_method' => 'required',
            'cost' => 'required',
            'code' => 'exists:promotions,code',
        ],
            [
                'plan.required' => __('validation.plan_renewal.plan_required'),
                'payment_method.required' => __('validation.plan_renewal.payment_method_required'),
                'cost.required' => __('validation.plan_renewal.cost_required'),
                'code.exists' => __('validation.plan_renewal.code_not_valid'),
            ]);

        try {
            $agents = null;
            $planid = $request->input('plan');
            $payment_method = $request->input('payment_method');
            $code = $request->input('code');
            $cost = $request->input('cost');
            $sub = Subscription::find($id);
            $order_id = $sub->order_id;
            if ($request->has('agents')) {
                $agents = $request->input('agents');
                $installation_path = Installation::where('license_code', Order::find($order_id)->serial_key)->where('installation_path', '!=', cloudCentralDomain())->latest('updated_at')->value('installation_path');
                if (empty($installation_path)) {
                    return response(['status' => false, 'message' => trans('message.no_installation_found')]);
                }

                if ($this->checktheAgent($agents, $installation_path)) {
                    return response(['status' => false, 'message' => trans('message.agent_reduce')]);
                }

                $license = Order::where('id', $order_id)->value('serial_key');
                new CloudExtraActivities(new Client, new FaveoCloud())->doTheAgentAltering($agents, $license, $order_id, $installation_path, $sub->product_id);
            }

            $renew = $this->renewBySubId($id, $planid, $payment_method, $cost, $code = '', true, $agents);

            Subscription::where('order_id', $order_id)->update(['plan_id' => $planid]);

            if ($renew) {
                return successResponse(__('message.renewed_successfully'));
                // return redirect()->back()->with('success', __('message.renewed_successfully'));
            }

            return errorResponse(__('message.cannot_process'));
            //  return redirect()->back()->with('fails', __('message.cannot_process'));
        } catch (Exception $ex) {
            return errorResponse($ex->getMessage());
            // return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    /**
     * Show the Renew Page from by clicking onRenew in All Orders (Admin Panel).
     *
     * @param  int  $id  Subscription id for the order
     */
    public function renewForm($id, $agents = null)
    {
        try {
            $sub = $this->sub->find($id);
            $userid = $sub->user_id;
            if (User::onlyTrashed()->find($userid)) {//If User is soft deleted for this order
                throw new Exception(__('message.user_order_suspended'));
            }

            $productid = $sub->product_id;
            $plans = $this->plan->pluck('name', 'id')->toArray();
            $data = ['id' => $id,
                'productid' => $productid,
                'plans' => $plans,
                'userid' => $userid,
                'agents' => $agents];

            return successResponse('', $data);
//            return view('themes.default1.renew.renew', compact('id', 'productid', 'plans', 'userid', 'agents'));
        } catch (Exception $ex) {
            return errorResponse($ex->getMessage());
            //return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    public function renewByClient($id, Request $request)
    {
        try {
            $request->validate(
                ['plan' => ['required']],
                ['plan.required' => __('validation.plan_renewal.plan_required')]
            );

            $planId = (int) $request->input('plan');
            $sub = Subscription::findOrFail($id);
            $plan = Plan::findOrFail($planId);

            $existingUnpaidInvoice = $this->checkExistingUnpaidInvoice($sub, $planId);
            if ($existingUnpaidInvoice) {
                return successResponse(trans('message.existings_invoice'), ['invoice_id' => $existingUnpaidInvoice->invoice_id]);
            }

            $planDetails = userCurrencyAndPrice(Auth::user()->id, $plan);
            $price = $planDetails['plan']->renew_price;
            $currency = $planDetails['currency'];
            $noOfAgentsPerPlan = (int) $planDetails['plan']->no_of_agents;

            $agents = InvoiceItem::whereHas('invoice', fn (Builder $q) => $q->whereHas('orders', fn (Builder $q) => $q->where('orders.id', $sub->order_id))
            )
                ->orderByDesc('id')
                ->value('agents');

            $cost = ($agents > 0 && $noOfAgentsPerPlan > 0)
                ? ($price / $noOfAgentsPerPlan) * (int) $agents
                : $price;

            $items = $this->invoiceBySubscriptionId($id, $planId, $cost, $currency, $agents ?: null);
            $invoiceid = $items->invoice_id;

            return successResponse('', ['invoice_id' => $invoiceid]);
        } catch (Exception $ex) {
            return errorResponse($ex->getMessage());
        }
    }

    private function checkExistingUnpaidInvoice($subscription, $planId)
    {
        $invoice_id = OrderInvoiceRelation::where('order_id', $subscription->order_id)->latest()->value('invoice_id');

        $latestInvoiceItem = InvoiceItem::whereHas('invoice', function (Builder $query) use ($invoice_id, $planId): void {
            $query->where('invoice_id', $invoice_id)
                ->where('is_renewed', 1)
                ->where('status', 'pending')
                ->where('plan_id', $planId);
        })
            ->latest('created_at')
            ->first();

        return $latestInvoiceItem;
    }

    public function setSession($sub_id, $planid)
    {
        Session::put('subscription_id', $sub_id);
        Session::put('plan_id', $planid);
    }

    public function removeSession()
    {
        Session::forget('subscription_id');
        Session::forget('plan_id');
        Session::forget('invoiceid');
    }

    public function checkRenew($flag = 1)
    {
        $res = false;
        if (Session::has('subscription_id') && Session::has('plan_id') && $flag) {
            $res = true;
        }

        return $res;
    }

    //Update License Expiry Date
    public function getExpiryDate($permissions, $sub, int $days)
    {
        $expiry_date = '';
        if ($days > 0 && $permissions == 1) {
            $date = Date::parse($sub->ends_at);
            $expiry_date = $date->addDays($days);
        }

        return $expiry_date;
    }

    //Update Updates Expiry Date
    public function getUpdatesExpiryDate($permissions, $sub, int $days)
    {
        $expiry_date = '';
        if ($days > 0 && $permissions == 1) {
            $date = Date::parse($sub->update_ends_at);
            $expiry_date = $date->addDays($days);
        }

        return $expiry_date;
    }

    //Update Support Expiry Date
    public function getSupportExpiryDate($permissions, $sub, int $days)
    {
        $expiry_date = '';
        if ($days > 0 && $permissions == 1) {
            $date = Date::parse($sub->support_ends_at);
            $expiry_date = $date->addDays($days);
        }

        return $expiry_date;
    }

    private function checktheAgent($numberOfAgents, $domain)
    {
        $client = new Client([]);
        $data = ['number_of_agents' => $numberOfAgents];
        $response = $client->request(
            'POST',
            'https://'.$domain.'/api/agent-check', ['form_params' => $data]
        );
        $response = explode('{', (string) $response->getBody());

        $response = Arr::first($response);

        return json_decode((string) $response);
    }
}
