<?php

namespace App\Http\Controllers;

use App\Model\Common\Setting;
use App\Model\Order\Invoice;
use App\Model\Order\Order;
use App\Model\Payment\Currency;
use App\Model\Product\Product;
use App\Model\Product\Subscription;
use App\User;
use Carbon\Carbon;
use DateTime;
use DB;
use Exception;
use Illuminate\Support\Facades\Date;

class DashboardController extends Controller
{
    /**
     * Get all the installations and their percentage that got active in the last 30 days with respect to inactive installation.
     *
     * @return array<mixed>
     */
    public function getLast30DaysInstallation(): array
    {
        $dayUtc = new Carbon('-30 days');
        $now = Date::now()->subDays(1);
        $rate = 0;
        $totalSubscriptionInLast30Days = Subscription::whereBetween('created_at', [$dayUtc, $now])->count();
        $inactiveInstallation = Subscription::whereColumn('created_at', '=', 'updated_at')->whereBetween('created_at', [$dayUtc, $now])->count();
        if ($totalSubscriptionInLast30Days) {
            $rate = (($totalSubscriptionInLast30Days - $inactiveInstallation) / $totalSubscriptionInLast30Days * 100);
        }

        return ['total_subscription' => $totalSubscriptionInLast30Days, 'inactive_subscription' => $inactiveInstallation, 'rate' => $rate];
    }

    /**
     * Calculates total sales.
     *
     * @param  $allowedCurrencies  The currency in which total needs to be calculated
     */
    public function getTotalSales(mixed $allowedCurrencies): float|int
    {
        $total = Invoice::leftJoin('payments', 'invoices.id', '=', 'payments.invoice_id')
            ->where('invoices.currency', $allowedCurrencies)
            ->where('invoices.status', '!=', 'pending')
            ->pluck('payments.amount')->all();

        return array_sum($total);
    }

    /**
     * Calculates yearly sales.
     *
     * @param  $allowedCurrencies  The currency in which yearly sales needs to be calculated
     */
    public function getYearlySales(mixed $allowedCurrencies): float|int
    {
        $currentYear = date('Y');
        $yearlytotal = Invoice::leftJoin('payments', 'invoices.id', '=', 'payments.invoice_id')
            ->whereYear('invoices.date', '=', $currentYear)
            ->where('invoices.currency', $allowedCurrencies)
            ->where('invoices.status', '!=', 'pending')
            ->pluck('payments.amount')->all();

        return array_sum($yearlytotal);
    }

    /**
     * Calculates monthly sales.
     *
     * @param  $allowedCurrencies  Currency in which monthly sales needs to be calculated
     */
    public function getMonthlySales(mixed $allowedCurrencies): float|int
    {
        $currentMonth = date('m');
        $currentYear = date('Y');
        $total = Invoice::leftJoin('payments', 'invoices.id', '=', 'payments.invoice_id')
            ->whereYear('invoices.date', '=', $currentYear)->whereMonth('invoices.date', '=', $currentMonth)
            ->where('invoices.currency', $allowedCurrencies)
            ->where('invoices.status', '!=', 'pending')
            ->pluck('payments.amount')->all();

        return array_sum($total);
    }

    /**
     * Calculates pending payments in the system.
     *
     * @param  $allowedCurrencies  Currency in which pending payment need to be calculated
     */
    public function getPendingPayments(mixed $allowedCurrencies): float|int
    {
        $total = Invoice::where('currency', $allowedCurrencies)
            ->where('status', '=', 'pending')
            ->pluck('grand_total')->all();

        return array_sum($total);
    }

    /**
     * Get the list of previous month registered users.
     */
    public function getAllUsers(): mixed
    {
        $dateBefore = Date::now()->subDays(31)->startOfDay()->setTime(12, 0, 0);

        $today = Date::now()->endOfDay();
        $fromDateStart = $dateBefore->format('Y-m-d').' 00:00:00';
        $tillDateEnd = $today->format('Y-m-d').' 23:59:59';

        Date::now()->endOfDay()->second(59);

        return User::orderBy('created_at', 'desc')
            ->where('active', 1)
            ->where('mobile_verified', 1)
            ->select('id', 'first_name', 'last_name', 'user_name', 'profile_pic', 'email', 'created_at')
            ->whereBetween('users.created_at', [$fromDateStart, $tillDateEnd])
            ->get();
    }

    /**
     * List of products sold in past $noOfDays days. If no parameter is passed, it will give all products.
     *
     *
     * @throws Exception
     */
    public function getSoldProducts(?int $noOfDays = null): mixed
    {
        // ASSUMING THIS CODE WON"T STAY ALIVE TILL year 3000
        $dateBefore = $noOfDays ? new Carbon(sprintf('-%s days', $noOfDays))->toDateTimeString() : Date::now()->startOfMillennium()->toDateTimeString();

        return Order::join('products', 'products.id', '=', 'orders.product')
            ->select(DB::raw('COUNT(*) as order_count'), 'products.id as product_id',
                'orders.created_at as order_created_at', 'products.image as product_image', 'products.name as product_name')
            ->where('order_status', 'executed')
            ->where('orders.created_at', '>', $dateBefore)
            ->orderBy('order_count', 'desc')
            ->orderBy('orders.created_at', 'desc')
            ->groupBy('products.id')
            ->get()->map(function ($element) {
                // product_image already set on element
                $element->order_created_at = getTimeInLoggedInUserTimeZone($element->order_created_at); // @phpstan-ignore property.notFound, property.notFound

                return $element;
            });
    }

    /**
     * List of orders of past 30 days.
     */
    public function getRecentOrders(): mixed
    {
        $dateBefore = new Carbon('-30 days')->toDateTimeString();

        return Order::with('user:id,first_name,last_name,email,user_name')
            ->join('products', 'products.id', '=', 'orders.product')
            ->select('products.id as product_id', 'orders.created_at as order_created_at', 'number as order_number', 'client', 'orders.id as order_id', 'products.name as product_name')
            ->where('orders.created_at', '>', $dateBefore)
            ->where('price_override', '>', 0)
            ->orderBy('orders.id', 'desc')
            ->get()->map(function ($element) {
                $element->order_created_at = getDateHtml($element->order_created_at); // @phpstan-ignore property.notFound, property.notFound

                $element->client_name = $element->user ? $element->user->first_name.' '.$element->user->last_name : User::onlyTrashed()->find($element->client)?->first_name.' '.User::onlyTrashed()->find($element->client)?->last_name; // @phpstan-ignore property.notFound

                $element->client_profile_link = \Config('app.url').'/clients/'.$element->client; // @phpstan-ignore property.notFound
                unset($element->user);

                return $element;
            });
    }

    /**
     * List of orders expiring in next 30 days.
     *
     * @param  bool  $past30Days
     *
     * @throws Exception
     */
    public function getExpiringSubscriptions($past30Days = false): mixed
    {
        $today = Date::now()->endOfDay();

        $baseQuery = Subscription::with('user:id,first_name,last_name,email,user_name')
            ->join('orders', 'subscriptions.order_id', '=', 'orders.id')
            ->join('products', 'products.id', '=', 'orders.product')
            ->select('subscriptions.id', 'products.id as product_id', 'orders.number as order_number', 'orders.id as order_id',
                'products.name as product_name', 'subscriptions.update_ends_at as subscription_ends_at', 'user_id')
            ->where('price_override', '>', 0);

        if ($past30Days) {
            $baseQuery->whereBetween('update_ends_at', [now()->subMonth()->toDateTimeString(), $today]);
        } else {
            $baseQuery->whereBetween('update_ends_at', [$today, now()->addMonth()->toDateTimeString()]);
        }

        $baseQuery->latest('subscription_ends_at')
            ->groupBy('subscriptions.id');

        return $baseQuery->get()->map(function ($element) {
            $element->client_name = $element->user ? $element->user->first_name.' '.$element->user->last_name : User::onlyTrashed()->find($element->user_id)?->first_name.' '.User::onlyTrashed()->find($element->user_id)?->last_name; // @phpstan-ignore property.notFound
            $element->client_profile_link = config('app.url').'/clients/'.$element->user_id; // @phpstan-ignore property.notFound
            $element->order_link = config('app.url').'/orders/'.$element->order_id; // @phpstan-ignore property.notFound
            $element->days_difference = date_diff(now(), new DateTime($element->subscription_ends_at))->format('%a days'); // @phpstan-ignore property.notFound, property.notFound
            $element->subscription_ends_at = getDateHtml($element->subscription_ends_at); // @phpstan-ignore property.notFound, property.notFound
            unset($element->user);

            return $element;
        });
    }

    /**
     * List of Invoices of past 30 ays.
     */
    public function getRecentInvoices(): mixed
    {
        $dateBefore = Date::now()->subDays(31)->startOfDay()->setTime(12, 0, 0);

        $today = Date::now()->endOfDay();
        $fromDateStart = $dateBefore->format('Y-m-d').' 00:00:00';
        $tillDateEnd = $today->format('Y-m-d').' 23:59:59';

        Date::now()->endOfDay()->second(59);

        return Invoice::with('user:id,first_name,last_name,email,user_name')
            ->leftJoin('currencies', 'invoices.currency', '=', 'currencies.code')
            ->leftJoin('payments', 'invoices.id', '=', 'payments.invoice_id')
            ->select('invoices.id as invoice_id', 'invoices.number as invoice_number', 'invoices.grand_total', 'invoices.status',
                DB::raw('SUM(payments.amount) as paid'), 'invoices.user_id', 'currencies.code as currency_code', 'invoices.date')
            ->whereBetween('invoices.date', [$fromDateStart, $tillDateEnd])

            ->groupBy('invoices.id')
            ->orderBy('invoices.date', 'desc')
            ->get()->map(function ($element) {
                $element->balance = (int) ($element->grand_total - $element->paid); // @phpstan-ignore property.notFound, property.notFound
                $element->status = getStatusLabel($element->status);
                $element->grand_total = currencyFormat((int) $element->grand_total, $element->currency_code); // @phpstan-ignore property.notFound
                $element->paid = currencyFormat((int) $element->paid, $element->currency_code); // @phpstan-ignore property.notFound, property.notFound, property.notFound
                $element->balance = currencyFormat((int) $element->balance, $element->currency_code); // @phpstan-ignore property.notFound
                $element->client_name = $element->user ? $element->user->first_name.' '.$element->user->last_name : User::onlyTrashed()->find($element->user_id)?->first_name.' '.User::onlyTrashed()->find($element->user_id)?->last_name; // @phpstan-ignore property.notFound
                $element->client_profile_link = \Config('app.url').'/clients/'.$element->user_id; // @phpstan-ignore property.notFound
                unset($element->user);

                return $element;
            });
    }

    /**
     * @param  array<mixed>  $totals
     * @return array<mixed>
     */
    private function formatCurrencyTotals(array $totals): array
    {
        $defaultCurrency = Setting::where('id', 1)->value('default_currency');
        $dashboardCurrency = Currency::where('dashboard_currency', 1)->value('code');

        $result = [];

        if ($defaultCurrency) {
            $result[$defaultCurrency] = $totals[$defaultCurrency] ?? 0;
        }

        if ($dashboardCurrency && $dashboardCurrency !== $defaultCurrency) {
            $result[$dashboardCurrency] = $totals[$dashboardCurrency] ?? 0;
        }

        return $result;
    }

    /**
     * @return array<mixed>
     */
    public function dashboard(): array
    {
        return [
            'totalSales' => $this->formatCurrencyTotals($this->getTotalSalesByCurrency()->toArray()),
            'yearlySales' => $this->formatCurrencyTotals($this->getYearlySalesByCurrency()->toArray()),
            'monthlySales' => $this->formatCurrencyTotals($this->getMonthlySalesByCurrency()->toArray()),
            'pendingPayments' => $this->formatCurrencyTotals($this->getAllPendingPayments()->toArray()),
            'productInstalledRate' => $this->getLastNoOfDaysInstallation(30),
            'paidOrderRate' => $this->getConversionRateByDays(30),

            'clientWithMobileAndEmailActivation' => $this->getUsersWithMobileAndEmailActivation(30),
            'recentInvoices' => $this->getAllRecentInvoices(30),
            'expiringOrders' => $this->getExpiringOrders(30),
            'expiredOrders' => $this->getExpiredOrders(30),
            'clientWithOutdatedProducts' => $this->getClientsUsingOldVersion(),
            'recentPaidOrders' => $this->getRecentPaidOrders(30),
            'productSoldInLast30Days' => $this->getSoldProduct(30),
            'totalProductsSold' => $this->getSoldProduct(),
        ];
    }

    private function getUsersWithMobileAndEmailActivation(int $days): mixed
    {
        return User::where('mobile_verified', 1)
            ->where('email_verified', 1)
            ->whereBetween('created_at', [
                Date::now()->subDays($days)->startOfDay(),
                Date::now()->endOfDay(),
            ])
            ->get();
    }

    private function getExpiredOrders(int $days): mixed
    {
        return Subscription::select(
            'id',
            'order_id',
            'update_ends_at',
            'user_id',
            'product_id',
            DB::raw('DATEDIFF(NOW(), update_ends_at) as days_expired')
        )
            ->with([
                'user:id,first_name,last_name',
                'order:id,number',
                'product:id,name',
            ])
            ->whereBetween('update_ends_at', [
                Date::now()->subDays($days)->startOfDay(),
                Date::now()->endOfDay(),
            ])
            ->orderBy('days_expired')
            ->get();
    }

    private function getExpiringOrders(int $days): mixed
    {
        return Subscription::select(
            'id',
            'order_id',
            'update_ends_at',
            'user_id',
            'product_id',
            DB::raw('DATEDIFF(update_ends_at, NOW()) as days_to_expire')
        )
            ->with([
                'user:id,first_name,last_name',
                'order:id,number',
                'product:id,name',
            ])
            ->whereBetween('update_ends_at', [
                Date::now()->startOfDay(),
                Date::now()->addDays($days)->endOfDay(),
            ])
            ->orderBy('days_to_expire')
            ->get();
    }

    public function getClientsUsingOldVersion(): mixed
    {
        // Fetch subscriptions whose product/version exists in outdated uploads
        return Subscription::select(
            'id',
            'order_id',
            'update_ends_at',
            'user_id',
            'product_id',
            'version'
        )
            ->whereExists(function ($query): void {
                $query->selectRaw('1')
                    ->from('product_uploads as pu')
                    ->whereColumn('pu.product_id', 'subscriptions.product_id')
                    ->whereColumn('pu.version', 'subscriptions.version')
                    ->whereRaw('pu.id NOT IN (SELECT MAX(id) FROM product_uploads GROUP BY product_id)');
            })
            ->with([
                'user:id,first_name,last_name',
                'product:id,name',
            ])
            ->whereHas('order', function ($query): void {
                $query->where('price_override', '>', 0);
            })
            ->orderBy('id', 'desc')
            ->get();
    }

    public function getRecentPaidOrders(int $days): mixed
    {
        return Order::select(
            'id',
            'number',
            'price_override',
            'client',
            'product',
            'created_at'
        )
            ->with([
                'user:id,first_name,last_name',
                'productRelation:id,name',
            ])
            ->where('price_override', '>', 0)
            ->whereBetween('created_at', [
                Date::now()->subDays($days)->startOfDay(),
                Date::now()->endOfDay(),
            ])
            ->orderBy('id', 'desc')
            ->get();
    }

    public function getTotalSalesByCurrency(): mixed
    {
        return Invoice::join('payments', 'invoices.id', '=', 'payments.invoice_id')
            ->where('invoices.status', '!=', 'pending')
            ->groupBy('invoices.currency')
            ->selectRaw('invoices.currency, SUM(payments.amount) as total')
            ->pluck('total', 'currency');
    }

    public function getYearlySalesByCurrency(): mixed
    {
        return Invoice::join('payments', 'invoices.id', '=', 'payments.invoice_id')
            ->where('invoices.status', '!=', 'pending')
            ->whereYear('invoices.created_at', Date::now()->year)
            ->groupBy('invoices.currency')
            ->selectRaw('invoices.currency, SUM(payments.amount) as total')
            ->pluck('total', 'currency');
    }

    public function getMonthlySalesByCurrency(): mixed
    {
        return Invoice::join('payments', 'invoices.id', '=', 'payments.invoice_id')
            ->where('invoices.status', '!=', 'pending')
            ->whereBetween('invoices.date', [now()->startOfMonth(), now()->endOfMonth()])
            ->groupBy('invoices.currency')
            ->selectRaw('invoices.currency, SUM(payments.amount) as total')
            ->pluck('total', 'currency');
    }

    public function getAllPendingPayments(): mixed
    {
        return DB::table(
            Invoice::leftJoin('payments', 'invoices.id', '=', 'payments.invoice_id')
                ->where('invoices.status', '!=', 'paid')
                ->groupBy('invoices.id', 'invoices.currency', 'invoices.grand_total')
                ->selectRaw('invoices.currency, invoices.grand_total - COALESCE(SUM(payments.amount), 0) as remaining')
                ->toBase(),
            'sub'
        )
            ->groupBy('currency')
            ->selectRaw('currency, SUM(remaining) as total')
            ->pluck('total', 'currency');
    }

    /**
     * @return array<mixed>
     */
    public function getLastNoOfDaysInstallation(int $days): array
    {
        $startDate = Date::now()->subDays($days)->startOfDay();
        $endDate = Date::now()->subDay()->endOfDay();

        // Total subscriptions in the period
        $totalSubscription = Subscription::whereBetween('created_at', [$startDate, $endDate])->count();

        // Inactive subscriptions (no installation detail)
        $inactiveSubscription = Subscription::whereBetween('created_at', [$startDate, $endDate])
            ->whereDoesntHave('order.licensedInstallations')
            ->count();

        // Calculate rate
        $rate = $totalSubscription ? (($totalSubscription - $inactiveSubscription) / $totalSubscription * 100) : 0;

        return [
            'total_subscription' => $totalSubscription,
            'inactive_subscription' => $inactiveSubscription,
            'rate' => $rate,
        ];
    }

    /**
     * @return array<mixed>
     */
    private function getConversionRateByDays(int $days): array
    {
        $startDate = Date::now()->subDays($days)->startOfDay();
        $endDate = Date::now()->endOfDay();

        // Total orders in the period
        $allOrders = Order::whereBetween('created_at', [$startDate, $endDate])->count();

        // Paid orders in the same period
        $paidOrders = Order::whereBetween('created_at', [$startDate, $endDate])
            ->where('price_override', '>', 0)
            ->count();

        $rate = $allOrders ? ($paidOrders / $allOrders * 100) : 0;

        return [
            'all_orders' => $allOrders,
            'paid_orders' => $paidOrders,
            'rate' => $rate,
        ];
    }

    private function getAllRecentInvoices(int $days): mixed
    {
        $fromDate = Date::now()->subDays($days)->startOfDay();
        $toDate = Date::now()->endOfDay();

        // Fetch invoices with user info and payment sum
        $invoices = Invoice::with([
            'user:id,first_name,last_name',
        ])
            ->withSum('payment', 'amount')
            ->whereBetween('date', [$fromDate, $toDate])
            ->orderByDesc('date')
            ->get([
                'id',
                'number',
                'date',
                'user_id',
                'grand_total',
                'currency',
                'status',
            ]);

        return $invoices->map(function ($invoice): array {
            $paidAmount = $invoice->payment_sum_amount ?? 0;
            $balance = $invoice->grand_total - $paidAmount;

            return [
                'id' => $invoice->id,
                'number' => $invoice->number,
                'date' => $invoice->date,
                'grand_total' => currencyFormat($invoice->grand_total, $invoice->currency),
                'currency' => $invoice->currency,
                'status' => getStatusLabel($invoice->status),
                'paid_amount' => currencyFormat($paidAmount, $invoice->currency),
                'balance' => currencyFormat($balance, $invoice->currency),
                'user' => $invoice->user,
            ];
        });
    }

    public function getSoldProduct(?int $days = null): mixed
    {
        $fromDate = $days ? Date::now()->subDays($days)->startOfDay() : null;
        $toDate = Date::now()->endOfDay();

        return Product::select('id', 'name', 'image')
            ->withCount(['order as order_count' => function ($query) use ($fromDate, $toDate): void {
                $query->where('order_status', 'executed');
                if ($fromDate) {
                    $query->whereBetween('created_at', [$fromDate, $toDate]);
                }
            }])
            ->withMax(['order as latest_order_created_at' => function ($query) use ($fromDate, $toDate): void {
                $query->where('order_status', 'executed');
                if ($fromDate) {
                    $query->whereBetween('created_at', [$fromDate, $toDate]);
                }
            }], 'created_at')
            ->having('order_count', '>', 0)
            ->orderByDesc('order_count')
            ->orderByDesc('latest_order_created_at')
            ->get();
    }
}
