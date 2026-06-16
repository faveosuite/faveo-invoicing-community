<?php

namespace App\Services\Payment;

use App\Http\Controllers\Order\OrderController;
use App\Http\Controllers\Tenancy\TenantController;
use App\Model\Common\FaveoCloud;
use App\Model\Order\Invoice;
use App\Model\Order\InvoiceItem;
use App\Model\Payment\Plan;
use App\Model\Payment\PlanPrice;
use App\Model\Payment\TaxOption;
use App\Model\Product\CloudProducts;
use App\Model\Product\Product;
use App\User;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class FreeTrialService
{
    public function __construct()
    {
    }

    /**
     * @throws RuntimeException
     */
    public function checkEligibility(User $user, CloudProducts $cloudProduct): void
    {
        $used = DB::table('free_trial_allowed')
            ->where('user_id', $user->id)
            ->where('product_id', $cloudProduct->cloud_product)
            ->exists();

        if ($used) {
            throw new RuntimeException(__('message.limit_is_up'));
        }
    }

    /**
     * @throws RuntimeException
     */
    public function provision(User $user, string $domain, CloudProducts $cloudProduct): array
    {
        $currency = getCurrencyForClient($user->country);
        $plan = $this->resolveFreePlan($cloudProduct);
        $product = Product::findOrFail($cloudProduct->cloud_product);

        return DB::transaction(function () use ($user, $domain, $cloudProduct, $plan, $product, $currency) {
            $invoice = $this->createInvoice($user, $plan, $currency);
            $this->createInvoiceItem($invoice, $product, $plan, $currency);

            $order = (new OrderController)->executeOrder($invoice->id)
                ->firstWhere('product', $product->id)
                ?? throw new RuntimeException(__('message.cannot_generate_freetrial_cloud_instance'));

            $result = new TenantController(new Client(), new FaveoCloud())
                ->createTenant(new Request(['orderNo' => $order->number, 'domain' => $domain]));

            if (($result['status'] ?? '') === 'false') {
                throw new RuntimeException($result['message'] ?? __('message.cannot_generate_freetrial_cloud_instance'));
            }

            DB::table('free_trial_allowed')->insert([
                'user_id' => $user->id,
                'product_id' => $cloudProduct->cloud_product,
                'domain' => $result['Free_trial_domain'] ?? $domain,
            ]);

            return $result;
        });
    }

    private function resolveFreePlan(CloudProducts $cloudProduct): Plan
    {
        return Plan::where('id', $cloudProduct->cloud_free_plan)
            ->where('days', '<', 30)
            ->firstOrFail();
    }

    private function createInvoice(User $user, Plan $plan, string $currency): Invoice
    {
        $price = (float) (PlanPrice::where('plan_id', $plan->id)->where('currency', $currency)->value('add_price') ?? 0);
        $rounding = (bool) (TaxOption::find(1)?->rounding ?? false);
        $grandTotal = $rounding ? round($price) : $price;

        return Invoice::create([
            'user_id' => $user->id,
            'number' => random_int(11111111, 99999999),
            'date' => Date::now(),
            'grand_total' => $grandTotal,
            'status' => 'success',
            'currency' => $currency,
        ]);
    }

    private function createInvoiceItem(Invoice $invoice, Product $product, Plan $plan, string $currency): InvoiceItem
    {
        $price = (float) (PlanPrice::where('plan_id', $plan->id)->where('currency', $currency)->value('add_price') ?? 0);
        $agents = PlanPrice::where('plan_id', $plan->id)->value('no_of_agents');

        return InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'product_name' => $product->name,
            'product_id' => $product->id,
            'regular_price' => $price,
            'quantity' => 1,
            'tax_name' => 'null',
            'tax_percentage' => $product->planRelation()->where('id', $plan->id)->value('allow_tax'),
            'subtotal' => 0,
            'domain' => '',
            'plan_id' => $plan->id,
            'agents' => $agents,
        ]);
    }
}
