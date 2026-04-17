<?php

namespace App\License\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\License\Models\Installation;
use App\License\Models\License;
use App\License\Models\LicenseCallback;
use App\License\Models\LicenseReport;
use App\License\Models\ProductVersion;
use App\License\Models\VersionCallback;
use App\Model\Product\Product;
use Carbon\Carbon;
use Illuminate\Support\Facades\Lang;

class DashboardController extends Controller
{
    public function dashboard()
    {
        $productsCount = Product::count();
        $versionsCount = ProductVersion::distinct('version_number')->count('version_number');
        $licenseCount = License::count();
        $callbacksCount = LicenseCallback::count() + VersionCallback::count();

        $latestProducts = Product::query()
            ->select('id', 'name', 'status')
            ->where('status', '1')
            ->withCount(['versions', 'installations', 'licenses'])
            ->latest('id')
            ->take(10)
            ->get()
            ->map(function (Product $product) {
                return (object) [
                    'id' => $product->id,
                    'product_title' => $product->name,
                    'product_status' => $product->status,
                    'versions' => $product->versions_count,
                    'installations_count' => $product->installations_count,
                    'licenses_count' => $product->licenses_count,
                ];
            });

        $latestVersions = ProductVersion::query()
            ->with('product:id,name')
            ->where('version_status', '1')
            ->latest('id')
            ->take(10)
            ->get()
            ->map(function (ProductVersion $version) {
                return (object) [
                    'id' => $version->id,
                    'product_id' => $version->product_id,
                    'version_number' => $version->version_number,
                    'version_date' => $version->version_date,
                    'version_status' => $version->version_status,
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
                    'installation_date' => $installation->installation_date,
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
            ->get();

        $latestReports = LicenseReport::with('product:id,name', 'user:id,email')
            ->where('report_status', '1')
            ->orderByDesc('report_date_time')
            ->take(10)
            ->get();

        $currentDateTime = Carbon::now()->toDateTimeString();

        $expiredVersions = ProductVersion::query()
            ->select('id', 'version_number', 'version_date', 'version_expire_date', 'version_status')
            ->where(function ($query) {
                $query->whereNotNull('version_expire_date')
                    ->orWhereNotIn('version_status', ['1', 'active']);
            })
            ->latest('id')
            ->take(10)
            ->get();

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
                    'client_active_date' => $user->client_active_date,
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
                'license_date' => $license->license_date,
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
                'license_date' => $license->license_date,
                'license_support_date' => $license->license_support_date,
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
                'license_date' => $license->license_date,
                'license_updates_date' => $license->license_updates_date,
                'license_status' => $license->license_status,
                'product_title' => optional($license->product)->name,
                'product_id' => $license->product_id,
                'client_email' => optional($license->user)->email,
            ]);

        return successResponse(Lang::get('lang.dashboard_show'), compact('productsCount', 'versionsCount', 'licenseCount', 'callbacksCount', 'latestProducts', 'latestVersions', 'latestInstallations', 'latestCallbacks', 'latestReports', 'expiredVersions', 'latestClients', 'latestLicenses', 'expiringSupport', 'expiringUpdates'));
    }
}
