<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\License\LicensePermissionsController;
use App\Http\Controllers\Tenancy\CloudExtraActivities;
use App\Model\Common\FaveoCloud;
use App\Model\Common\StatusSetting;
use App\Model\Order\InstallationDetail;
use App\Model\Order\Invoice;
use App\Model\Order\InvoiceItem;
use App\Model\Order\Order;
use App\Model\Order\OrderInvoiceRelation;
use App\Model\Payment\Plan;
use App\Model\Product\Product;
use App\Model\Product\Subscription;
use App\Traits\TaxCalculation;
use App\User;
use Carbon\Carbon;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
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
                $permissions = LicensePermissionsController::getPermissionsForProduct($sub->product_id);
                $licenseExpiry = $this->getExpiryDate($permissions['generateLicenseExpiryDate'], $sub, $days);
                $updatesExpiry = $this->getUpdatesExpiryDate($permissions['generateUpdatesxpiryDate'], $sub, $days);
                $supportExpiry = $this->getSupportExpiryDate($permissions['generateSupportExpiryDate'], $sub, $days);
                $sub->ends_at = $licenseExpiry;
                $sub->update_ends_at = $updatesExpiry;
                $sub->support_ends_at = $supportExpiry;
                $sub->save();
            }

            if (Order::where('id', $sub->order_id)->value('license_mode') == 'File') {
                Order::where('id', $sub->order_id)->update(['is_downloadable' => 0]);
            } else {
                $licenseStatus = StatusSetting::pluck('license_status')->first();
                if ($licenseStatus == 1 && $isAgentIncrease) {
                    $this->editDateInAPL($sub, $updatesExpiry, $licenseExpiry, $supportExpiry);
                }
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
    public function successRenew($invoice, $isCloud = false)
    {
        try {
            if (! $isCloud) {
                $invoice->processing_fee = $invoice->processing_fee;
                $invoice->status = 'success';
                $invoice->save();
            }
            $orderid = \DB::table('order_invoice_relations')->where('invoice_id', $invoice->id)->value('order_id');
            if (Session::has('plan_id')) {
                Subscription::where('order_id', $orderid)->update(['plan_id' => Session::get('plan_id')]);
            }
            // $id = Session::get('subscription_id');
            // $planid = Session::get('plan_id');
            $id = Subscription::where('order_id', $orderid)->value('id');
            $planid = Subscription::where('order_id', $orderid)->value('plan_id');
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
            $sub->save();
            if (Order::where('id', $sub->order_id)->value('license_mode') == 'File') {
                Order::where('id', $sub->order_id)->update(['is_downloadable' => 0]);
            } else {
                $licenseStatus = StatusSetting::pluck('license_status')->first();
                if ($licenseStatus == 1) {
                    $this->editDateInAPL($sub, $updatesExpiry, $licenseExpiry, $supportExpiry);
                }
            }
            $this->removeSession();
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
            $relation = new \App\Model\Order\OrderInvoiceRelation();
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
                throw new Exception('Product has removed from database');
            }
            $currency = $this->getUserCurrencyById($userid);
            $price = $product->price()->where('currency', $currency)->first();
            if (! $price) {
                throw new Exception('Price has removed from database');
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
            $controller = new \App\Http\Controllers\Order\InvoiceController();
            $tax = $this->calculateTax($product->id, $user->state, $user->country);
            $tax_name = $tax->getName();
            $tax_rate = $tax->getValue();

            $grand_total = $controller->calculateTotal($tax_rate, $cost);

            return rounding($grand_total);
        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage());
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
                $installation_path = InstallationDetail::where('order_id', $order_id)->where('installation_path', '!=', cloudCentralDomain())->latest()->value('installation_path');
                if (empty($installation_path)) {
                    return response(['status' => false, 'message' => trans('message.no_installation_found')]);
                }
                if ($this->checktheAgent($agents, $installation_path)) {
                    return response(['status' => false, 'message' => trans('message.agent_reduce')]);
                }
                $license = Order::where('id', $order_id)->value('serial_key');
                (new CloudExtraActivities(new Client, new FaveoCloud()))->doTheAgentAltering($agents, $license, $order_id, $installation_path, $sub->product_id);
            }
            $renew = $this->renewBySubId($id, $planid, $payment_method, $cost, $code = '', true, $agents);

            Subscription::where('order_id', $order_id)->update(['plan_id' => $planid]);

            if ($renew) {
                return redirect()->back()->with('success', 'Renewed Successfully');
            }

            return redirect()->back()->with('fails', 'Can not Process');
        } catch (Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
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
                throw new \Exception('The user for this order is suspended from the system. Restore the user to renew.');
            }
            $productid = $sub->product_id;
            $plans = $this->plan->pluck('name', 'id')->toArray();

            return view('themes.default1.renew.renew', compact('id', 'productid', 'plans', 'userid', 'agents'));
        } catch (Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    public function renewByClient($id, Request $request)
    {
        $subscription = Subscription::find($id);
        $userId = $subscription->user_id;
        $existingUnpaidInvoice = $this->checkExistingUnpaidInvoice($subscription, $request->input('plan'));
        if ($existingUnpaidInvoice) {
            return redirect('my-invoice/'.$existingUnpaidInvoice->invoice_id.'#invoice-section')
                ->with('warning', trans('message.existings_invoice'));
        }

        $this->validate($request, [
            'plan' => 'required',
            'code' => 'exists:promotions,code',
        ]);

        try {
            $planid = $request->input('plan');
            $code = $request->input('code');
            $plan = Plan::find($planid);
            $planDetails = userCurrencyAndPrice($request->input('user'), $plan);
            $cost = $planDetails['plan']->renew_price;
            $cost = preg_replace('/[^0-9]/', '', $cost);
            $currency = $planDetails['currency'];
            $agents = null;

            if ($request->has('agents')) {
                $agents = $request->input('agents');
                $sub = Subscription::find($id);
                $order_id = $sub->order_id;
                $installation_path = InstallationDetail::where('order_id', $order_id)->where('installation_path', '!=', cloudCentralDomain())->latest()->value('installation_path');
                $oldAgents = intval(substr(Order::where('id', $order_id)->value('serial_key'), 12));
                if ($oldAgents != $agents) {
                    if (empty($installation_path)) {
                        return redirect()->back()->with('fails', trans('message.without_installation_found'));
                    }
                    if ($this->checktheAgent($agents, $installation_path)) {
                        return redirect()->back()->with('fails', trans('message.agent_reduce'));
                    }
                }
                $cost = (int) $cost * (int) $agents;
            } else {
                if (Product::whereid(Plan::whereid($planid)->first()->product)->first()->can_modify_agent) {
                    $sub = Subscription::find($id);
                    $order_id = $sub->order_id;
                    $agents = intval(substr(Order::where('id', $order_id)->value('serial_key'), 12));
                    //This will get the cost of per agent
                    $cost = $cost/$planDetails['plan']->no_of_agents;
                    $cost = (int) $cost * (int) $agents;
                }
            }

            $items = $this->invoiceBySubscriptionId($id, $planid, $cost, $currency, $agents);
            $invoiceid = $items->invoice_id;
            $this->setSession($id, $planid);

            return redirect('paynow/'.$invoiceid);
        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage());
        }
    }

    private function checkExistingUnpaidInvoice($subscription, $planId)
    {
        $invoice_id = OrderInvoiceRelation::where('order_id', $subscription->order_id)->latest()->value('invoice_id');

        $latestInvoiceItem = InvoiceItem::whereHas('invoice', function ($query) use ($invoice_id, $planId) {
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

    private function checktheAgent($numberOfAgents, $domain)
    {
        $client = new Client([]);
        $data = ['number_of_agents' => $numberOfAgents];
        $response = $client->request(
            'POST',
            'https://'.$domain.'/api/agent-check', ['form_params' => $data]
        );
        $response = explode('{', (string) $response->getBody());

        $response = array_first($response);

        return json_decode($response);
    }
}
