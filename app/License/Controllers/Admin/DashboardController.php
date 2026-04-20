<?php

namespace App\License\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\License\Helpers\LicenseHelper;
use App\License\Models\Installation;
use App\License\Models\License;
use App\License\Models\LicenseCallback;
use App\License\Models\LicenseReport;
use App\License\Models\VersionCallback;
use App\Model\Product\Product;
use App\Model\Product\ProductUpload;
use Carbon\Carbon;
use Illuminate\Support\Facades\Lang;

class DashboardController extends Controller
{
    public function dashboard()
    {
        $productsCount = Product::count();
        $versionsCount = ProductUpload::distinct('version')->count('version');
        $licenseCount = License::count();
        $callbacksCount = LicenseCallback::count() + VersionCallback::count();

        $latestProducts = Product::query()
            ->select('id', 'name', 'product_sku', 'status')
            ->where('status', '1')
            ->withCount(['versions', 'installations', 'licenses'])
            ->latest('id')
            ->take(10)
            ->get()
            ->map(function (Product $product) {
                return (object) [
                    'id' => $product->id,
                    'product_title' => $product->name,
                    'product_sku' => $product->product_sku,
                    'product_status' => $product->status,
                    'versions' => $product->versions_count,
                    'installations_count' => $product->installations_count,
                    'licenses_count' => $product->licenses_count,
                ];
            });

        $latestVersions = ProductUpload::query()
            ->with('product:id,name')
            ->active()
            ->latest('id')
            ->take(10)
            ->get()
            ->map(function (ProductUpload $version) {
                return (object) [
                    'id' => $version->id,
                    'product_id' => $version->product_id,
                    'version_number' => $version->version,
                    'version_date' => LicenseHelper::formatDatetime($version->created_at),
                    'version_status' => $version->status,
                    'product_title' => optional($version->product)->name,
                ];
            });

        $latestInstallations = Installation::query()
            ->with('license:id,license_code')
            ->where('installation_status', '1')
            ->orderByDesc('installation_date')
            ->orderByDesc('id')
            ->take(10)
            ->get()
            ->map(function (Installation $installation) {
                return (object) [
                    'id' => $installation->id,
                    'license_code' => $installation->license_code,
                    'installation_ip' => $installation->installation_ip,
                    'installation_domain' => $installation->installation_domain,
                    'installation_date' => LicenseHelper::formatDatetime($installation->installation_date),
                    'installation_status' => $installation->installation_status,
                    'license_id' => optional($installation->license)->id,
                ];
            });

        $latestCallbacks = LicenseCallback::query()
            ->where('callback_status', '1')
            ->distinct('callback_ip')
            ->orderByDesc('callback_date_time')
            ->orderByDesc('id')
            ->take(10)
            ->get()
            ->map(fn (LicenseCallback $cb) => (object) [
                'id' => $cb->id,
                'callback_domain' => $cb->callback_domain,
                'callback_ip' => $cb->callback_ip,
                'callback_date_time' => LicenseHelper::formatDatetime($cb->callback_date_time),
                'callback_status' => $cb->callback_status,
            ]);

        $latestReports = LicenseReport::with('product:id,name', 'user:id,email')
            ->where('report_status', '1')
            ->orderByDesc('report_date_time')
            ->take(10)
            ->get();

        $currentDateTime = Carbon::now()->toDateTimeString();

        $expiredVersions = ProductUpload::query()
            ->select('id', 'version', 'created_at', 'version_expire_date', 'status')
            ->where(function ($query) {
                $query->whereNotNull('version_expire_date')
                    ->orWhere('status', 0);
            })
            ->latest('id')
            ->take(10)
            ->get()
            ->map(fn (ProductUpload $v) => (object) [
                'id' => $v->id,
                'version_number' => $v->version,
                'version_date' => LicenseHelper::formatDatetime($v->created_at),
                'version_expire_date' => LicenseHelper::formatDate($v->version_expire_date),
                'version_status' => $v->status,
            ]);

        $latestClients = \App\User::query()
            ->select('id as client_id', 'first_name', 'last_name', 'email as client_email', 'created_at as client_active_date', 'active as client_status')
            ->withCount('licenses')
            ->where('active', '1')
            ->latest('created_at')
            ->take(10)
            ->get()
            ->map(function ($user) {
                return (object) [
                    'client_id' => $user->client_id,
                    'full_name' => trim($user->first_name.' '.$user->last_name),
                    'client_email' => $user->client_email,
                    'client_active_date' => LicenseHelper::formatDatetime($user->client_active_date),
                    'client_status' => $user->client_status,
                    'license_count' => $user->licenses_count,
                ];
            });

        $latestLicenses = License::query()
            ->with('product:id,name', 'user:id,email')
            ->where('license_status', '1')
            ->orderByDesc('license_date')
            ->orderByDesc('id')
            ->take(10)
            ->get()
            ->map(fn (License $license) => (object) [
                'license_id' => $license->id,
                'client_id' => $license->user_id,
                'license_code' => $license->license_code,
                'license_date' => LicenseHelper::formatDatetime($license->license_date),
                'license_status' => $license->license_status,
                'product_title' => optional($license->product)->name,
                'product_id' => $license->product_id,
                'client_email' => optional($license->user)->email,
            ]);

        $expiringSupport = License::query()
            ->with('product:id,name', 'user:id,email')
            ->where('license_status', '1')
            ->where('license_support_date', '>', $currentDateTime)
            ->orderBy('license_support_date')
            ->orderBy('id')
            ->take(10)
            ->get()
            ->map(fn (License $license) => (object) [
                'license_id' => $license->id,
                'client_id' => $license->user_id,
                'license_code' => $license->license_code,
                'license_date' => LicenseHelper::formatDatetime($license->license_date),
                'license_support_date' => LicenseHelper::formatDate($license->license_support_date),
                'license_status' => $license->license_status,
                'product_title' => optional($license->product)->name,
                'product_id' => $license->product_id,
                'client_email' => optional($license->user)->email,
            ]);

        $expiringUpdates = License::query()
            ->with('product:id,name', 'user:id,email')
            ->where('license_status', '1')
            ->where('license_updates_date', '>', $currentDateTime)
            ->orderBy('license_updates_date')
            ->orderBy('id')
            ->take(10)
            ->get()
            ->map(fn (License $license) => (object) [
                'license_id' => $license->id,
                'client_id' => $license->user_id,
                'license_code' => $license->license_code,
                'license_date' => LicenseHelper::formatDatetime($license->license_date),
                'license_updates_date' => LicenseHelper::formatDate($license->license_updates_date),
                'license_status' => $license->license_status,
                'product_title' => optional($license->product)->name,
                'product_id' => $license->product_id,
                'client_email' => optional($license->user)->email,
            ]);

        return successResponse(Lang::get('lang.dashboard_show'), compact('productsCount', 'versionsCount', 'licenseCount', 'callbacksCount', 'latestProducts', 'latestVersions', 'latestInstallations', 'latestCallbacks', 'latestReports', 'expiredVersions', 'latestClients', 'latestLicenses', 'expiringSupport', 'expiringUpdates'));
    }
}
