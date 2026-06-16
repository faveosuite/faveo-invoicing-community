<?php

namespace Database\Seeders\v4_0_2_5_RC_2;

use File;
use DB;
use App\Model\Common\FaveoCloud;
use App\Model\Order\InstallationDetail;
use App\Model\Product\Subscription;
use App\ThirdPartyApp;
use GuzzleHttp\Client;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->packageRemoval();
        $this->domaincheck();
        $this->domainDelete();
    }

    public function packageRemoval()
    {
        $paths = [
            base_path('vendor' . DIRECTORY_SEPARATOR . 'arcanedev'),
            base_path('vendor' . DIRECTORY_SEPARATOR . 'shvetsgroup'),
            config_path('log-viewer.php')
        ];


        foreach ($paths as $path) {
            if (file_exists($path)) {
                if (is_dir($path)) {
                    $this->deleteDirectory($path);
                } else {
                    @unlink($path);
                }
            }
        }
    }

    private function deleteDirectory($dir)
    {
        if (!is_dir($dir)) {
            return;
        }

        $items = scandir($dir);
        if ($items === false) {
            return;
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $dir . DIRECTORY_SEPARATOR . $item;
            if (is_dir($path)) {
                $this->deleteDirectory($path);
            } else {
                @unlink($path);
            }
        }

        @rmdir($dir);
    }


    public function domaincheck()
    {

        $env = base_path('.env');
        if (File::exists($env) && (env('DB_INSTALL') == 1)) {
            $keys = ThirdPartyApp::where('app_name', 'faveo_app_key')->select('app_key', 'app_secret')->first();

            if (is_null($keys)) {//Valdidate if the app key to be sent is valid or not
                return;
            }

            $client = new Client();
            $cloud = new FaveoCloud();
            $response = $client->request(
                'GET',
                $cloud->first()->cloud_central_domain . '/tenants',
                [
                    'query' => [
                        'key' => $keys->app_key,
                    ],
                ]
            );

            $responseBody = (string)$response->getBody();
            $responseData = json_decode($responseBody);

            $collection = collect($responseData->message)->reject(fn($item) => $item === null);

            $allowedDomains = $collection->pluck('domain')->toArray();


//            foreach ($allowedDomains as $domain) {
//
//                $installationDetails = InstallationDetail::where('installation_path', $domain)->get();
//
//                $orderIds = $installationDetails->pluck('order_id')->filter()->toArray();
//
//                if (empty($orderIds)) continue;
//
//                $subscriptions = Subscription::whereIn('order_id', $orderIds)->get();
//
//                if ($subscriptions->isEmpty()) continue;
//
//                $latest = $subscriptions->sortByDesc('ends_at')->first();
//
//                Subscription::whereIn('order_id', $orderIds)
//                    ->where('id', '!=', $latest->id)
//                    ->update(['is_deleted' => 1]);
//
//            }
            array_map(function ($domain) {
                $installationDetails = InstallationDetail::where('installation_path', $domain)->get();

                $orderIds = $installationDetails->pluck('order_id')->filter()->toArray();
                if (empty($orderIds)) return null;

                $subscriptions = Subscription::whereIn('order_id', $orderIds)->get();
                if ($subscriptions->isEmpty()) return null;

                $latest = $subscriptions->sortByDesc('ends_at')->first();

                Subscription::whereIn('order_id', $orderIds)
                    ->where('id', '!=', $latest->id)
                    ->update(['is_deleted' => 1]);

            }, $allowedDomains);
        }
    }


    public function domainDelete()
    {
        $env = base_path('.env');
        if (File::exists($env) && (env('DB_INSTALL') == 1)) {
            $keys = ThirdPartyApp::where('app_name', 'faveo_app_key')->select('app_key', 'app_secret')->first();

            if (is_null($keys)) {//Valdidate if the app key to be sent is valid or not
                return;
            }

            $client = new Client();
            $cloud = new FaveoCloud();
            $response = $client->request(
                'GET',
                $cloud->first()->cloud_central_domain . '/tenants',
                [
                    'query' => [
                        'key' => $keys->app_key,
                    ],
                ]
            );

            $responseBody = (string)$response->getBody();
            $responseData = json_decode($responseBody);

            $collection = collect($responseData->message)->reject(fn($item) => $item === null);

            $allowedDomains = $collection->pluck('domain')->toArray();
            $cloudProductIds = cloudPopupProducts();

            DB::transaction(function () use ($allowedDomains, $cloudProductIds): void {

                $otherOrders = DB::table("installation_details")
                    ->whereNotIn("installation_path", $allowedDomains)
                    ->pluck("order_id");

                if ($otherOrders->isEmpty()) {
                    return;
                }

                $updated = DB::table("subscriptions")
                    ->whereIn("order_id", $otherOrders)
                    ->whereIn("product_id", $cloudProductIds)
                    ->update(["is_deleted" => 1]);

            });
        }
    }
}