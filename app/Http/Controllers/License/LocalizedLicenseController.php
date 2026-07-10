<?php

namespace App\Http\Controllers\License;

use App\Http\Controllers\Controller;
use App\License\Models\License;
use App\License\Models\LicenseScheme;
use App\License\Services\InstallationService;
use App\License\Services\LicenseService;
use App\License\Services\Ed25519SigningService;
use App\Model\Order\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use SodiumException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LocalizedLicenseController extends Controller
{
    public function __construct(
        protected InstallationService $installationService,
        protected Ed25519SigningService $signingService,
    ) {
        $this->middleware('auth');
        $this->middleware('admin', ['except' => ['downloadFile', 'submitLicenseBinding', 'pluginsForOrder']]);
    }

    /**
     * Builds the signed license file on the fly and streams it as a download.
     * Only the order's own client may download it this way. Pass `productId`
     * (one of the order's attached plugins/add-ons) to get that plugin's
     * license file instead of the main product's.
     *
     * @throws SodiumException
     */
    public function downloadFile(Request $request): StreamedResponse|JsonResponse
    {
        if (! Auth::check()) {
            return errorResponse(__('message.access_denied'), 401);
        }

        $orderNo = (string) $request->query('orderNo');

        if (! $this->findOwnedOrder($orderNo)) {
            return errorResponse(__('message.access_denied'), 403);
        }

        $productId = $request->query('productId');

        return $this->streamFile($this->buildSignedLicenseFile($orderNo, $productId !== null ? (int) $productId : null));
    }

    /**
     * Submits an order's domain/IP and machine ID together, binding future
     * downloads of the offline license file to that specific server. Either
     * the order's own client, or an admin acting on their behalf, may call
     * this — required once, before the first download.
     */
    public function submitLicenseBinding(Request $request): JsonResponse
    {
        if (! Auth::check()) {
            return errorResponse(__('message.access_denied'), 401);
        }

        $orderNo = (string) $request->input('orderNo');
        $order = Order::where('number', $orderNo)->first();
        $isAdmin = Auth::user()?->role === 'admin';

        if (! $order || (! $isAdmin && $order->client !== Auth::id())) {
            return errorResponse(__('message.access_denied'), 403);
        }

        return $this->updateLicenseBinding($orderNo, $request);
    }

    private function updateLicenseBinding(string $orderNo, Request $request): JsonResponse
    {
        $domain = trim((string) $request->input('domain'));
        $machineId = trim((string) $request->input('machine_id'));

        if ($domain === '' || $machineId === '' || strlen($domain) > 255 || strlen($machineId) > 255) {
            return errorResponse(__('message.invalid'));
        }

        $ipAndDomain = LicenseService::parseIpAndDomain($domain);

        $updated = License::where('license_order_number', $orderNo)->update([
            'license_domain' => $ipAndDomain['domain'],
            'license_ip' => $ipAndDomain['ip'],
            'license_require_domain' => $ipAndDomain['requireDomain'],
            'license_machine_id' => $machineId,
        ]);

        if (! $updated) {
            return errorResponse(__('message.not_found', ['file' => __('message.localized_license')]), 404);
        }

        return successResponse(__('message.saved-successfully'));
    }

    private function findOwnedOrder(string $orderNo): ?Order
    {
        $order = Order::where('number', $orderNo)->first();

        return $order && $order->client === Auth::id() ? $order : null;
    }

    /**
     * Builds the signed license file on the fly and streams it as a download, via the admin panel.
     * Pass `$productId` (one of the order's attached plugins/add-ons) to get that
     * plugin's license file instead of the main product's.
     *
     * @throws SodiumException
     */
    public function downloadFileAdmin(string $orderNo, ?string $productId = null): StreamedResponse|JsonResponse
    {
        return $this->streamFile($this->buildSignedLicenseFile($orderNo, $productId !== null ? (int) $productId : null));
    }

    /**
     * Lists the add-on products attached to an order's license (bundled or
     * separately purchased - both end up as a license_plugins row the same
     * way), so the admin panel and the client's own order page can each offer
     * a per-plugin license file download. Callable by the order's own client
     * or an admin acting on their behalf, same access rule as license binding.
     */
    public function pluginsForOrder(string $orderNo): JsonResponse
    {
        if (! Auth::check()) {
            return errorResponse(__('message.access_denied'), 401);
        }

        $order = Order::where('number', $orderNo)->first();
        $isAdmin = Auth::user()?->role === 'admin';

        if (! $order || (! $isAdmin && $order->client !== Auth::id())) {
            return errorResponse(__('message.access_denied'), 403);
        }

        $license = License::where('license_order_number', $orderNo)->first();

        if (! $license instanceof License) {
            return errorResponse(__('message.not_found', ['file' => __('message.localized_license')]), 404);
        }

        return successResponse('', $license->addonProducts()->get(['products.id', 'products.name']));
    }

    private function streamFile(?string $file): StreamedResponse|JsonResponse
    {
        if ($file === null) {
            return errorResponse(__('message.not_found', ['file' => __('message.localized_license')]), 404);
        }

        return response()->streamDownload(
            fn () => print($file),
            'license.json',
            ['Content-Type' => 'application/json']
        );
    }

    /**
     * Lists every order currently in File (offline) license mode, with its
     * binding status, so an admin can see who's configured vs. still pending
     * without opening each order individually.
     */
    public function listFileModeOrders(Request $request): JsonResponse
    {
        $perPage = $request->input('limit', $request->input('perPage', 10));
        $page = $request->input('page', 1);
        $searchQuery = $request->input('search-query', $request->input('search_query', ''));
        $sortOrder = strtolower((string) $request->input('sort-order', $request->input('sort_order', 'desc'))) === 'asc' ? 'asc' : 'desc';
        $sortField = in_array($request->input('sort-field', $request->input('sort_field', 'id')), ['id', 'number', 'created_at'], strict: true)
            ? $request->input('sort-field', $request->input('sort_field', 'id'))
            : 'id';

        $orders = Order::with(['user:id,email', 'productRelation:id,name'])
            ->where('license_mode', 'File')
            ->when($searchQuery, function ($query) use ($searchQuery): void {
                $query->where(function ($q) use ($searchQuery): void {
                    $q->where('number', 'like', '%'.$searchQuery.'%')
                        ->orWhereHas('user', fn ($u) => $u->where('email', 'like', '%'.$searchQuery.'%'))
                        ->orWhereHas('productRelation', fn ($p) => $p->where('name', 'like', '%'.$searchQuery.'%'));
                });
            })
            ->orderBy($sortField, $sortOrder)
            ->paginate($perPage, ['*'], 'page', $page);

        $licenses = License::whereIn('license_order_number', $orders->getCollection()->pluck('number'))
            ->get(['license_order_number', 'license_domain', 'license_machine_id', 'license_expire_date'])
            ->keyBy('license_order_number');

        $orders->getCollection()->transform(function (Order $order) use ($licenses) {
            $license = $licenses->get($order->number);

            return (object) [
                'id' => $order->id,
                'number' => $order->number,
                'client_id' => $order->client,
                'client_email' => $order->user?->email,
                'product_id' => $order->productRelation?->id,
                'product_name' => $order->productRelation?->name,
                'license_domain' => $license?->license_domain,
                'license_machine_id' => $license?->license_machine_id,
                'license_expire_date' => $license?->license_expire_date,
                'is_bound' => (bool) ($license && $license->license_domain && $license->license_machine_id),
            ];
        });

        return successResponse('', $orders);
    }

    /**
     * Bulk-disables File (offline) license mode for the given orders,
     * switching them back to Database mode.
     */
    public function bulkDisableLicenseMode(Request $request): JsonResponse
    {
        $ids = (array) $request->input('select');

        if (empty($ids)) {
            return errorResponse(__('message.select-a-row'));
        }

        Order::whereIn('id', $ids)->update(['license_mode' => 'Database']);

        return successResponse(__('message.status_change_successfully'));
    }

    /**
     * Chooses which license mode is applicable File/Database.
     * */
    public function chooseLicenseMode(Request $request): JsonResponse
    {
        $orderNo = $request->input('orderNo');
        $chose = $request->boolean('choose');

        Order::where('number', $orderNo)->update(['license_mode' => $chose ? 'File' : 'Database']);

        return successResponse(__('message.status_change_successfully'));
    }

    /**
     * Builds and signs the offline license file for an order on the fly, so
     * it can be verified locally without any internet interaction and never
     * needs to be persisted anywhere. Returns null when there is no license
     * record to build it from. Pass `$pluginProductId` to build the file for
     * one of the order's attached plugins/add-ons instead of the main product.
     *
     * @throws SodiumException
     */
    private function buildSignedLicenseFile(string $orderNo, ?int $pluginProductId = null): ?string
    {
        $license = License::where('license_order_number', $orderNo)->first();

        if (! $license instanceof License) {
            return null;
        }

        $payload = $this->buildLicensePayload($license, $pluginProductId);

        if ($payload === false) {
            return null;
        }

        $file = json_encode([
            'license' => $payload,
            'signature' => $this->signingService->sign($payload),
        ]);

        return $file === false ? null : $file;
    }

    /**
     * Same field set returned by the online AFL license_verify callback, so the
     * licensed product can parse a local file identically to a live response.
     *
     * With `$pluginProductId` set, builds the payload for that attached plugin
     * instead: product_id becomes the plugin's own ID, and the scheme_query
     * carries both the create and update table schemes (scheme_id 2 and 3) —
     * the client decides at install time whether its local plugin_license
     * table already exists. Fails if the product isn't actually attached to
     * this license.
     */
    private function buildLicensePayload(License $license, ?int $pluginProductId = null): string|false
    {
        if ($pluginProductId !== null) {
            if (! $license->addonProducts()->where('products.id', $pluginProductId)->exists()) {
                return false;
            }

            $createScheme = LicenseScheme::where('id', 2)->where('scheme_status', 1)->first();
            $updateScheme = LicenseScheme::where('id', 3)->where('scheme_status', 1)->first();

            if (! $createScheme || ! $updateScheme) {
                return false;
            }

            $productId = $pluginProductId;
            $schemeQuery = [
                'plugin_create_schema' => base64_encode($createScheme->scheme_query),
                'plugin_update_schema' => base64_encode($updateScheme->scheme_query),
            ];
        } else {
            // scheme_id 1 = product_schema, matching the online /api/licenseScheme flow's
            // schemeId for a non-plugin install (see LicenseSchemeController::licenseScheme).
            $scheme = LicenseScheme::where('id', 1)->where('scheme_status', 1)->first();

            if (! $scheme) {
                return false;
            }

            $productId = $license->product_id;
            $schemeQuery = ['product_schema' => base64_encode($scheme->scheme_query)];
        }

        return json_encode([
            'license_code' => $license->license_code,
            'product_id' => $productId,
            'license_status' => $license->license_status,
            'license_expire_date' => $license->license_expire_date,
            'license_updates_date' => $license->license_updates_date,
            'license_support_date' => $license->license_support_date,
            'license_domain' => $license->license_domain,
            'license_ip' => $license->license_ip,
            'license_machine_id' => $license->license_machine_id,
            'scheme_query' => $schemeQuery,
        ]);
    }
}
