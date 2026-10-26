<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Model\Order\Order;
use App\Model\Product\ProductUpload;
use App\Model\Product\Subscription;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;

class OrderSearchController extends Controller
{
    /** An installation counts as active if it checked in within this many days (Status/Version badges too). */
    public const int ACTIVE_INSTALL_DAYS = 7;

    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    /**
     * @return Builder<Order>
     */
    public function advanceOrderSearch(Request $request): Builder
    {
        $query = Order::with([
            'user' => function ($q): void {
                $q->withTrashed()
                    ->select('id', 'first_name', 'last_name', 'email', 'mobile', 'mobile_code', 'country', 'deleted_at');
            },
            'productRelation.groupRelation',
            'installationLogs',
            'subscription' => function ($q): void {
                $q->with('plan');
            },
        ]);

        $this->filterOrderNum($query, $request->order_no);
        $this->filterProduct($query, $request->product_id);
        $this->filterDateRange($query, $request);
        $this->filterDomain($query, $request->domain);
        $this->filterInstallation($query, $request->act_ins);
        $this->filterRenewal($query, $request->renewal);
        $this->filterVersion($query, $request->version);
        if ($request->filled('client')) {
            $query->where('client', $request->client);
        }

        if (in_array($request->renewal, ['expiring_subscription', 'expired_subscription'])) {
            $query->orderByDesc(
                Subscription::select('ends_at')
                    ->whereColumn('subscriptions.order_id', 'orders.id')
                    ->limit(1)
            );
        }

        return $query;
    }

    /**
     * @param  Builder<Order>  $query
     */
    private function filterOrderNum(Builder $query, mixed $orderNo): void
    {
        if ($orderNo) {
            $query->where('number', $orderNo);
        }
    }

    /**
     * @param  Builder<Order>  $query
     */
    private function filterProduct(Builder $query, mixed $productId): void
    {
        if (! $productId) {
            return;
        }

        if ($productId === 'paid') {
            $query->where('price_override', '>', 0);
        } elseif ($productId === 'unpaid') {
            $query->where('price_override', '=', 0);
        } else {
            $query->where('product', $productId);
        }
    }

    /**
     * @param  Builder<Order>  $query
     */
    private function filterDateRange(Builder $query, Request $request): void
    {
        // Always the order date — the From/To the admin picks matches the Order Date column.
        $this->applyDateRange($query, 'orders.created_at', $request);
    }

    private function applyDateRange(\Illuminate\Contracts\Database\Query\Builder|Builder $query, string $column, Request $request): void
    {
        if ($request->from && $request->till) {
            $query->whereBetween($column, [
                userDayStart($request->from),
                userDayEnd($request->till),
            ]);
        } elseif ($request->from) {
            $query->where($column, '>=', userDayStart($request->from));
        } elseif ($request->till) {
            $query->where($column, '<=', userDayEnd($request->till));
        }
    }

    /**
     * @param  Builder<Order>  $query
     */
    private function filterDomain(Builder $query, mixed $domain): void
    {
        if ($domain) {
            $domain = rtrim((string) $domain, '/');
            $query->whereHas('licensedInstallations', function (\Illuminate\Contracts\Database\Query\Builder $q) use ($domain): void {
                $q->where('installations.installation_domain', 'like', likePattern($domain));
            });
        }
    }

    /**
     * @param  Builder<Order>  $query
     */
    private function filterInstallation(Builder $query, mixed $filter): void
    {
        // Same installation records the list's Status and Version columns are built from.
        $active = fn ($q) => $q->where('installation_logs.installation_last_active_date', '>=', Date::now()->subDays(self::ACTIVE_INSTALL_DAYS));

        match ($filter) {
            'installed' => $query->whereIn('orders.number', $this->installedOrderNumbers()),
            'not_installed' => $query->whereNotIn('orders.number', $this->installedOrderNumbers()),
            'paid_ins' => $query->whereIn('orders.number', $this->installedOrderNumbers($active)),
            'paid_inactive_ins' => $query->whereIn('orders.number', $this->installedOrderNumbers())
                ->whereNotIn('orders.number', $this->installedOrderNumbers($active)),
            default => null,
        };
    }

    /**
     * Order numbers with at least one installation (optionally narrowed by $scope).
     * Fetched as a list: as a subquery, MySQL runs NOT IN / whereDoesntHave() once per order
     * here (licenses.license_order_number has no index) — minutes instead of milliseconds.
     * ponytail: one id per installed order in memory; index licenses.license_order_number if that grows large.
     *
     * @return list<string>
     */
    private function installedOrderNumbers(?\Closure $scope = null): array
    {
        return DB::table('licenses')
            ->join('installation_logs', 'installation_logs.license_code', '=', 'licenses.license_code')
            ->whereNotNull('licenses.license_order_number')
            ->when($scope, $scope)
            ->distinct()
            ->pluck('licenses.license_order_number')
            ->all();
    }

    /**
     * @param  Builder<Order>  $query
     */
    private function filterRenewal(Builder $query, mixed $renewal): void
    {
        if (! $renewal) {
            return;
        }

        $now = Date::now();

        if ($renewal === 'expired_subscription') {
            $query->whereHas('subscription', function (\Illuminate\Contracts\Database\Query\Builder $q) use ($now): void {
                $q->where('ends_at', '<', $now);
            });
        } elseif ($renewal === 'active_subscription') {
            // No end date = lifetime license, which never expires.
            $query->whereHas('subscription', function (\Illuminate\Contracts\Database\Query\Builder $q) use ($now): void {
                $q->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now));
            });
        } elseif ($renewal === 'expiring_subscription') {
            $thirtyDaysFromNow = $now->copy()->addDays(30);
            $query->whereHas('subscription', function (\Illuminate\Contracts\Database\Query\Builder $q) use ($now, $thirtyDaysFromNow): void {
                $q->whereBetween('ends_at', [$now, $thirtyDaysFromNow]);
            });
        }
    }

    /**
     * @param  Builder<Order>  $query
     */
    private function filterVersion(Builder $query, mixed $version): void
    {
        if (! $version) {
            return;
        }

        if (! in_array($version, ['Latest', 'Outdated'], true)) {
            $query->whereIn('orders.number', $this->installedOrderNumbers(fn ($q) => $q->where('installation_logs.version_number', $version)));

            return;
        }

        // "Latest" = each product's most recently uploaded release, as everywhere else in the app.
        $latest = ProductUpload::latest()->get(['product_id', 'version'])->unique('product_id')->pluck('version', 'product_id');

        $onLatest = $this->installedOrderNumbers(function ($q) use ($latest): void {
            $q->where(function ($q) use ($latest): void {
                foreach ($latest as $productId => $latestVersion) {
                    $q->orWhere(fn ($q) => $q->where('licenses.product_id', $productId)->where('installation_logs.version_number', $latestVersion));
                }
            });
        });

        if ($version === 'Latest') {
            $latest->isEmpty() ? $query->whereRaw('0 = 1') : $query->whereIn('orders.number', $onLatest);

            return;
        }

        $query->whereIn('orders.number', $this->installedOrderNumbers(fn ($q) => $q->where('installation_logs.version_number', '!=', '')));
        if ($latest->isNotEmpty()) {
            $query->whereNotIn('orders.number', $onLatest);
        }
    }

    /**
     * @param  Builder<Order>  $query
     * @return Builder<Order>
     */
    public function applyOrdersSearch(Builder $query, mixed $search): Builder
    {
        return $query->when($search, function ($q) use ($search): void {
            $q->where(function (\Illuminate\Contracts\Database\Query\Builder $q) use ($search): void {
                // Search in order-level columns
                $q->where('number', 'like', likePattern($search))
                    ->orWhere('order_status', 'like', likePattern($search))

                    // Search in user-related fields
                    ->orWhereHas('user', function (\Illuminate\Contracts\Database\Query\Builder $uq) use ($search): void {
                        $uq->where('email', 'like', likePattern($search))
                            ->orWhere('mobile', 'like', likePattern($search))
                            ->orWhere('first_name', 'like', likePattern($search))
                            ->orWhere('last_name', 'like', likePattern($search))
                            ->orWhere('country', 'like', likePattern($search))
                            ->orWhere(DB::raw('CONCAT(first_name, " ", last_name)'), 'like', likePattern($search));
                    })

                    // Search in product relation (product name)
                    ->orWhereHas('productRelation', function (\Illuminate\Contracts\Database\Query\Builder $pq) use ($search): void {
                        $pq->where('name', 'like', likePattern($search));
                    })

                    // Search in subscription & plan
                    ->orWhereHas('subscription', function (\Illuminate\Contracts\Database\Query\Builder $sq) use ($search): void {
                        $sq->where('version', 'like', likePattern($search))
                            ->orWhere('updated_at', 'like', likePattern($search))
                            ->orWhere('update_ends_at', 'like', likePattern($search))
                            ->orWhereHas('plan', function (\Illuminate\Contracts\Database\Query\Builder $pq) use ($search): void {
                                $pq->where('name', 'like', likePattern($search));
                            });
                    });
            });
        });
    }
}
