<?php

namespace App\Http\Controllers\Product;

use App\Enums\OrderAbility;
use App\Http\Controllers\Github\GithubApiController;
use App\License\Services\LicenseService;
use App\Model\Order\Order;
use App\Model\Payment\Plan;
use App\Model\Product\Product;
use App\Model\Product\ProductUpload;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Logger;
use Symfony\Component\HttpFoundation\Response;

class BaseProductController extends ExtendedBaseProductController
{
    public function getMyUrl(): string
    {
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

    public function userDownload(mixed $order_id, mixed $version_id = ''): Response|JsonResponse
    {
        try {
            /** @var Order $order */
            $order = Order::with('subscription')->findOrFail($order_id);

            $access = Gate::inspect(OrderAbility::Download, $order);
            if ($access->denied()) {
                return deniedResponse($access);
            }

            $product = Product::findOrFail($order->product);

            if ($product->github_owner) {
                return $this->download($product, tag: $this->allowedGithubTag($order, $product, (string) $version_id));
            }

            // Only releases from before the updates period ended (OrderPolicy::downloadVersion).
            $updateEndsAt = $order->subscription?->update_ends_at;
            $version = ProductUpload::where('product_id', $order->product)
                ->when($version_id, fn ($q) => $q->where('id', $version_id))
                ->when($updateEndsAt && $updateEndsAt !== '0000-00-00 00:00:00', fn ($q) => $q->where('created_at', '<', $updateEndsAt))
                ->where('is_private', 0)
                ->latest()
                ->first();

            if (! $version) {
                throw new Exception(trans('message.renew_subscription_download'));
            }

            return $this->download($product, $version, order: $order);
        } catch (Exception $exception) {
            Logger::exception($exception);

            return errorResponse($exception->getMessage());
        }
    }

    /**
     * The GitHub release tag this order may download: the requested one, or else the
     * newest stable release — either way only one released before updates ended.
     */
    private function allowedGithubTag(Order $order, Product $product, string $requestedTag): string
    {
        $releases = collect(resolve(GithubApiController::class)->releases((string) $product->github_owner, (string) $product->github_repository));

        $release = $requestedTag !== ''
            ? $releases->firstWhere('tag_name', $requestedTag)
            : $releases->first(fn (array $r): bool => empty($r['prerelease']) && Gate::allows(OrderAbility::DownloadVersion, [$order, $r['created_at'] ?? null]));

        if (! is_array($release) || Gate::denies(OrderAbility::DownloadVersion, [$order, $release['created_at'] ?? null])) {
            throw new Exception(trans('message.renew_subscription_download'));
        }

        return (string) $release['tag_name'];
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
            $productId = $request->integer('product');
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
                    'required_domain' => (bool) $product->require_domain,
                    'is_cloud_product' => in_array($productId, cloudPopupProducts())
                        ? ['domain' => cloudSubDomain()]
                        : false,
                ],
                'product_quantity' => $this->getProductQtyCheck($productId, $plan, $currency),
                'agents' => $this->getAgentQtyCheck($productId, $plan, $currency),
            ];

            return successResponse('', $result);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $exception) {
            return errorResponse(__('message.record_not_found'));
        } catch (Exception $exception) {
            \Logger::exception($exception);

            return errorResponse(__('message.sorry_something_wrong'));
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

    public function getProductUsingLicenseCode(Request $request, LicenseService $licenseService): JsonResponse
    {
        $license_code = $request->input('license_code');

        $licenseRecord = $licenseService->findByCode($license_code);
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
