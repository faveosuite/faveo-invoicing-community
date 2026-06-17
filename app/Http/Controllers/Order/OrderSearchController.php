<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Model\Order\Order;
use App\Model\Product\ProductUpload;
use App\Model\Product\Subscription;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;

class OrderSearchController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder<\App\Model\Order\Order>
     */
    public function advanceOrderSearch(\Illuminate\Http\Request $request): \Illuminate\Database\Eloquent\Builder
    {
        $query = Order::with([
            'user' => function ($q): void {
                $q->withTrashed()
                    ->select('id', 'first_name', 'last_name', 'email', 'mobile', 'mobile_code', 'country');
            },
            'productRelation.groupRelation',
            'installationDetail',
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
        $this->filterVersion($query, $request->version, $request->product_id);
        if ($request->filled('client')) {
            $query->where('client', $request->client);
        }

        if (in_array($request->renewal, ['expiring_subscription', 'expired_subscription'])) {
            $query->orderByDesc(
                Subscription::select('update_ends_at')
                    ->whereColumn('subscriptions.order_id', 'orders.id')
                    ->limit(1)
            );
        }

        return $query;
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<\App\Model\Order\Order>  $query
     */
    private function filterOrderNum(\Illuminate\Database\Eloquent\Builder $query, mixed $orderNo): void
    {
        if ($orderNo) {
            $query->where('number', $orderNo);
        }
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<\App\Model\Order\Order>  $query
     */
    private function filterProduct(\Illuminate\Database\Eloquent\Builder $query, mixed $productId): void
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
     * @param  \Illuminate\Database\Eloquent\Builder<\App\Model\Order\Order>  $query
     */
    private function filterDateRange(\Illuminate\Database\Eloquent\Builder $query, \Illuminate\Http\Request $request): void
    {
        $field = $request->renewal ? 'subscription.update_ends_at' : 'created_at';

        if ($request->from && $request->till) {
            $from = Date::parse($request->from)->startOfDay();
            $till = Date::parse($request->till)->endOfDay();

            $query->whereBetween($field, [$from, $till]);
        } elseif ($request->from) {
            $from = Date::parse($request->from)->startOfDay();
            $query->whereDate($field, '>=', $from);
        } elseif ($request->till) {
            $till = Date::parse($request->till)->endOfDay();
            $query->whereDate($field, '<=', $till);
        }
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<\App\Model\Order\Order>  $query
     */
    private function filterDomain(\Illuminate\Database\Eloquent\Builder $query, mixed $domain): void
    {
        if ($domain) {
            $domain = rtrim((string) $domain, '/');
            $query->whereHas('installation', function ($q) use ($domain): void {
                $q->where('installation_path', 'like', sprintf('%%%s%%', $domain));
            });
        }
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<\App\Model\Order\Order>  $query
     */
    private function filterInstallation(\Illuminate\Database\Eloquent\Builder $query, mixed $filter): void
    {
        if (! $filter) {
            return;
        }

        $minus30 = Date::now()->subDays(30);

        $query->whereHas('subscription', function ($q) use ($filter, $minus30): void {
            if ($filter === 'installed') {
                $q->whereColumn('created_at', '!=', 'updated_at');
            } elseif ($filter === 'not_installed') {
                $q->whereColumn('created_at', '=', 'updated_at');
            } elseif ($filter === 'paid_inactive_ins') {
                $q->where('updated_at', '<', $minus30);
            } elseif ($filter === 'paid_ins') {
                $q->whereColumn('created_at', '!=', 'updated_at')
                    ->where('updated_at', '>', $minus30);
            }
        });
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<\App\Model\Order\Order>  $query
     */
    private function filterRenewal(\Illuminate\Database\Eloquent\Builder $query, mixed $renewal): void
    {
        if (! $renewal) {
            return;
        }

        $now = Date::now();

        if ($renewal === 'expired_subscription') {
            $query->whereHas('subscription', function ($q) use ($now): void {
                $q->where('update_ends_at', '<', $now);
            });
        } elseif ($renewal === 'active_subscription') {
            $query->whereHas('subscription', function ($q) use ($now): void {
                $q->where('update_ends_at', '>=', $now);
            });
        } elseif ($renewal === 'expiring_subscription') {
            $thirtyDaysFromNow = $now->copy()->addDays(30);
            $query->whereHas('subscription', function ($q) use ($now, $thirtyDaysFromNow): void {
                $q->whereBetween('update_ends_at', [$now, $thirtyDaysFromNow]);
            });
        }
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<\App\Model\Order\Order>  $query
     */
    private function filterVersion(\Illuminate\Database\Eloquent\Builder $query, mixed $version, mixed $productId): void
    {
        if (! $version) {
            return;
        }

        if (in_array($productId, ['paid', 'unpaid'])) {
            $latest = ProductUpload::orderBy('version', 'desc')->value('version');
        } else {
            $latest = Subscription::where('product_id', $productId)->orderBy('version', 'desc')->value('version');
        }

        $query->whereHas('subscription', function ($q) use ($version, $latest): void {
            if ($version === 'Latest') {
                $q->where('version', $latest);
            } elseif ($version === 'Outdated') {
                $q->where('version', '<', $latest)->whereNotNull('version')->where('version', '!=', '');
            } else {
                $q->where('version', $version);
            }
        });
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<\App\Model\Order\Order>  $query
     * @return \Illuminate\Database\Eloquent\Builder<\App\Model\Order\Order>
     */
    public function applyOrdersSearch(\Illuminate\Database\Eloquent\Builder $query, mixed $search): \Illuminate\Database\Eloquent\Builder
    {
        return $query->when($search, function ($q) use ($search): void {
            $q->where(function ($q) use ($search): void {
                // Search in order-level columns
                $q->where('number', 'like', sprintf('%%%s%%', $search))
                    ->orWhere('order_status', 'like', sprintf('%%%s%%', $search))

                    // Search in user-related fields
                    ->orWhereHas('user', function ($uq) use ($search): void {
                        $uq->where('email', 'like', sprintf('%%%s%%', $search))
                            ->orWhere('mobile', 'like', sprintf('%%%s%%', $search))
                            ->orWhere('first_name', 'like', sprintf('%%%s%%', $search))
                            ->orWhere('last_name', 'like', sprintf('%%%s%%', $search))
                            ->orWhere('country', 'like', sprintf('%%%s%%', $search))
                            ->orWhere(DB::raw('CONCAT(first_name, " ", last_name)'), 'like', sprintf('%%%s%%', $search));
                    })

                    // Search in product relation (product name)
                    ->orWhereHas('productRelation', function ($pq) use ($search): void {
                        $pq->where('name', 'like', sprintf('%%%s%%', $search));
                    })

                    // Search in subscription & plan
                    ->orWhereHas('subscription', function ($sq) use ($search): void {
                        $sq->where('version', 'like', sprintf('%%%s%%', $search))
                            ->orWhere('updated_at', 'like', sprintf('%%%s%%', $search))
                            ->orWhere('update_ends_at', 'like', sprintf('%%%s%%', $search))
                            ->orWhereHas('plan', function ($pq) use ($search): void {
                                $pq->where('name', 'like', sprintf('%%%s%%', $search));
                            });
                    });
            });
        });
    }
}
