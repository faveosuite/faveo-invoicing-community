<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Github\GithubApiController;
use App\License\Services\LicenseService;
use App\Model\Order\Order;
use App\Model\Payment\Plan;
use App\Model\Product\Product;
use App\Model\Product\ProductUpload;
use App\User;
use Auth;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Logger;
use Symfony\Component\HttpFoundation\Response;

class BaseProductController extends ExtendedBaseProductController
{
    public function getMyUrl(): string
    {
        $server = new Request;
        $url = \Illuminate\Support\Facades\Request::server('REQUEST_URI');
        $server = parse_url(is_string($url) ? $url : '');
        $server = is_array($server) ? $server : [];
        $server['path'] = dirname($server['path'] ?? '');
        $server = parse_url($server['path']);
        $server = is_array($server) ? $server : [];
        $server['path'] = dirname($server['path'] ?? '');

        $host = \Illuminate\Support\Facades\Request::server('HTTP_HOST');

        return 'http://'.(is_string($host) ? $host : '').$server['path'];
    }

    /*
    * Get Product Qty if Product can be modified
     */
    /**
     * @return array<mixed>
     */
    public function getProductQtyCheck(int $productId, Plan $plan, string $currency): array
    {
        if (! self::checkMultiProduct($productId)) {
            return [
                'can_modify' => false,
                'quantity' => null,
            ];
        }

        $value = $plan->planPrice
            ->where('currency', $currency)
            ->value('product_quantity');

        return [
            'can_modify' => true,
            'quantity' => empty($value) ? 1 : (int) $value, // @phpstan-ignore cast.int
        ];
    }

    /*
    * Check whether Product is allowed for Increasing the Quantity fromAdmin Panel
    * @param int $productid
    *
    * @return boolean
     */
    public function checkMultiProduct(int $productid): bool
    {
        $product = new Product;
        $product = $product->find($productid);
        if (! $product) {
            return false;
        }

        return $product->can_modify_quantity == 1;
    }

    /**
     * @return array<mixed>
     */
    public function getAgentQtyCheck(int $productId, Plan $plan, string $currency): array
    {
        if (! self::checkMultiAgent($productId)) {
            return [
                'can_modify' => false,
                'quantity' => null,
            ];
        }

        $value = $plan->planPrice
            ->where('currency', $currency)
            ->value('no_of_agents');

        return [
            'can_modify' => true,
            'quantity' => empty($value) ? 0 : (int) $value, // @phpstan-ignore cast.int
        ];
    }

    /*
    * Check whether No of the GAents can be modified or not fromAdmin Panel
    * @param int $productid
    *
    * @return boolean
     */
    public function checkMultiAgent(int $productid): bool
    {
        $product = new Product;
        $product = $product->find($productid);
        if (! $product) {
            return false;
        }

        return $product->can_modify_agent == 1;
    }

    /**
     * Get the Subscription and Price Based on the Product Selected while generating Invoice (Admin Panel).
     */
    public function getSubscriptionCheck(int $productid, Request $request): JsonResponse
    {
        try {
            /** @var User $authUser */
            $authUser = Auth::user();
            $useID = $request->input('user_id') ?: $authUser->id;
            /** @var User $userForCountry */
            $userForCountry = User::find($useID);
            $userCountry = $userForCountry->country;
            $currency = getCurrencyForClient($userCountry);
            $plans = Plan::where('product', $productid)
                ->whereHas('planPrice', function ($query) use ($currency): void {
                    $query->where('currency', $currency);
                })
                ->pluck('name', 'id')
                ->toArray();

            if (empty($plans)) { // If Plans Exist For A Product, Display Dropdown for Plans
                return errorResponse(__('message.no_available_plans_for_user_currency'));
            }

            $field = html()->div()
                ->class('form-group')
                ->children([
                    html()->label()
                        ->class('required')
                        ->text(__('message.subscription')), // Translated label
                    html()->select('plan', ['' => __('message.Select'), 'Plans' => $plans])
                        ->class('form-control')
                        ->id('plan')
                        ->attribute('onchange', 'getPrice(this.value)'),
                    html()->div()
                        ->class('error-message')
                        ->id('subscription-msg'),
                ])
                ->toHtml();

            return successResponse('', $field);
        } catch (Exception $exception) {
            Logger::exception($exception);

            return errorResponse($exception->getMessage());
        }
    }

    public function userDownload(mixed $order_id, mixed $version_id = ''): Response|JsonResponse
    {
        try {
            /** @var Order $order */
            $order = Order::with('subscription')->findOrFail($order_id);

            /** @var User $authUser2 */
            $authUser2 = Auth::user();
            if ($authUser2->role !== 'admin' && $authUser2->id !== $order->client) {
                throw new Exception(__('message.no_permission_for_action'));
            }

            $subscription = $order->subscription;

            if (! $subscription) {
                throw new Exception(__('message.no_order_exists_invoice'));
            }

            if ($subscription->update_ends_at && now()->gt($subscription->update_ends_at)) {
                throw new Exception(__('message.renew_subscription_download'));
            }

            $product = Product::findOrFail($order->product);

            if ($product->github_owner) {
                $tag = $version_id
                    ?: resolve(GithubApiController::class)->latestTag($product->github_owner, $product->github_repository);

                return $this->download($product, tag: $tag);
            }

            $version = ProductUpload::where('product_id', $order->product)
                ->when($version_id, fn ($q) => $q->where('id', $version_id))
                ->when($subscription->update_ends_at, fn ($q) => $q->where('created_at', '<', $subscription->update_ends_at))
                ->where('is_private', 0)
                ->latest()
                ->first();

            if (! $version) {
                throw new Exception(trans('message.renew_subscription_download'));
            }

            return $this->download($product, $version);
        } catch (Exception $exception) {
            Logger::exception($exception);

            return errorResponse($exception->getMessage());
        }
    }

    /**
     * Get Price For a Particular Plan Selected.
     *
     * get productid,userid,plan id as request
     */
    public function getPrice(Request $request): JsonResponse
    {
        $request->validate([
            'product' => ['required', 'integer'],
            'plan' => ['required', 'string'],
            'user' => ['nullable', 'integer'],
        ]);

        try {
            $productId = $request->input('product');
            $userId = $request->input('user');
            $planId = $request->input('plan');

            /** @var Plan $plan */
            $plan = Plan::findOrFail($planId);

            $currency = userCurrencyAndPrice($userId, $plan)['currency'];

            $userPlan = userCurrencyAndPrice($userId, $plan);
            if (empty($userPlan['plan'])) {
                return errorResponse(__('message.no_available_plans_currency'));
            }

            $planPrice = $userPlan['plan'];
            $cost = (float) $planPrice->add_price;
            $offer = $planPrice->offer_price ?? 0;
            $price = $offer > 0 ? $cost * (1 - $offer / 100) : $cost;

            $product = Product::findOrFail($productId);

            $result = [
                'price' => $price,
                'fields' => [
                    'required_domain' => (bool) $product->required_domain, // @phpstan-ignore property.notFound
                    'is_cloud_product' => in_array($productId, cloudPopupProducts())
                        ? ['domain' => cloudSubDomain()]
                        : false,
                ],
                'product_quantity' => $this->getProductQtyCheck($productId, $plan, $currency),
                'agents' => $this->getAgentQtyCheck($productId, $plan, $currency),
            ];

            return successResponse('', $result);
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    public function updateVersionFromGithub(mixed $productid, string $github_owner, string $github_repository): void
    {
        /** @var Product $product */
        $product = Product::findOrFail($productid);
        $product->version = resolve(GithubApiController::class)->latestTag($github_owner, $github_repository) ?? '';
        $product->save();
    }

    /**
     * Check Whether No. of Agents Allowed or Product Qunatity on cart.
     *
     * @author Ashutosh Pathak <ashutosh.pathak@ladybirdweb.com>
     *
     * @date   2019-01-11T00:18:49+0530
     */
    public function allowQuantityOrAgent(int $productid): bool
    {
        /** @var Product $product */
        $product = Product::find($productid);

        return (bool) $product->show_agent;
    }

    /**
     * Checks Permission for Incresing the no. of Agents/Quantity in Cart.
     *
     *
     * @param  int  $productid  The id of the Product added to the cart
     * @return array<mixed> The permissons for Agents and Quantity
     */
    public function isAllowedtoEdit(int $productid): array
    {
        /** @var Product $product */
        $product = Product::where('id', $productid)->first();

        $agentModifyPermission = $product->can_modify_agent;
        $quantityModifyPermission = $product->can_modify_quantity;

        return ['agent' => $agentModifyPermission, 'quantity' => $quantityModifyPermission];
    }

    public function getProductUsingLicenseCode(Request $request): JsonResponse
    {
        $license_code = $request->input('license_code');

        $licenseRecord = resolve(LicenseService::class)->findByCode($license_code);
        $product = $licenseRecord ? [collect($licenseRecord)->toArray()] : [];

        if ($product === []) {
            return errorResponse(__('message.product_not_found'));
        }

        $data = [
            'product_id' => $product[0]['product_id'],
        ];

        return successResponse(__('message.product_retrieved_successfully'), $data);
    }
}
