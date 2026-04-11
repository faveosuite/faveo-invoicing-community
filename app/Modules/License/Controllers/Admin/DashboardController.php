<?php

namespace App\Modules\License\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\User;
use App\Modules\License\Models\License;
use App\Model\Product\Product;
use App\Modules\License\Models\ProductVersion;
use App\Modules\License\Models\Installation;
use App\Modules\License\Models\LicenseCallback;
use App\Modules\License\Models\VersionCallback;
use App\Modules\License\Models\LicenseReport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Carbon\Carbon;

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
        $latestProducts = Product::where('status', '1')
            ->orderBy('id','desc')
            ->take(10)
            ->get()
            ->map(function ($product) {
                $product->versions = DB::table('product_versions')
                    ->where('id', $product->id)
                    ->where('version_status', '1')
                    ->orderByDesc('version_date')
                    ->value('version_number');
                $product->versions_count = DB::table('product_versions')
                    ->where('id', $product->id)
                    ->count();
                return $product;
            });

        // Latest versions
        $latestVersions = ProductVersion::where('version_status', '1')->with('product:id,name')->distinct('version_number')->orderByDesc('version_date')->orderByDesc('id')->take(10)->get();

        // Latest installations (AFL and AFU combined)
        $latestInstallations = Installation::where('installation_status', '1')->orWhere('installation_status', '1')->distinct('installation_ip')->orderByDesc('installation_date')->orderByDesc('id')->take(10)->get();

        // Latest callbacks (AFL and AFU combined)
        $latestCallbacks = LicenseCallback::where('callback_status', '1')->orWhere('callback_status', '1')->distinct('callback_ip')->orderByDesc('callback_date_time')->orderByDesc('id')->take(10)->get();

        // Latest product reports
        $latestReports = LicenseReport::with('product:id,name','user:user_id,email')->where('report_status', '1')->orderByDesc('report_date_time')->take(10)->get();

        $currentDateTime = Carbon::now()->toDateTimeString();

        // Expired versions (versions with inactive status)
        $expiredVersions = ProductVersion::where('version_status', '!=', 'active')->take(10)->get();

        //Latest clients
        $latestClients = DB::table('users')
            ->select('id as client_id','email as client_email','created_at as client_active_date','active as client_status')
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
            ->select('licenses.id as license_id','licenses.user_id as client_id','licenses.id','licenses.license_code','licenses.license_date','licenses.license_status')
            ->leftJoin('products', 'licenses.id', '=', 'products.id')
            ->leftJoin('users', 'licenses.user_id', '=', 'users.id')
            ->selectRaw('licenses.id as license_id, licenses.user_id as client_id, licenses.id, licenses.license_code, licenses.license_date, licenses.license_status, products.name as name, users.email as client_email')
            ->where('licenses.license_status', '1')
            ->orderByDesc('licenses.license_date')
            ->orderByDesc('licenses.id')
            ->take(10)
            ->get();

        //Expiring support
        $expiringSupport = DB::table('licenses')
            ->selectRaw('licenses.id as license_id, licenses.user_id as client_id, licenses.id, licenses.license_code, licenses.license_date, licenses.license_support_date, licenses.license_status, products.name as name, users.email as client_email')
            ->leftJoin('products', 'licenses.id', '=', 'products.id')
            ->leftJoin('users', 'licenses.user_id', '=', 'users.id')
            ->where('licenses.license_status', 1)
            ->where('licenses.license_support_date', '>', $currentDateTime)
            ->orderBy('licenses.license_support_date')
            ->orderBy('licenses.id')
            ->take(10)
            ->get();

        //Expiring updates
        $expiringUpdates = DB::table('licenses')
            ->selectRaw('licenses.id as license_id, licenses.user_id as client_id, licenses.id, licenses.license_code, licenses.license_date, licenses.license_updates_date, licenses.license_status, products.name as name, users.email as client_email')
            ->leftJoin('products', 'licenses.id', '=', 'products.id')
            ->leftJoin('users', 'licenses.user_id', '=', 'users.id')
            ->where('licenses.license_status', 1)
            ->where('licenses.license_updates_date', '>', $currentDateTime)
            ->orderBy('licenses.license_updates_date')
            ->orderBy('licenses.id')
            ->take(10)
            ->get();

        return successResponse(Lang::get('lang.dashboard_show'), compact('productsCount', 'versionsCount','licenseCount', 'callbacksCount', 'latestProducts', 'latestVersions', 'latestInstallations', 'latestCallbacks', 'latestReports', 'expiredVersions','latestClients','latestLicenses','expiringSupport','expiringUpdates'));
    }
}

