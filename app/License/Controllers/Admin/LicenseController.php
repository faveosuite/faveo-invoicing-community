<?php

namespace App\License\Controllers\Admin;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Date;
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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;

class LicenseController extends Controller
{
    public function __construct()
    {
        $this->ip_address = request()->server('REMOTE_ADDR');
    }

    public function licenseAdd(LicenseRequest $request)
    {
        $productId = $request->integer('product_id');
        $licenseCode = $request->get('license_code') ?: null;
        $clientId = $request->get('client_id') ?: null;

        if (! LicenseHelper::validateIntegerValue($productId) || ! LicenseHelper::validateIntegerValue($request->get('license_require_domain'), 0, 1) || ! LicenseHelper::validateIntegerValue($request->get('license_status'), 0, 2)) {
            return errorResponse(Lang::get('license::lang.invalid'), 400);
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

        if (! empty($checks)) {
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

        return successResponse(Lang::get('license::lang.adddd'), $clientFormatted, 201);
    }

    public function licenseUpdate(Request $request)
    {
        $license = License::with('user:id,email')->find($request->get('id'));
        if (! $license) {
            return errorResponse(Lang::get('license::lang.license_id'), 400);
        }

        $checks = $this->licenseChecks(
            $request->get('client_id') ?: null,
            $request->get('license_code') ?: null,
            $request->get('license_ip'),
            $request->get('license_domain'),
            $request->get('license_limit'),
            $request->get('license_expire_date'),
            $request->get('license_updates_date'),
            $request->get('license_support_date')
        );

        if (! empty($checks)) {
            return errorResponse($checks->getOriginalContent()['message'], 400);
        }

        $license->update([
            'license_order_number' => $request->get('license_order_number'),
            'license_ip' => $request->get('license_ip'),
            'license_domain' => $request->get('license_domain'),
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

        return successResponse(Lang::get('license::lang.license_Update'), $clientFormatted, 200);
    }

    public function deleteLicense(Request $request)
    {
        $license = License::find($request->get('id'));
        if (! $license) {
            return successResponse(Lang::get('license::lang.delete'), 0, 200);
        }

        DB::transaction(function () use ($license): void {
            $licenseCode = $license->license_code;
            LicenseCallback::where('license_code', $licenseCode)->delete();
            Installation::where('license_code', $licenseCode)->delete();
            InstallationLog::where('license_code', $licenseCode)->delete();
            $license->delete();
        });

        return successResponse(Lang::get('license::lang.delete'), 1, 200);
    }

    public function show(Request $request)
    {
        $perPage = $request->input('perPage', 10);
        $page = $request->input('page', 1);
        $searchQuery = $request->input('search_query', '');
        $sortOrder = strtolower((string) $request->input('sort_order', 'desc')) === 'asc' ? 'asc' : 'desc';
        $sortField = in_array($request->input('sort_field', 'id'), ['id', 'product_id', 'user_id', 'license_code', 'license_ip', 'license_limit', 'license_expire_date', 'license_support_date', 'license_order_number', 'license_domain', 'license_date', 'license_updates_date', 'license_status'], true) ? $request->input('sort_field', 'id') : 'id';

        $licenses = License::query()
            ->with(['product:id,name', 'user:id,email'])
            ->withCount(['installations as installation_counts', 'callbacks as call_backs_count'])
            ->withMax('callbacks as latest_call_backs', 'callback_date_time')
            ->when($searchQuery, function ($query) use ($searchQuery): void {
                $query->where(function ($q) use ($searchQuery): void {
                    $q->whereHas('user', fn ($u) => $u->where('email', 'like', '%'.$searchQuery.'%'))
                        ->orWhereHas('product', fn ($p) => $p->where('name', 'like', '%'.$searchQuery.'%'))
                        ->orWhere('license_code', 'like', '%'.str_replace('-', '', $searchQuery).'%')
                        ->orWhere('license_ip', 'like', '%'.$searchQuery.'%')
                        ->orWhere('license_limit', 'like', '%'.$searchQuery.'%')
                        ->orWhere('license_expire_date', 'like', '%'.$searchQuery.'%')
                        ->orWhere('license_support_date', 'like', '%'.$searchQuery.'%')
                        ->orWhere('license_order_number', 'like', '%'.$searchQuery.'%')
                        ->orWhere('license_domain', 'like', '%'.$searchQuery.'%')
                        ->orWhere('license_date', 'like', '%'.$searchQuery.'%')
                        ->orWhere('license_updates_date', 'like', '%'.$searchQuery.'%')
                        ->orWhere('license_status', 'like', '%'.LicenseHelper::statusFormatter($searchQuery).'%');
                });
            })
            ->orderBy($sortField, $sortOrder)
            ->paginate($perPage, ['*'], 'page', $page);

        $licenses->getCollection()->transform(fn(License $license) => (object) [
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
            'license_date' => $license->license_date,
            'license_updates_date' => $license->license_updates_date,
            'license_status' => $license->license_status,
            'product_title' => $license->product?->name,
            'client_email' => $license->user?->email,
            'license_order_url' => $license->license_order_number ?? '',
            'installation_counts' => $license->installation_counts,
            'latest_call_backs' => $license->latest_call_backs,
            'call_backs_count' => $license->call_backs_count,
        ]);

        return successResponse(Lang::get('license::lang.License_show'), $licenses, 200);
    }

    public function edit($license_id)
    {
        $license = License::with(['product:id,name', 'user:id,first_name,last_name,email'])->findOrFail($license_id);
        $productName = collect([(object) ['name' => $license->product?->name, 'id' => $license->id]]);
        $clientName = collect([(object) ['full_name' => trim($license->user?->first_name.' '.$license->user?->last_name).' <'.$license->user?->email.'>', 'id' => $license->user_id]]);

        return successResponse('', ['license' => $license, 'product_name' => $productName, 'client_name' => $clientName], 200);
    }

    public function formatClient($license_code, $client_email)
    {
        if (! empty($license_code)) {
            return $license_code;
        }

        return filter_var($client_email, FILTER_VALIDATE_EMAIL) ? $client_email : 'Unknown Client';
    }

    protected function licenseChecks($client_id, $license_code, $license_ip, $license_domain, $license_limit, $license_expire_date, $license_updates_date, $license_support_date)
    {
        if (! LicenseHelper::validateIntegerValue($client_id) && empty($license_code)) {
            return errorResponse(Lang::get('license::lang.error_client_or_license_code'), 400);
        }

        if (LicenseHelper::validateIntegerValue($client_id) && ! empty($license_code)) {
            return errorResponse(Lang::get('license::lang.invalid_licnese'), 400);
        }

        if (! empty($license_ip)) {
            foreach (explode(',', (string) $license_ip) as $ipToValidate) {
                if (! filter_var($ipToValidate, FILTER_VALIDATE_IP)) {
                    return errorResponse(Lang::get('license::lang.invalid_license_ip'), 400);
                }
            }
        }

        if (! empty($license_domain)) {
            foreach (explode(',', (string) $license_domain) as $domain) {
                if (! LicenseHelper::validateRawDomain(LicenseHelper::getRawDomain($domain)) || ! ctype_alnum(substr($domain, -1))) {
                    return errorResponse(Lang::get('license::lang.invalid_domain'), 400);
                }
            }
        }

        if (! empty($license_limit) && ! LicenseHelper::validateIntegerValue($license_limit)) {
            return errorResponse(Lang::get('license::lang.invalid_license_limit'), 400);
        }

        if (! empty($license_expire_date) && ! LicenseHelper::verifyDateTime($license_expire_date, 'Y-m-d')) {
            return errorResponse(Lang::get('license::lang.invalid_license_expiry'), 400);
        }

        if (! empty($license_updates_date) && ! LicenseHelper::verifyDateTime($license_updates_date, 'Y-m-d')) {
            return errorResponse(Lang::get('license::lang.invalid_license_update_date'), 400);
        }

        if (! empty($license_support_date) && ! LicenseHelper::verifyDateTime($license_support_date, 'Y-m-d')) {
            return errorResponse(Lang::get('license::lang.invalid_license_support_date'), 400);
        }
    }

    public function reissueLicenseCloud(Request $request)
    {
        Installation::where('license_code', $request->get('license_code'))->delete();
    }

    public function licenseDeactivate(Request $request)
    {
        License::where('license_code', $request->get('license_code'))->update(['license_status' => 0]);
    }

    public function updateTheLicenseCode(Request $request)
    {
        return License::where('license_code', $request->old_license_code)->update(['license_code' => $request->license_code]);
    }

    public function syncTheCreationOfLicense(Request $request)
    {
        try {
            $license = License::where('license_code', $request->input('license_code'))->first();
            if (! $license) {
                return response()->json(['error' => 'License not found'], 404);
            }

            $ids = collect(explode(',', (string) $request->input('ids')))->filter()->map(fn ($id) => (int) $id);
            foreach ($ids as $productId) {
                LicensePlugin::updateOrCreate(
                    ['license_id' => $license->id, 'product_id' => $productId],
                    ['license_id' => $license->id, 'product_id' => $productId]
                );
            }

            $inputOptions = json_decode((string) $request->input('options', '[]'), true);
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
        } catch (Exception $e) {
            Log::error($e->getMessage());

            return response()->json(['error' => 'Unable to sync license'], 500);
        }
    }

    public function licenseInfo(Request $request)
    {
        $license = License::with(['addonProducts.latestVersion'])->where('license_code', $request->input('license_code'))->firstOrFail();
        $product = Product::find($license->product_id);

        $addons = $license->addonProducts->map(fn($product) => [
            'id' => $product->id,
            'product_name' => $product->name,
            'product_attributes' => $product->product_attributes,
            'product_attributes_license' => $product->pivot->product_attributes_license ?? null,
            'latest_version' => $product->latestVersion?->version,
            'latest_version_file' => $product->latestVersion?->file,
        ]);

        return successResponse(Lang::get('license::lang.license_info'), ['license' => $license, 'product' => $product, 'addons' => $addons], 200);
    }

    public function individualLicenseInfo(Request $request): JsonResponse
    {
        $license = License::where('license_code', $request->input('license_code'))->with('licenseOptions')->first();
        if (! $license) {
            return successResponse('', []);
        }

        $licenseOptions = $license->licenseOptions->map(fn(LicenseOption $option) => [
            'license_code' => $license->license_code,
            'id' => $option->id,
            'option_group' => $option->option_group,
            'key' => $option->option_key,
            'value' => $option->option_value,
        ])->toArray();

        return successResponse('', $licenseOptions);
    }

    public function giveLicenseTakeOrder(Request $request)
    {
        return successResponse('', License::where('license_code', $request->input('license_code'))->value('license_order_number'));
    }

    public function getPluginInfo(Request $request)
    {
        $licenseCodes = collect(json_decode((string) $request->input('license_code'), true));
        $licenses = License::whereIn('license_code', $licenseCodes)
            ->where(function ($q): void {
                $q->where('license_expire_date', '>', Date::now())
                    ->orWhereNull('license_expire_date');
            })
            ->get()
            ->keyBy('license_code');

        $result = $licenseCodes->map(function ($licenseCode) use ($licenses) {
            $license = $licenses->get($licenseCode);
            if (! $license) {
                return null;
            }

            $ids = LicensePlugin::where('license_id', $license->id)->pluck('product_id')->toArray();
            $ids = ! empty($ids) ? $ids : [$license->product_id];

            return collect($ids)->unique()->map(fn ($id) => $this->generateLicenseData((int) $id, $licenseCode))->filter();
        })->filter()->values();

        return successResponse('', $result);
    }

    private function generateLicenseData($productId, $licenseCode)
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
            'path' => $product->product_path,
        ];
    }
}
