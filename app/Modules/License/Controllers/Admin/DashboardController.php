<?php

namespace App\Modules\License\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Model\Product\Product;
use App\Modules\License\Models\License;
use App\Modules\License\Models\LicenseCallback;
use App\Modules\License\Models\LicenseReport;
use App\Modules\License\Models\ProductVersion;
use App\Modules\License\Models\VersionCallback;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;

class DashboardController extends Controller
{
    public function dashboard()
    {
        // Count of active products
        $productsCount = Product::count();

        // Count of distinct active version numbers
        $versionsCount = ProductVersion::distinct('version_number')->count('version_number');

        //Count of active license
        $licenseCount = License::count();

        // Count of distinct callbacks
        $callbacksCount = bcadd(LicenseCallback::count(), VersionCallback::count());

        // Latest products
        $latestProducts = DB::table('products')
            ->where('status', '1')
            ->orderBy('id', 'desc')
            ->take(10)
            ->get()
            ->map(function ($product) {
                $product->product_title = $product->name;
                $product->product_status = $product->status;

                $product->versions = DB::table('product_versions')
                    ->where('product_id', $product->id)
                    ->count();

                $product->installations_count = DB::table('installations')
                    ->where('product_id', $product->id)
                    ->count();
                $product->licenses_count = DB::table('licenses')
                    ->where('product_id', $product->id)
                    ->count();

                return $product;
            });

        // Latest versions
        $latestVersions = DB::table('product_versions')
            ->selectRaw('product_versions.id, product_versions.product_id, product_versions.version_number, product_versions.version_date, product_versions.version_status, products.name as product_title')
            ->leftJoin('products', 'product_versions.product_id', '=', 'products.id')
            ->where('product_versions.version_status', '1')
            ->orderByDesc('product_versions.id')
            ->take(10)
            ->get();

        // Latest installations (AFL and AFU combined)
        $latestInstallations = DB::table('installations')
            ->selectRaw('installations.id, installations.license_code, installations.installation_ip, installations.installation_domain, installations.installation_date, installations.installation_status, licenses.id as license_id')
            ->leftJoin('licenses', 'installations.license_code', '=', 'licenses.license_code')
            ->where('installations.installation_status', '1')
            ->orderByDesc('installations.installation_date')
            ->orderByDesc('installations.id')
            ->take(10)
            ->get();

        // Latest callbacks (AFL and AFU combined)
        $latestCallbacks = LicenseCallback::where('callback_status', '1')->distinct('callback_ip')->orderByDesc('callback_date_time')->orderByDesc('id')->take(10)->get();

        // Latest product reports
        $latestReports = LicenseReport::with('product:id,name', 'user:id,email')->where('report_status', '1')->orderByDesc('report_date_time')->take(10)->get();

        $currentDateTime = Carbon::now()->toDateTimeString();

        // Expired versions (versions with expire date set or inactive status)
        $expiredVersions = DB::table('product_versions')
            ->select('id', 'version_number', 'version_date', 'version_expire_date', 'version_status')
            ->where(function ($query) {
                $query->whereNotNull('version_expire_date')
                    ->orWhereNotIn('version_status', ['1', 'active']);
            })
            ->orderByDesc('id')
            ->take(10)
            ->get();

        //Latest clients
        $latestClients = DB::table('users')
            ->selectRaw('id as client_id, CONCAT(first_name, " ", last_name) as full_name, email as client_email, created_at as client_active_date, active as client_status')
            ->selectSub(function ($query) {
                $query->selectRaw('COUNT(*)')
                    ->from('licenses')
                    ->whereColumn('licenses.user_id', 'users.id');
            }, 'license_count')
            ->where('active', '1')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->take(10)
            ->get();

        //Latest licenses
        $latestLicenses = DB::table('licenses')
            ->selectRaw('licenses.id as license_id, licenses.user_id as client_id, licenses.license_code, licenses.license_date, licenses.license_status, products.name as product_title, products.id as product_id, users.email as client_email')
            ->leftJoin('products', 'licenses.product_id', '=', 'products.id')
            ->leftJoin('users', 'licenses.user_id', '=', 'users.id')
            ->where('licenses.license_status', '1')
            ->orderByDesc('licenses.license_date')
            ->orderByDesc('licenses.id')
            ->take(10)
            ->get();

        //Expiring support
        $expiringSupport = DB::table('licenses')
            ->selectRaw('licenses.id as license_id, licenses.user_id as client_id, licenses.license_code, licenses.license_date, licenses.license_support_date, licenses.license_status, products.name as product_title, products.id as product_id, users.email as client_email')
            ->leftJoin('products', 'licenses.product_id', '=', 'products.id')
            ->leftJoin('users', 'licenses.user_id', '=', 'users.id')
            ->where('licenses.license_status', 1)
            ->where('licenses.license_support_date', '>', $currentDateTime)
            ->orderBy('licenses.license_support_date')
            ->orderBy('licenses.id')
            ->take(10)
            ->get();

        //Expiring updates
        $expiringUpdates = DB::table('licenses')
            ->selectRaw('licenses.id as license_id, licenses.user_id as client_id, licenses.license_code, licenses.license_date, licenses.license_updates_date, licenses.license_status, products.name as product_title, products.id as product_id, users.email as client_email')
            ->leftJoin('products', 'licenses.product_id', '=', 'products.id')
            ->leftJoin('users', 'licenses.user_id', '=', 'users.id')
            ->where('licenses.license_status', 1)
            ->where('licenses.license_updates_date', '>', $currentDateTime)
            ->orderBy('licenses.license_updates_date')
            ->orderBy('licenses.id')
            ->take(10)
            ->get();

        return successResponse(Lang::get('lang.dashboard_show'), compact('productsCount', 'versionsCount', 'licenseCount', 'callbacksCount', 'latestProducts', 'latestVersions', 'latestInstallations', 'latestCallbacks', 'latestReports', 'expiredVersions', 'latestClients', 'latestLicenses', 'expiringSupport', 'expiringUpdates'));
    }
}
