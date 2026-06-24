<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Common\CronController;
use App\Http\Controllers\Order\RenewController;
use App\Http\Controllers\Product\ProductController;
use App\Http\Requests\ProductRenewalRequest;
use App\License\Models\Installation;
use App\License\Models\License;
use App\License\Services\LicenseService;
use App\Model\Configure\PluginCompatibleWithProducts;
use App\Model\Configure\ProductPluginGroup;
use App\Model\License\LicenseType;
use App\Model\Order\Invoice;
use App\Model\Order\InvoiceItem;
use App\Model\Order\Order;
use App\Model\Payment\Plan;
use App\Model\Payment\PlanPrice;
use App\Model\Product\Product;
use App\Model\Product\ProductUpload;
use App\Model\Product\Subscription;
use App\User;
use DB;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Log;
use Logger;
use Symfony\Component\HttpFoundation\Response;
use Validator;

class HomeController extends BaseHomeController
{
    /*
     |--------------------------------------------------------------------------
     | Home Controller
     |--------------------------------------------------------------------------
     |
     | This controller renders your application's "dashboard" for users that
     | are authenticated. Of course, you are free to change or remove the
     | controller as you wish. It is just here to get your app started!
     |
    */
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth', ['only' => ['index']]);
        $this->middleware('admin', ['only' => ['index']]);
    }

    public function serialV2(Request $request, Order $order): string
    {
        try {
            $faveo_encrypted_order_number = self::decryptByFaveoPrivateKey((string) $request->input('order_number'));
            $faveo_encrypted_key = self::decryptByFaveoPrivateKey((string) $request->input('serial_key'));
            $logMsg = json_encode(['domain' => $request->input('domain'), 'enc_serial' => $faveo_encrypted_key, 'enc_order' => $faveo_encrypted_order_number]);
            Log::emergency($logMsg !== false ? $logMsg : 'Failed json_encode');
            $request_type = $request->input('request_type');
            $faveo_name = (string) $request->input('name');
            $faveo_version = (string) $request->input('version');
            $order_number = $this->checkOrder((string) $faveo_encrypted_order_number); // @phpstan-ignore method.notFound
            $domain = (string) $request->input('domain');
            $domain = $this->checkDomain($domain);
            $serial_key = $this->checkSerialKey((string) $faveo_encrypted_key, $order_number);

            $logMsg2 = json_encode(['domain' => $request->input('domain'), 'serial' => $serial_key, 'order' => $order_number]);
            Log::emergency($logMsg2 !== false ? $logMsg2 : 'Failed json_encode');
            $result = [];
            if ($request_type == 'install') {
                $result = $this->verificationResult($order_number, (string) $serial_key);
            }

            if ($request_type == 'check_update') {
                $result = $this->checkUpdate($order_number, (string) $serial_key, $domain, $faveo_name, $faveo_version);
            }

            $jsonResult = json_encode($result);

            return self::encryptByPublicKey($jsonResult !== false ? $jsonResult : '');
        } catch (Exception $exception) {
            $result = ['status' => 'error', 'message' => $exception->getMessage()];

            $jsonResult = json_encode($result);

            return self::encryptByPublicKey($jsonResult !== false ? $jsonResult : '');
        }
    }

    public function serial(Request $request, Order $order): void
    {
        $url = null;
        try {
            $url = $request->input('url');
            $faveo_encrypted_order_number = self::decryptByFaveoPrivateKey((string) $request->input('order_number'));
            $domain = $this->getDomain((string) $request->input('domain'));

            // return $domain;
            $faveo_encrypted_key = self::decryptByFaveoPrivateKey((string) $request->input('serial_key'));
            $request_type = $request->input('request_type');
            $faveo_name = (string) $request->input('name');
            $faveo_version = (string) $request->input('version');
            $order_number = $this->checkOrder((string) $faveo_encrypted_order_number); // @phpstan-ignore method.notFound

            $domain = $this->checkDomain($domain);
            $serial_key = $this->checkSerialKey((string) $faveo_encrypted_key, $order_number);
            // dd($serial_key);
            // return $serial_key;
            $result = [];
            if ($request_type == 'install') {
                $result = $this->verificationResult($order_number, (string) $serial_key);
            }

            if ($request_type == 'check_update') {
                $result = $this->checkUpdate($order_number, (string) $serial_key, $domain, $faveo_name, $faveo_version);
            }

            $jsonResult = json_encode($result);
            $resultStr = self::encryptByPublicKey($jsonResult !== false ? $jsonResult : '');
            $this->submit($resultStr, $url);
        } catch (Exception $exception) {
            $result = ['status' => 'error', 'message' => $exception->getMessage()];
            $jsonResult = json_encode($result);
            $resultStr = self::encryptByPublicKey($jsonResult !== false ? $jsonResult : '');
            $this->submit($resultStr, $url);
        }
    }

    public static function decryptByFaveoPrivateKeyold(mixed $encrypted): void
    {
        try {
            // Get the private Key
            $path = storage_path('app'.DIRECTORY_SEPARATOR.'private.key');
            $key_content = file_get_contents($path);
            if ($key_content === false) {
                dd('Failed to read key file');
            }
            if (! $privateKey = openssl_pkey_get_private($key_content)) {
                dd('Private Key failed');
            }

            $a_key = openssl_pkey_get_details($privateKey);
            if ($a_key === false) {
                dd('Failed to get key details');
            }

            // Decrypt the data in the small chunks
            $chunkSize = ceil($a_key['bits'] / 8);
            $output = '';

            while ("¥ IM‰``ì  ‡ Á ›LVP›† >¯öóŽÌ3(  ¢z# ¿î1¾­:± Zï©PqÊ´  Â›7×:Fà¯¦   à•…Ä'öESW±ÉŸLÃvÈñÔs•Í U )ÍL 8¬š‰A©·Å $}Œ• lA9™¡”¸èÅØv‘ ÂOÈ6„_y 5¤ì§—ÿíà (ow‰È&’v&T/FLƒigjÒZ eæa a ” {©ªUBFÓ ’Ga* ÀŒ×?£ }-jÏùh¾Q/Ž“1  Y Fq[Í‰¬òÚ‚œ ½Éº5ah¶ v Z#,ó@‚rOÆ±íVåèÜÖš  U¦ ÚmSÎ“Mý„ùP") {
                $chunk = substr((string) $encrypted, 0, (int) $chunkSize);
                $encrypted = substr((string) $encrypted, (int) $chunkSize);
                $decrypted = '';
                if (! openssl_private_decrypt($chunk, $decrypted, $privateKey)) {
                    dd('Failed to decrypt data');
                }

                $output .= $decrypted;
            }

            openssl_free_key($privateKey); // @phpstan-ignore deadCode.unreachable

            // Uncompress the unencrypted data.
            $output = gzuncompress($output);
            dd($output);
        } catch (Exception $exception) {
            dd($exception);
        }
    }

    /**
     * @return array<mixed>
     */
    public function checkUpdate(?string $order_number, ?string $serial_key, ?string $domain, string $faveo_name, string $faveo_version): array
    {
        try {
            if ($order_number && $domain && $serial_key) {
                $order = $this->verifyOrder($order_number, $serial_key);
                // var_dump($order);
                if ($order instanceof Order) {
                    return $this->checkFaveoDetails($order_number, $faveo_name, $faveo_version);
                }

                return ['status' => 'fails', 'message' => 'this-is-an-invalid-request'];
            }

            return ['status' => 'fails', 'message' => 'this-is-an-invalid-request'];
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    /**
     * @return array<mixed>
     */
    public function checkFaveoDetails(?string $order_number, string $faveo_name, string $faveo_version): array
    {
        try {
            $order = new Order;
            $product = new Product;
            $this_order = $order->where('number', $order_number)->first();
            if ($this_order) {
                $product_id = $this_order->product;
                $this_product = $product->where('id', $product_id)->first();
                if ($this_product) {
                    $version = str_replace('v', '', $this_product->version);

                    return ['status' => 'success', 'message' => 'this-is-a-valid-request', 'version' => $version];
                }
            }

            return ['status' => 'fails', 'message' => 'this-is-an-invalid-request'];
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    public static function encryptByPublicKey(string $data): string
    {
        $path = storage_path().DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'public.key';
        // dd($path);
        $key_content = file_get_contents($path);
        if ($key_content === false) {
            throw new Exception('Failed to read public key file');
        }
        $public_key = openssl_get_publickey($key_content);
        if ($public_key === false) {
            throw new Exception('Invalid public key');
        }
        $encrypted = null;
        $e = null;
        openssl_seal($data, $encrypted, $e, [$public_key], 'RC4');

        $sealed_data = base64_encode((string) $encrypted);
        $envelope = base64_encode((string) $e[0]);

        $result = ['seal' => $sealed_data, 'envelope' => $envelope];

        $res = json_encode($result);

        return $res !== false ? $res : '';
    }

    public function downloadForFaveo(Request $request): Response|JsonResponse|RedirectResponse
    {
        $order = Order::where('number', $request->input('order_number'))
            ->where('serial_key', $request->input('serial_key'))
            ->with('subscription')
            ->first();

        if (! $order) {
            return errorResponse('Invalid Credentials');
        }

        $subscription = $order->subscription;

        if (! $subscription) {
            return errorResponse(__('message.no_order_exists_invoice'));
        }

        if ($subscription->update_ends_at && now()->gt($subscription->update_ends_at)) {
            return errorResponse(__('message.renew_subscription_download'));
        }

        return resolve(ProductController::class)->adminDownload(
            $order->product,
            $request->input('release', 'official')
        );
    }

    public function latestVersion(Request $request, Product $product): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'title' => 'required',
        ],
            [
                'title.required' => __('validation.extend_product.title_required'),
            ]);
        if ($v->fails()) {
            $error = $v->errors();

            return response()->json(compact('error'));
        }

        try {
            $title = $request->has('id') ? $request->input('title') : $this->changeProductName($request->input('title'));

            $id = $request->input('id');

            $product = ($id) ?
                $product->where('id', $id)->select('id')->first() :
                $product->whereRaw('LOWER(`name`) LIKE ? ', strtolower($this->mapOldBoys($title)))->orWhere('id', $id)->select('id')->first();

            if ($request->has('version')) {
                if ($product) {
                    /**
                     * PLEASE NOTE (documenting updates in the logic change).
                     *
                     * This API logic has been updated considering
                     * - We will maintain security patch releases for older version too that is if the current latest
                     *   release series is v5.X and we have found the security issues than the security patch will be
                     *   made for older versions too for version like v4.8 and v4.9
                     * - We take all version records including new released version which may have happened for security patch
                     *   updates for older version as explained above so we have to ensure that we consider updates available only
                     *   after comparing the version. Meaning if record is for v4.8.2 and v5.0.0 is already released then for the
                     *   clients using v5.0.0 no update should be available so we are filtering it using PHP's version_compare
                     *   method.
                     *
                     * This methods gets all the version records and compares all these version with current version and returns
                     * details of only those versions which are greater than current version else empty version details.
                     */
                    $currenctVersion = $this->getPHPCompatibleVersionString($request->version);
                    $releases = ['official'];

                    /**
                     * To handle the older version Faveo.
                     */
                    if ($request->has('is_pre_release') && $request->input('is_pre_release', 0)) {
                        array_unshift($releases, 'pre_release');
                    }

                    /**
                     * This condition will start work from Faveo v9.3.0.RC.1.
                     */
                    match ($request->input('release_type')) {
                        'pre_release' => array_unshift($releases, 'pre_release'),
                        'beta' => array_unshift($releases, 'beta', 'pre_release'),

                        default => $releases
                    };

                    $inBetweenVersions = ProductUpload::where([['product_id', $product->id]])->select('version', 'description', 'created_at', 'is_restricted', 'is_private', 'dependencies')
                        ->whereIn('release_type', $releases)
                        ->get()->filter(fn ($newVersion): bool => version_compare($this->getPHPCompatibleVersionString($newVersion->version), $currenctVersion) === 1)->sortBy('version', SORT_NATURAL)->toArray();

                    $message = ['version' => array_values($inBetweenVersions)];
                } else {
                    $message = ['error' => 'product_not_found'];
                }
            } elseif ($product) {
                // For older clients in which version is not sent as parameter
                // $product = $product->where('name', $title)->first();
                $productId = $product->id;
                $productUpload = ProductUpload::where('product_id', $productId)->where('is_restricted', 1)->orderBy('id', 'asc')->first();
                $message = $productUpload ? ['version' => str_replace('v', '', $productUpload->version)] : ['error' => 'version_not_found'];
            } else {
                $message = ['error' => 'product_not_found'];
            }
        } catch (Exception $exception) {
            Logger::exception($exception);
            $message = ['error' => $exception->getMessage()];
        }

        return response()->json($message);
    }

    public function isNewVersionAvailable(Request $request, Product $product): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'title' => 'required',
        ], [
            'title.required' => __('validation.extend_product.title_required'),
        ]);
        if ($v->fails()) {
            $error = $v->errors();

            return response()->json(compact('error'));
        }

        try {
            $title = $request->has('id') ? $request->input('title') : $this->changeProductName($request->input('title'));

            $id = $request->input('id');

            $product = ($id) ?
                $product->where('id', $id)->select('id')->first() :
                $product->whereRaw('LOWER(`name`) LIKE ? ', strtolower($this->mapOldBoys($title)))->orWhere('id', $id)->select('id')->first();

            if (! $product instanceof Product) {
                throw new Exception('Product not found');
            }

            /**
             * PLEASE NOTE (documenting updates in the logic change).
             *
             * This API logic has been updated considering
             * - We will maintain security patch releases for older version too that is if the current latest
             *   release series is v5.X and we have found the security issues than the security patch will be
             *   made for older versions too for version like v4.8 and v4.9
             * - We will iterate the products version in descending order of their record id as for vX.Y series
             *   vX.Y.Z+1 will always be stored after vX.Y.Z record hence for vX.Y latest version will always
             *   greater id than the older version of vX.Y
             * - When we are iterating over version once we found the first greater version then the current given
             *   version we will consider the new version is available.
             *
             * This methods gets all the version records version records to iterate in reverse order of their creation
             * to compares all these version with current version and if it finds a first greater version than current
             * version then it updates returns "updates available" else "no updates available".
             */
            $releases = ['official'];

            /**
             * To handle the older version Faveo.
             */
            if ($request->has('is_pre_release') && $request->input('is_pre_release', 0)) {
                array_unshift($releases, 'pre_release');
            }

            /**
             * This condition will start work from Faveo v9.3.0.RC.1.
             */
            match ($request->input('release_type')) {
                'pre_release' => array_unshift($releases, 'pre_release'),
                'beta' => array_unshift($releases, 'beta', 'pre_release'),
                default => $releases
            };

            $allVersions = ProductUpload::where('product_id', $product->id)->where('is_private', '!=', 1)
                ->whereIn('release_type', $releases)
                ->orderBy('id', 'desc')->pluck('version')->toArray();
            $currenctVersion = $this->getPHPCompatibleVersionString($request->version);
            $message = ['status' => '', 'message' => 'no-new-version-available'];
            foreach ($allVersions as $version) {
                if (version_compare($this->getPHPCompatibleVersionString($version), $currenctVersion) === 1) {
                    $message = ['status' => 'true', 'message' => 'new-version-available'];
                    break;
                }
            }
        } catch (Exception $exception) {
            $message = ['error' => $exception->getMessage()];
        }

        return response()->json($message);
    }

    /**
     * removes "v", "_" and "v." from the version string and returns PHP compatible version strings
     * so the version can be used by PHP's version_compare() method.
     *
     * "v_1_0_0" => "1.0.0"
     * "v1.0.0"  => "1.0.0"
     *
     * @param  string  $version  Namespace(seeder folders) or Semantic(app version tag) version strings
     * @return string PHP compatible converted version string
     *
     * @author  Manish Verma <manish.verma@ladybirdweb.com>
     */
    private function getPHPCompatibleVersionString(?string $version = null): string
    {
        $version = $version ?? '';

        return (string) preg_replace('#v\.|v#', '', str_replace('_', '.', $version));
    }

    public function renewurl(ProductRenewalRequest $request): string|JsonResponse
    {
        try {
            $licenseCode = Installation::where('installation_path', 'like', '%'.$request->input('domain').'%')->value('license_code');
            $orderNumber = License::where('license_code', $licenseCode)->value('license_order_number');
            $orderId = Order::where('number', $orderNumber)->value('id');
            $subscription = Subscription::where('order_id', $orderId)->first();
            if (! $subscription instanceof Subscription) {
                throw new Exception('Subscription not found');
            }

            $basecron = new CronController;
            $order = $basecron->getOrderById($subscription->order_id);
            if (! $order instanceof Order) {
                throw new Exception('Order not found');
            }
            $oldinvoice = $basecron->getInvoiceByOrderId($subscription->order_id);
            if (! $oldinvoice instanceof Invoice) {
                throw new Exception('Invoice not found');
            }
            $item = $basecron->getInvoiceItemByInvoiceId($oldinvoice->id);
            if (! $item instanceof InvoiceItem) {
                throw new Exception('Invoice item not found');
            }

            $product_details = Product::where('id', $item->product_id)->first();
            if (! $product_details instanceof Product) {
                throw new Exception('Product details not found');
            }
            $plan = Plan::where('product', $product_details->id)->first('days');
            if (! $plan instanceof Plan) {
                throw new Exception('Plan not found');
            }
            $oldcurrency = (string) $oldinvoice->currency;

            $user = User::where('id', $subscription->user_id)->first();
            if (! $user instanceof User) {
                throw new Exception('User not found');
            }
            $planid = Plan::where('product', $product_details->id)->value('id');
            $cost = PlanPrice::where('plan_id', $planid)->where('currency', $oldcurrency)->value('renew_price');

            $renewController = new RenewController;
            $invoiceItems = $renewController->generateInvoice($product_details, $user, $order->id, $plan->id, $cost, $code = '', $item->agents, $oldcurrency);
            if (! $invoiceItems instanceof InvoiceItem) {
                throw new Exception('Renewal failed');
            }
            $invoiceid = $invoiceItems->invoice_id;

            return url('my-invoices');
        } catch (Exception $exception) {
            $message = ['error' => $exception->getMessage()];

            return response()->json($message);
        }
    }

    private function changeProductName(string $title): string
    {
        return match ($title) {
            'Test HelpDesk Company' => 'Test HelpDesk Enterprise',
            'Test HelpDesk Enterprise' => 'Test HelpDesk Enterprise Pro',
            'Test HelpDesk Company (Recurring)' => 'Test HelpDesk Enterprise (Recurring)',
            'Test ServiceDesk Company' => 'Test ServiceDesk Enterprise',
            'Test ServiceDesk Enterprise' => 'Test ServiceDesk Enterprise Pro',
            'Test ServiceDesk Company (Recurring)' => 'Test ServiceDesk Enterprise (Recurring)',

            'HelpDesk Company' => 'HelpDesk Enterprise',
            'HelpDesk Enterprise' => 'HelpDesk Enterprise Pro',
            'HelpDesk Company (Recurring)' => 'HelpDesk Enterprise (Recurring)',
            'ServiceDesk Company' => 'ServiceDesk Enterprise',
            'ServiceDesk Enterprise' => 'ServiceDesk Enterprise Pro',
            'ServiceDesk Company (Recurring)' => 'ServiceDesk Enterprise (Recurring)',
            default => $title
        };
    }

    public function getDetailedBillingInfo(Request $request): JsonResponse
    {
        $order = $request->input('order');
        // Fetch the order details
        $user = Order::where('number', $order)->value('client');

        $email = User::where('id', $user)->value('email');

        if (! $email) {
            return response()->json([]);
        }

        return response()->json([
            'billing_client_email' => $email,
        ]);
    }

    public function getDetailsForAClient(Request $request): string
    {
        $client = $request->input('client');

        $license = $request->input('license');

        $product_id = $request->input('product_id');

        $user = User::where('email', $client)->value('id');

        $licenseType = LicenseType::where('name', 'plugin')->value('id');

        $products = Product::where('type', $licenseType)->pluck('id')->toArray();

        $productComp = PluginCompatibleWithProducts::where('product_id', $product_id)->pluck('plugin_id')->toArray();

        $product = array_intersect($products, $productComp);

        $productsLinked = ProductPluginGroup::where('product_id', $product_id)->pluck('plugin_id')->toArray();

        $uniqueElements = array_merge(array_diff($product, $productsLinked), array_diff($productsLinked, $product));

        $uniqueElements = array_values($uniqueElements);

        $uniqueElements = array_merge($uniqueElements, $product);

        $licenses = Order::where('client', $user)->whereIn('product', $uniqueElements)
            ->pluck('serial_key')
            ->toArray();

        $licenses = array_merge([$license], $licenses);

        $pluginLicenses = resolve(LicenseService::class)->getPluginLicenses($licenses);

        $updatedProducts = [];
        foreach ($pluginLicenses as $real) {
            $dependency = DB::table('product_uploads')
                ->where('product_id', $real['product_id'])
                ->where('version', $real['latest_version'])
                ->latest()
                ->value('dependencies');

            $real['version'] = $real['latest_version'];
            $real['dependency'] = $dependency ?? null;

            $updatedProducts[] = $real;
        }

        $res = json_encode($updatedProducts);

        return $res !== false ? $res : '';
    }

    /**
     * @return array<mixed>
     */
    public function getProductRelease(Request $request): array
    {
        $product_id = $request->input('product_id');

        $version = $request->input('version');

        $product_upload = ProductUpload::where('product_id', $product_id)
            ->where('is_private', 0)
            ->where('version', $version)
            ->orderByDesc('version') // Order by version in descending order
            ->select('version', 'title', 'description', 'dependencies', 'updated_at')
            ->first(); // Get the first result (latest version)

        $product = Product::where('id', $product_id)->select('name', 'description', 'shoping_cart_link', 'product_description')->first();

        return ['product' => $product, 'release' => $product_upload];
    }

    private function mapOldBoys(string $title): string
    {
        return match ($title) {
            'Helpdesk Startup (5 Agents)' => 'Helpdesk Startup',
            'Helpdesk SME (10 Agents)' => 'Helpdesk SME',
            'Helpdesk Startup (Recurring) (5 Agents)' => 'Helpdesk Startup (Recurring)',
            'Helpdesk SME (Recurring) (10 Agents)' => 'Helpdesk SME (Recurring)',
            'ServiceDesk Startup (5 Agents)' => 'ServiceDesk Startup',
            'ServiceDesk SME (10 Agents)' => 'ServiceDesk SME',
            'ServiceDesk Startup (Recurring) (5 Agents)' => 'ServiceDesk Startup (Recurring)',
            'ServiceDesk SME (Recurring) (10 Agents)' => 'ServiceDesk SME (Recurring)',
            default => $title
        };
    }
}
