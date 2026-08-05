<?php

namespace App\License\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\License\Helpers\LicenseHelper;
use App\License\Models\Installation;
use App\License\Models\InstallationLog;
use App\License\Models\License;
use App\License\Models\LicenseCallback;
use App\License\Models\LicenseOption;
use App\License\Models\LicensePlugin;
use App\License\Requests\LicenseRequest;
use App\Model\Product\Product;
use App\Model\Product\ProductUpload;
use Exception;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LicenseController extends Controller
{
    public function __construct()
    {
        $this->ip_address = request()->server('REMOTE_ADDR'); // @phpstan-ignore property.notFound
    }

    public function licenseAdd(LicenseRequest $request): JsonResponse
    {
        $productId = $request->integer('product_id');
        $licenseCode = $request->get('license_code') ?: null;
        $clientId = $request->get('client_id') ?: null;

        if (! LicenseHelper::validateIntegerValue($productId) || ! LicenseHelper::validateIntegerValue($request->get('license_require_domain'), 0, 1) || ! LicenseHelper::validateIntegerValue($request->get('license_status'), 0, 2)) {
            return errorResponse(__('license::lang.invalid'), 400);
        }

        $checks = $this->licenseChecks(
            $clientId,
            $licenseCode,
            $request->get('license_ip'),
            $request->get('license_domain'),
            $request->get('license_limit'),
            $request->get('license_expire_date'),
            $request->get('license_updates_date'),
            $request->get('license_support_date')
        );

        if ($checks instanceof JsonResponse) {
            return $checks;
        }

        if (LicenseHelper::validateIntegerValue($clientId) && empty($licenseCode)) {
            do {
                $licenseCode = strtoupper(substr(str_shuffle(str_repeat('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789', 5)), 0, 16));
            } while (License::where('license_code', $licenseCode)->exists());
        }

        $license = License::create([
            'product_id' => $productId,
            'user_id' => LicenseHelper::validateIntegerValue($clientId) ? $clientId : null,
            'license_code' => $licenseCode,
            'license_order_number' => $request->get('license_order_number'),
            'license_ip' => $request->get('license_ip'),
            'license_domain' => $request->get('license_domain'),
            'license_machine_id' => $request->get('license_machine_id'),
            'license_require_domain' => $request->get('license_require_domain'),
            'license_limit' => $request->get('license_limit') ?: 1,
            'license_date' => now(),
            'license_cancel_date' => $request->get('license_status') == 1 ? null : now(),
            'license_expire_date' => $request->get('license_expire_date'),
            'license_expire_email_date' => $request->get('license_expire_date'),
            'license_updates_date' => $request->get('license_updates_date'),
            'license_updates_email_date' => $request->get('license_updates_date'),
            'license_support_date' => $request->get('license_support_date'),
            'license_support_email_date' => $request->get('license_support_date'),
            'license_comments' => $request->get('license_comments'),
            'license_status' => $request->get('license_status'),
        ]);

        $license->load('user:id,email');

        $clientFormatted = LicenseHelper::formatClient($license->license_code, $license->user?->email);

        return successResponse(__('license::lang.adddd'), $clientFormatted, 201);
    }

    public function licenseUpdate(Request $request): JsonResponse
    {
        /** @var License|null $license */
        $license = License::with('user:id,email')->find($request->get('id'));
        if (! $license) {
            return errorResponse(__('license::lang.license_id'), 400);
        }

        $checks = $this->licenseChecks(
            $request->get('client_id') ?: null,
            $request->get('license_code') ?: null,
            $request->get('license_ip'),
            $request->get('license_domain'),
            $request->get('license_limit'),
            $request->get('license_expire_date'),
            $request->get('license_updates_date'),
            $request->get('license_support_date'),
            isUpdate: true
        );

        if ($checks instanceof JsonResponse) {
            return errorResponse($checks->getOriginalContent()['message'], 400);
        }

        $license->update([
            'license_order_number' => $request->get('license_order_number'),
            'license_ip' => $request->get('license_ip'),
            'license_domain' => $request->get('license_domain'),
            'license_machine_id' => $request->get('license_machine_id'),
            'license_require_domain' => $request->get('license_require_domain'),
            'license_limit' => $request->get('license_limit'),
            'license_cancel_date' => $request->get('license_status') == 1 ? null : ($license->license_cancel_date ?: now()),
            'license_expire_date' => $request->get('license_expire_date'),
            'license_expire_email_date' => $request->get('license_expire_date') !== $license->license_expire_date ? null : $license->license_expire_email_date,
            'license_updates_date' => $request->get('license_updates_date'),
            'license_updates_email_date' => $request->get('license_updates_date') !== $license->license_updates_date ? null : $license->license_updates_email_date,
            'license_support_date' => $request->get('license_support_date'),
            'license_support_email_date' => $request->get('license_support_date') !== $license->license_support_date ? null : $license->license_support_email_date,
            'license_comments' => $request->get('license_comments'),
            'license_status' => $request->get('license_status'),
        ]);

        $clientFormatted = LicenseHelper::formatClient($license->license_code, $license->user?->email);

        return successResponse(__('license::lang.license_Update'), $clientFormatted, 200);
    }

    public function deleteLicense(Request $request): JsonResponse
    /** @var License|null $license */
    {
        /** @var License|null $license */
        $license = License::find($request->get('id'));
        if (! $license) {
            return successResponse(__('license::lang.delete'), 0, 200);
        }

        DB::transaction(function () use ($license): void {
            $licenseCode = $license->license_code;
            LicenseCallback::where('license_code', $licenseCode)->delete();
            Installation::where('license_code', $licenseCode)->delete();
            InstallationLog::where('license_code', $licenseCode)->delete();
            $license->delete();
        });

        return successResponse(__('license::lang.delete'), 1, 200);
    }

    public function show(Request $request): JsonResponse
    {
        $perPage = $request->input('limit', $request->input('perPage', 10));
        $page = $request->input('page', 1);
        $searchQuery = $request->input('search-query', $request->input('search_query', ''));
        $sortOrder = strtolower((string) $request->input('sort-order', $request->input('sort_order', 'desc'))) === 'asc' ? 'asc' : 'desc';
        $sortField = in_array($request->input('sort-field', $request->input('sort_field', 'id')), ['id', 'product_id', 'user_id', 'license_code', 'license_ip', 'license_machine_id', 'license_limit', 'license_expire_date', 'license_support_date', 'license_order_number', 'license_domain', 'license_date', 'license_updates_date', 'license_status'], strict: true) ? $request->input('sort-field', $request->input('sort_field', 'id')) : 'id';

        $licenses = License::query()
            ->with(['product:id,name', 'user:id,email'])
            ->withCount(['installations as installation_counts', 'callbacks as call_backs_count'])
            ->withMax('callbacks as latest_call_backs', 'callback_date_time')
            ->when($searchQuery, function ($query) use ($searchQuery): void {
                $query->where(function (Builder $q) use ($searchQuery): void {
                    $q->whereHas('user', fn (Builder $u) => $u->where('email', 'like', '%'.$searchQuery.'%'))
                        ->orWhereHas('product', fn (Builder $p) => $p->where('name', 'like', '%'.$searchQuery.'%'))
                        ->orWhere('license_code', 'like', '%'.str_replace('-', '', $searchQuery).'%')
                        ->orWhere('license_ip', 'like', '%'.$searchQuery.'%')
                        ->orWhere('license_limit', 'like', '%'.$searchQuery.'%')
                        ->orWhere('license_expire_date', 'like', '%'.$searchQuery.'%')
                        ->orWhere('license_support_date', 'like', '%'.$searchQuery.'%')
                        ->orWhere('license_order_number', 'like', '%'.$searchQuery.'%')
                        ->orWhere('license_domain', 'like', '%'.$searchQuery.'%')
                        ->orWhere('license_machine_id', 'like', '%'.$searchQuery.'%')
                        ->orWhere('license_date', 'like', '%'.$searchQuery.'%')
                        ->orWhere('license_updates_date', 'like', '%'.$searchQuery.'%')
                        ->orWhere('license_status', 'like', '%'.LicenseHelper::statusFormatter($searchQuery).'%');
                });
            })
            ->orderBy($sortField, $sortOrder)
            ->paginate($perPage, ['*'], 'page', $page);

        $licenses->getCollection()->transform(fn (License $license) => (object) [ // @phpstan-ignore method.unresolvableReturnType, argument.unresolvableType
            'id' => $license->id,
            'product_id' => $license->product_id,
            'client_id' => $license->user_id,
            'license_code' => $license->license_code,
            'license_ip' => $license->license_ip,
            'license_limit' => $license->license_limit,
            'license_expire_date' => $license->license_expire_date,
            'license_support_date' => $license->license_support_date,
            'license_order_number' => $license->license_order_number,
            'license_domain' => $license->license_domain,
            'license_machine_id' => $license->license_machine_id,
            'license_date' => $license->license_date,
            'license_updates_date' => $license->license_updates_date,
            'license_status' => $license->license_status,
            'product_title' => $license->product->name,
            'client_email' => $license->user?->email,
            'license_order_url' => $license->license_order_number ?? '',
            'installation_counts' => $license->installation_counts, // @phpstan-ignore property.notFound
            'latest_call_backs' => $license->latest_call_backs, // @phpstan-ignore property.notFound
            'call_backs_count' => $license->call_backs_count,
        ]);

        return successResponse(__('license::lang.License_show'), $licenses, 200);
    }

    public function edit(int $license_id): JsonResponse
    {
        $license = License::with(['product:id,name', 'user:id,first_name,last_name,email'])->findOrFail($license_id);
        $productName = collect([(object) ['product_id' => $license->product_id, 'product_title' => $license->product->name]]);
        $clientName = collect([(object) ['client_id' => $license->user_id, 'full_name' => trim($license->user?->first_name.' '.$license->user?->last_name)]]);

        return successResponse('', ['license' => $license, 'product_name' => $productName, 'client_name' => $clientName], 200);
    }

    public function formatClient(?string $license_code, ?string $client_email): string
    {
        if (! in_array($license_code, [null, '', '0'], strict: true)) {
            return $license_code;
        }

        return filter_var($client_email, FILTER_VALIDATE_EMAIL) ? (string) $client_email : 'Unknown Client';
    }

    protected function licenseChecks(mixed $client_id, ?string $license_code, ?string $license_ip, ?string $license_domain, mixed $license_limit, ?string $license_expire_date, ?string $license_updates_date, ?string $license_support_date, bool $isUpdate = false): ?JsonResponse
    {
        if (! LicenseHelper::validateIntegerValue($client_id) && in_array($license_code, [null, '', '0'], strict: true)) {
            return errorResponse(__('license::lang.error_client_or_license_code'), 400);
        }

        // A license_code paired with a client is only invalid on create (the code is meant to be
        // auto-generated for a selected client, see licenseAdd()) — an existing license being
        // edited legitimately has both already.
        if (! $isUpdate && LicenseHelper::validateIntegerValue($client_id) && ! in_array($license_code, [null, '', '0'], strict: true)) {
            return errorResponse(__('license::lang.invalid_licnese'), 400);
        }

        if (! in_array($license_ip, [null, '', '0'], strict: true)) {
            foreach (explode(',', $license_ip) as $ipToValidate) {
                if (! filter_var($ipToValidate, FILTER_VALIDATE_IP)) {
                    return errorResponse(__('license::lang.invalid_license_ip'), 400);
                }
            }
        }

        if (! in_array($license_domain, [null, '', '0'], strict: true)) {
            foreach (explode(',', $license_domain) as $domain) {
                if (! LicenseHelper::validateRawDomain(LicenseHelper::getRawDomain($domain)) || ! ctype_alnum(substr($domain, -1))) {
                    return errorResponse(__('license::lang.invalid_domain'), 400);
                }
            }
        }

        if (! empty($license_limit) && ! LicenseHelper::validateIntegerValue($license_limit)) {
            return errorResponse(__('license::lang.invalid_license_limit'), 400);
        }

        if (! in_array($license_expire_date, [null, '', '0'], strict: true) && ! LicenseHelper::verifyDateTime($license_expire_date, 'Y-m-d')) {
            return errorResponse(__('license::lang.invalid_license_expiry'), 400);
        }

        if (! in_array($license_updates_date, [null, '', '0'], strict: true) && ! LicenseHelper::verifyDateTime($license_updates_date, 'Y-m-d')) {
            return errorResponse(__('license::lang.invalid_license_update_date'), 400);
        }

        if (! in_array($license_support_date, [null, '', '0'], strict: true) && ! LicenseHelper::verifyDateTime($license_support_date, 'Y-m-d')) {
            return errorResponse(__('license::lang.invalid_license_support_date'), 400);
        }

        return null;
    }

    public function reissueLicenseCloud(Request $request): void
    {
        Installation::where('license_code', $request->get('license_code'))->delete();
    }

    public function licenseDeactivate(Request $request): void
    {
        License::where('license_code', $request->get('license_code'))->update(['license_status' => 0]);
    }

    public function updateTheLicenseCode(Request $request): int
    {
        return License::where('license_code', $request->old_license_code)->update(['license_code' => $request->license_code]);
    }

    public function syncTheCreationOfLicense(Request $request): JsonResponse
    {
        try {
            $license = License::where('license_code', $request->input('license_code'))->first();
            if (! $license) {
                return response()->json(['error' => 'License not found'], 404);
            }

            $ids = collect(explode(',', (string) $request->input('ids')))->filter()->map(fn ($id): int => (int) $id);
            foreach ($ids as $productId) {
                LicensePlugin::updateOrCreate(
                    ['license_id' => $license->id, 'product_id' => $productId],
                    ['license_id' => $license->id, 'product_id' => $productId]
                );
            }

            $inputOptions = json_decode((string) $request->input('options', '[]'), associative: true);
            foreach ($inputOptions as $option) {
                if (empty($option['key'])) {
                    continue;
                }

                LicenseOption::updateOrCreate(
                    [
                        'option_key' => $option['key'],
                        'option_group' => (string) $license->id,
                    ],
                    [
                        'option_value' => (string) ($option['value'] ?? ''),
                    ]
                );
            }

            return response()->json(['message' => 'License synchronization and options insertion complete']);
        } catch (Exception $exception) {
            Log::error($exception->getMessage());

            return response()->json(['error' => 'Unable to sync license'], 500);
        }
    }

    public function licenseInfo(Request $request): JsonResponse
    {
        $license = License::with(['addonProducts.latestVersion'])->where('license_code', $request->input('license_code'))->firstOrFail();
        $product = Product::find($license->product_id);

        $addons = $license->addonProducts->map(fn ($product): array => [ // @phpstan-ignore method.unresolvableReturnType, argument.unresolvableType
            'id' => $product->id,
            'product_name' => $product->name,
            'product_attributes' => $product->product_attributes, // @phpstan-ignore property.notFound
            'product_attributes_license' => $product->pivot->product_attributes_license ?? null,
            'latest_version' => $product->latestVersion?->version,
            'latest_version_file' => $product->latestVersion?->file,
        ]);

        return successResponse(__('license::lang.license_info'), ['license' => $license, 'product' => $product, 'addons' => $addons], 200);
    }

    public function individualLicenseInfo(Request $request): JsonResponse
    {
        $license = License::where('license_code', $request->input('license_code'))->with('licenseOptions')->first();
        if (! $license) {
            return successResponse('', []);
        }

        $licenseOptions = $license->licenseOptions->map(fn (LicenseOption $option): array => [
            'license_code' => $license->license_code,
            'id' => $option->id,
            'option_group' => $option->option_group,
            'key' => $option->option_key,
            'value' => $option->option_value,
        ])->toArray();

        return successResponse('', $licenseOptions);
    }

    public function giveLicenseTakeOrder(Request $request): JsonResponse
    {
        return successResponse('', License::where('license_code', $request->input('license_code'))->value('license_order_number'));
    }

    public function getPluginInfo(Request $request): JsonResponse
    {
        $licenseCodes = collect((array) json_decode((string) $request->input('license_code'), associative: true));
        $licenses = License::whereIn('license_code', $licenseCodes)
            ->where(function ($q): void {
                $q->where('license_expire_date', '>', Date::now())
                    ->orWhereNull('license_expire_date');
            })
            ->get()
            ->keyBy('license_code');

        $result = $licenseCodes->map(function (string $licenseCode) use ($licenses) {
            $license = $licenses->get($licenseCode);
            if (! $license) {
                return null;
            }

            $ids = LicensePlugin::where('license_id', $license->id)->pluck('product_id')->toArray();
            $ids = empty($ids) ? [$license->product_id] : $ids;

            return collect($ids)->unique()->map(fn ($id): ?array => $this->generateLicenseData((int) $id, $licenseCode))->filter();
        })->filter()->values();

        return successResponse('', $result);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function generateLicenseData(int $productId, string $licenseCode): ?array
    {
        $product = Product::find($productId);
        $version = ProductUpload::where('product_id', $productId)->latest()->first();
        $installed = Installation::where('product_id', $productId)->where('license_code', $licenseCode)->exists();

        if (! $product || ! $version || $installed) {
            return null;
        }

        return [
            'id' => $productId,
            'product_name' => $product->name,
            'product_key' => $product->product_key,
            'product_description' => $product->product_description,
            'version' => $version->version,
            'license_code' => $licenseCode,
            'path' => $product->product_path, // @phpstan-ignore property.notFound
        ];
    }
}
