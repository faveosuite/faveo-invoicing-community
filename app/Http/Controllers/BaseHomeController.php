<?php

namespace App\Http\Controllers;

use App\License\Models\Installation;
use App\License\Services\InstallationService;
use App\License\Services\LicenseService;
use App\Model\Order\Invoice;
use App\Model\Order\Order;
use App\Model\Payment\Plan;
use App\Model\Product\Product;
use App\Model\Product\Subscription;
use Crypt;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Lang;

class BaseHomeController extends Controller
{
    public static function decryptByFaveoPrivateKey(string $encrypted): ?string
    {
        $encrypted = json_decode((string) $encrypted);
        $sealed_data = $encrypted->seal;
        $envelope = $encrypted->envelope;
        $input = base64_decode((string) $sealed_data);
        $einput = base64_decode((string) $envelope);
        $path = storage_path('app'.DIRECTORY_SEPARATOR.'private.key');
        $key_content = file_get_contents($path);
        $private_key = openssl_get_privatekey($key_content);
        $plaintext = null;
        openssl_open($input, $plaintext, $einput, $private_key, 'RC4');

        return $plaintext;
    }

    public function getTotalSales(): float|int
    {
        $invoice = new Invoice();
        $total = $invoice->pluck('grand_total')->all();

        return array_sum($total);
    }

    public function checkDomain(string $request_url): ?string
    {
        try {
            $order = new Order();
            $this_order = $order->where('domain', $request_url)->first();
            if (! $this_order) {
                return null;
            }

            return $this_order->domain;
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    public function checkSerialKey(string $faveo_encrypted_key, string $order_number): ?string
    {
        try {
            $order = new Order();
            //$faveo_decrypted_key = self::decryptByFaveoPrivateKey($faveo_encrypted_key);
            $this_order = $order->where('number', $order_number)->first();
            if (! $this_order) {
                return null;
            }

            if ($this_order->serial_key == $faveo_encrypted_key) {
                return $this_order->serial_key;
            }

            return null;
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    public function verifyOrder(string $order_number, string $serial_key): ?\App\Model\Order\Order
    {
        // if (ends_with($domain, '/')) {
        //     $domain = substr_replace($domain, '', -1, 1);
        // }
        //dd($domain);
        try {
            $order = new Order();

            return $order
                    ->where('number', $order_number)
                    //->where('serial_key', $serial_key)
                    // ->where('domain', $domain)
                    ->first();
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    public function index(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
    {
        $this->getTotalSales();

        return view('themes.default1.common.dashboard'); // @phpstan-ignore argument.type
    }

    public function getDomain(string $url): string
    {
        $pieces = parse_url((string) $url);
        $domain = $pieces['host'] ?? '';
        if (preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain, $regs)) {
            return $regs['domain'];
        }

        return $domain;
    }

    public function verificationResult(string $order_number, string $serial_key): array
    {
        try {
            if ($order_number && $serial_key) {
                $order = $this->verifyOrder($order_number, $serial_key);
                if ($order) {
                    return ['status' => 'success', 'message' => 'this-is-a-valid-request',
                        'order_number' => $order_number, 'serial' => $serial_key, ];
                }

                return ['status' => 'fails', 'message' => 'this-is-an-invalid-request'];
            }

            return ['status' => 'fails', 'message' => 'this-is-an-invalid-request'];
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    public function getEncryptedData(Request $request): \Illuminate\Http\JsonResponse
    {
        $enc = $request->input('en');
        $result = self::decryptByFaveoPrivateKey($enc);

        return response()->json($result);
    }

    public function checkUpdatesExpiry(Request $request): array
    {
        // $v = \Validator::make($request->all(), [
        //     'order_number' => 'required',
        // ]);
        // if ($v->fails()) {
        //     $error = $v->errors();

        //     return response()->json(compact('error'));
        // }
        try {
            $order_number = $request->input('order_number');
            $licenseCode = $request->input('license_code');
            if ($order_number) {
                $orderId = Order::where('number', 'LIKE', $order_number)->value('id');
                if ($orderId) {
                    $expiryDate = Subscription::where('order_id', $orderId)->value('update_ends_at');
                    $subscription = Subscription::where('order_id', $orderId)->select('id', 'support_ends_at', 'version', 'update_ends_at', 'product_id', 'plan_id', 'ends_at')->first();
                    $data = $this->getData($subscription);
                    if (Date::now()->toDateTimeString() < $expiryDate) {
                        return ['status' => 'success', 'message' => 'New version available', 'data' => $data];
                    }
                }
            } elseif ($licenseCode) {
                $orderForLicense = Order::all()->filter(function ($order) use ($licenseCode) { // @phpstan-ignore argument.type
                    if ($order->serial_key == $licenseCode) {
                        return $order;
                    }
                });
                if (count($orderForLicense) > 0) {
                    $expiryDate = Subscription::where('order_id', $orderForLicense->first()->id)->value('update_ends_at');
                    $subscription = Subscription::where('order_id', $orderForLicense->first()->id)->select('id', 'support_ends_at', 'version', 'update_ends_at', 'product_id', 'plan_id', 'ends_at')->first();
                    $data = $this->getData($subscription);
                    if (Date::now()->toDateTimeString() < $expiryDate) {
                        return ['status' => 'success', 'message' => 'New version available', 'data' => $data];
                    }
                }
            }

            return ['status' => 'fails', 'message' => 'do-not-allow-auto-update'];
        } catch (Exception $exception) {
            return ['status' => 'fails', 'error' => $exception->getMessage()];
        }
    }

    public function getData(\App\Model\Product\Subscription $subscription): ?array
    {
        $productName = Product::where('id', $subscription->product_id)->value('name');
        $plan = Plan::where('id', $subscription->plan_id)->value('name');
        if (Date::now()->toDateTimeString() < $subscription->update_ends_at) {
            return [
                'product' => $productName,
                'plan' => $plan,
                'update_ends' => $subscription->update_ends_at,
                'version' => $subscription->version,
                'support_end' => $subscription->support_ends_at,
                'license_end' => $subscription->ends_at,
            ];
        }

        return null;
    }

    public function updateLatestVersion(Request $request): array
    {
        try {
            $orderId = null;
            $url = $request->url;
            $ip = $this->getUserIP();
            if ($url) {
                $url = getRootUrl($url.'/', 1, 1, 0, 1);
            }

            $licenseCode = $request->input('licenseCode');
            $orderForLicense = Order::all()->filter(function ($order) use ($licenseCode) { // @phpstan-ignore argument.type
                if ($order->serial_key == $licenseCode) {
                    return $order;
                }
            });
            if (count($orderForLicense) > 0) {
                $order = $orderForLicense->first();
                if ($url) {
                    Installation::where('license_code', $licenseCode)
                        ->where('installation_ip', $ip)
                        ->update([
                            'installation_path' => $url,
                            'version' => $request->input('version'),
                        ]);

                    $existingVersion = Subscription::where('order_id', $order->id)->value('version');
                    if ($existingVersion && $existingVersion < $request->input('version')) {
                        $existingVersion = $request->input('version');
                    }

                    resolve(InstallationService::class)->updateLogs([
                        'license_code' => $licenseCode, 'root_url' => $url,
                        'version_number' => $request->input('version'), 'installation_ip' => $ip,
                    ]);
                    Subscription::where('order_id', $order->id)->update(['version' => $existingVersion, 'version_updated_at' => (string) Date::now()]);

                    return ['status' => 'success', 'message' => 'version-updated-successfully'];
                }

                // Older clients that don't send URL: update version on all installations for this license
                Installation::where('license_code', $licenseCode)
                    ->update(['version' => $request->input('version')]);
                $existingVersion = Subscription::where('order_id', $order->id)->value('version');
                if ($existingVersion && $request->input('version') > $existingVersion) {
                    Subscription::where('order_id', $order->id)->update(['version' => $request->input('version')]);
                }

                return ['status' => 'success', 'message' => 'version-updated-successfully'];
            }

            return ['status' => 'fails', 'message' => 'version-not updated'];
        } catch (Exception $exception) {
            return ['status' => 'fails', 'error' => $exception->getMessage()];
        }
    }

    public function getUserIP(): ?string
    {
        $client = @\Illuminate\Support\Facades\Request::server('HTTP_CLIENT_IP');
        $forward = @\Illuminate\Support\Facades\Request::server('HTTP_X_FORWARDED_FOR');
        $remote = \Illuminate\Support\Facades\Request::server('REMOTE_ADDR');

        if (filter_var($client, FILTER_VALIDATE_IP)) {
            $ip = $client;
        } elseif (filter_var($forward, FILTER_VALIDATE_IP)) {
            $ip = $forward;
        } else {
            $ip = $remote;
        }

        return $ip;
    }

    public function updateLicenseCode(Request $request): ?\Illuminate\Http\JsonResponse
    {
        try {
            $licCode = $request->input('licenseCode'); //The license code already existing for older client
            $lastFour = $this->getLastFourDigistsOfLicenseCode($request->input('product'));
            $existingLicense = Order::select('id', 'client', 'product', 'serial_key')->get()
                ->filter(fn ($order): bool => $order->serial_key == $licCode)->first();

            if ($existingLicense) {//If the license code that is sent in the request exists in billing
                resolve(InstallationService::class)->deleteByLicenseCode($licCode); //Delete the installations for the current license before updating license so that no Faveo installation exists on the user domain/IP path and the install slots are freed

                $serial_key = substr((string) $licCode, 0, 12).$lastFour; //The new License Code
                //Create new license in license manager with the new license code which has no. of agents in the last 4 digits.
                $order = Order::find($existingLicense->id);
                $ipAndDomain = LicenseService::parseIpAndDomain($order->domain ?? '');
                $licExpiry = $this->getLicenseExpiryDate($existingLicense);
                $updExpiry = $this->getUpdatesExpiryDate($existingLicense);
                $supExpiry = $this->getSupportExpiryDate($existingLicense);
                resolve(LicenseService::class)->create([
                    'product_id' => $existingLicense->product,
                    'user_id' => $existingLicense->client,
                    'license_code' => $serial_key,
                    'license_order_number' => $order->number ?? null,
                    'license_domain' => $ipAndDomain['domain'],
                    'license_ip' => $ipAndDomain['ip'],
                    'license_require_domain' => $ipAndDomain['requireDomain'],
                    'license_limit' => 1,
                    'license_expire_date' => ($licExpiry != '') ? $licExpiry->toDateString() : null,
                    'license_updates_date' => ($updExpiry != '') ? $updExpiry->toDateString() : null,
                    'license_support_date' => ($supExpiry != '') ? $supExpiry->toDateString() : null,
                    'license_status' => 1,
                ]);
                //Update the old license code with new one in billing.
                $existingLicense->serial_key = Crypt::encrypt(substr((string) $licCode, 0, 12).$lastFour);
                $existingLicense->save();
                //send the newly updated license code in response
                $result = ['status' => 'success', 'updatedLicenseCode' => $existingLicense->serial_key];

                return response()->json($result);
            }
        } catch (Exception $exception) {
            $result = ['status' => 'fails', 'error' => $exception->getMessage()];

            return response()->json($result);
        }

        return null;
    }

    public function getLastFourDigistsOfLicenseCode(string $productName): string
    {
        return match (true) {
            strpos((string) $productName, 'Enterprise') > 0, strpos((string) $productName, 'Company') > 0 => '0000',
            strpos((string) $productName, 'Freelancer') > 0 => '0002',
            strpos((string) $productName, 'Startup') > 0 => '0005',
            strpos((string) $productName, 'SME') > 0 => '0010',
            default => throw new Exception(Lang::get('message.product_not_found')),
        };
    }

    public function getUpdatesExpiryDate(\App\Model\Order\Order $existingLicense): \Illuminate\Support\Carbon|string
    {
        $updatesDate = Date::parse(Subscription::where('order_id', $existingLicense->id)->value('update_ends_at'));
        if (strtotime($updatesDate) < 0) {
            return '';
        }

        return $updatesDate;
    }

    public function getLicenseExpiryDate(\App\Model\Order\Order $existingLicense): \Illuminate\Support\Carbon|string
    {
        $licenseDate = Date::parse(Subscription::where('order_id', $existingLicense->id)->value('ends_at'));
        if (strtotime($licenseDate) < 0) {
            return '';
        }

        return $licenseDate;
    }

    public function getSupportExpiryDate(\App\Model\Order\Order $existingLicense): \Illuminate\Support\Carbon|string
    {
        $supportDate = Date::parse(Subscription::where('order_id', $existingLicense->id)->value('support_ends_at'));
        if (strtotime($supportDate) < 0) {
            return '';
        }

        return $supportDate;
    }
}
