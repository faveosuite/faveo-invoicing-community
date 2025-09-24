<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Model\Order\Order;
use App\Model\Product\ProductUpload;
use App\Model\Product\Subscription;
use Carbon\Carbon;
use Illuminate\Http\Request;

class OrderSearchController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function advanceOrderSearch(Request $request)
    {
        try {
            $query = Order::with([
                'user' => function ($q) {
                    $q->withTrashed()
                        ->select('id', 'first_name', 'last_name', 'email', 'mobile', 'mobile_code', 'country');
                },
                'productRelation',
                'installationDetail',
                'subscription' => function ($q) {
                    $q->with('plan');
                },
            ]);

            // Filters
            $this->filterOrderNum($query, $request->order_no);
            $this->filterProduct($query, $request->product_id);
            $this->filterDateRange($query, $request);
            $this->filterDomain($query, $request->domain);
            $this->filterInstallation($query, $request->act_ins);
            $this->filterRenewal($query, $request->renewal);
            $this->filterVersion($query, $request->version, $request->product_id);

            if (in_array($request->renewal, ['expiring_subscription', 'expired_subscription'])) {
                $query->orderByDesc('subscription.update_ends_at');
            }

            return $query;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    private function filterOrderNum($query, $orderNo)
    {
        if ($orderNo) {
            $query->where('number', $orderNo);
        }
    }

    private function filterProduct($query, $productId)
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

    private function filterDateRange($query, Request $request)
    {
        $field = $request->renewal ? 'subscription.update_ends_at' : 'created_at';

        if ($request->from && $request->till) {
            $from = Carbon::parse($request->from)->startOfDay();
            $till = Carbon::parse($request->till)->endOfDay();

            $query->whereBetween($field, [$from, $till]);
        } elseif ($request->from) {
            $from = Carbon::parse($request->from)->startOfDay();
            $query->whereDate($field, '>=', $from);
        } elseif ($request->till) {
            $till = Carbon::parse($request->till)->endOfDay();
            $query->whereDate($field, '<=', $till);
        }
    }

    private function filterDomain($query, $domain)
    {
        if ($domain) {
            $domain = rtrim($domain, '/');
            $query->whereHas('installation', function ($q) use ($domain) {
                $q->where('installation_path', 'like', "%$domain%");
            });
        }
    }

    private function filterInstallation($query, $filter)
    {
        if (! $filter) {
            return;
        }

        $minus30 = Carbon::now()->subDays(30);

        $query->whereHas('subscription', function ($q) use ($filter, $minus30) {
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

    private function filterRenewal($query, $renewal)
    {
        if ($version) {
            if ($productId == 'paid') {
                $latestVersion = ProductUpload::orderBy('version', 'desc')->value('version');
                if ($version == 'Latest' && $latestVersion) {
                    $baseQuery->where('subscriptions.version', '=', $latestVersion);
                } elseif ($version == 'Outdated' && $latestVersion) {
                    $baseQuery->where('subscriptions.version', '<', $latestVersion);
                }
            } elseif ($productId == 'unpaid') {
                $latestVersion = ProductUpload::orderBy('version', 'desc')->value('version');
                if ($version == 'Latest' && $latestVersion) {
                    $baseQuery->where('subscriptions.version', '=', $latestVersion);
                } elseif ($version == 'Outdated' && $latestVersion) {
                    $baseQuery->where('subscriptions.version', '<', $latestVersion);
                }
            } elseif ($version == 'Outdated') {
                $latestVersion = Subscription::where('product_id', $productId)->orderBy('version', 'desc')->value('version');

                if (! empty($latestVersion)) {
                    $baseQuery->whereNotNull('subscriptions.version')
                        ->where('subscriptions.version', '!=', '')
                        ->where('subscriptions.version', '<', $latestVersion);
                }
            } else {
                $baseQuery->where('subscriptions.version', '=', $version);
            }
        }

        $query->whereHas('subscription', function ($q) use ($version, $latest) {
            if ($version === 'Latest') {
                $q->where('version', $latest);
            } elseif ($version === 'Outdated') {
                $q->where('version', '<', $latest)->whereNotNull('version')->where('version', '!=', '');
            } else {
                $q->where('version', $version);
            }
        });
    }

    public function applyOrdersSearch($query, $search)
    {
        return $query->when($search, function ($q) use ($search) {
            $q->where(function ($q) use ($search) {
                // Search in order-level columns
                $q->where('number', 'like', "%{$search}%")
                    ->orWhere('order_status', 'like', "%{$search}%")

                    // Search in user-related fields
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('email', 'like', "%{$search}%")
                            ->orWhere('mobile', 'like', "%{$search}%")
                            ->orWhere('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('country', 'like', "%{$search}%")
                            ->orWhere(\DB::raw('CONCAT(first_name, " ", last_name)'), 'like', "%{$search}%");
                    })

                    // Search in product relation (product name)
                    ->orWhereHas('productRelation', function ($pq) use ($search) {
                        $pq->where('name', 'like', "%{$search}%");
                    })

                    // Search in subscription & plan
                    ->orWhereHas('subscription', function ($sq) use ($search) {
                        $sq->where('version', 'like', "%{$search}%")
                            ->orWhere('updated_at', 'like', "%{$search}%")
                            ->orWhere('update_ends_at', 'like', "%{$search}%")
                            ->orWhereHas('plan', function ($pq) use ($search) {
                                $pq->where('name', 'like', "%{$search}%");
                            });
                    });
            });
        });
    }
}
