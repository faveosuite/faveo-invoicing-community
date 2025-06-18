<?php

namespace App\Http\Controllers;

use App\ApiKey;
use App\Http\Controllers\Common\CronController;
use App\Http\Controllers\Order\RenewController;
use App\Http\Requests\ProductRenewalRequest;
use App\Model\Common\Country;
use App\Model\Configure\PluginCompatibleWithProducts;
use App\Model\Configure\ProductPluginGroup;
use App\Model\License\LicenseType;
use App\Model\Order\InstallationDetail;
use App\Model\Order\Order;
use App\Model\Payment\Plan;
use App\Model\Payment\PlanPrice;
use App\Model\Product\Product;
use App\Model\Product\ProductGroup;
use App\Model\Product\ProductUpload;
use App\Model\Product\Subscription;
use App\User;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

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
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth', ['only' => ['index']]);
        $this->middleware('admin', ['only' => ['index']]);
    }

    public function getVersion(Request $request, Product $product)
    {
        $this->validate($request, [
            'title' => 'required',
        ],
            [
                'title.required' => __('validation.extend_product.title_required'),
            ]);
        $title = $request->input('title');
        $product = $product->where('name', $title)->first();
        if ($product) {
            $version = $product->version;
        } else {
            return json_encode(['message' => 'Product not found']);
        }

        return str_replace('v', '', $product->version);
    }

    public function serialV2(Request $request, Order $order)
    {
        try {
            $faveo_encrypted_order_number = self::decryptByFaveoPrivateKey($request->input('order_number'));
            $faveo_encrypted_key = self::decryptByFaveoPrivateKey($request->input('serial_key'));
            \Log::emergency(json_encode(['domain' => $request
                    ->input('domain'), 'enc_serial' => $faveo_encrypted_key,
                'enc_order' => $faveo_encrypted_order_number, ]));
            $request_type = $request->input('request_type');
            $faveo_name = $request->input('name');
            $faveo_version = $request->input('version');
            $order_number = $this->checkOrder($faveo_encrypted_order_number);
            $domain = $request->input('domain');
            $domain = $this->checkDomain($domain);
            $serial_key = $this->checkSerialKey($faveo_encrypted_key, $order_number);

            \Log::emergency(json_encode(['domain' => $request->input('domain'),
                'serial' => $serial_key, 'order' => $order_number, ]));
            $result = [];
            if ($request_type == 'install') {
                $result = $this->verificationResult($order_number, $serial_key);
            }
            if ($request_type == 'check_update') {
                $result = $this->checkUpdate($order_number, $serial_key, $domain, $faveo_name, $faveo_version);
            }
            $result = self::encryptByPublicKey(json_encode($result));

            return $result;
        } catch (Exception $ex) {
            $result = ['status' => 'error', 'message' => $ex->getMessage()];
            $result = self::encryptByPublicKey(json_encode($result));

            return $result;
        }
    }

    public function serial(Request $request, Order $order)
    {
        try {
            $url = $request->input('url');
            $faveo_encrypted_order_number = self::decryptByFaveoPrivateKey($request->input('order_number'));
            $domain = $this->getDomain($request->input('domain'));

            //return $domain;
            $faveo_encrypted_key = self::decryptByFaveoPrivateKey($request->input('serial_key'));
            $request_type = $request->input('request_type');
            $faveo_name = $request->input('name');
            $faveo_version = $request->input('version');
            $order_number = $this->checkOrder($faveo_encrypted_order_number);

            $domain = $this->checkDomain($domain);
            $serial_key = $this->checkSerialKey($faveo_encrypted_key, $order_number);
            //dd($serial_key);
            //return $serial_key;
            $result = [];
            if ($request_type == 'install') {
                $result = $this->verificationResult($order_number, $serial_key);
            }
            if ($request_type == 'check_update') {
                $result = $this->checkUpdate($order_number, $serial_key, $domain, $faveo_name, $faveo_version);
            }
            $result = self::encryptByPublicKey(json_encode($result));
            $this->submit($result, $url);
        } catch (Exception $ex) {
            $result = ['status' => 'error', 'message' => $ex->getMessage()];
            $result = self::encryptByPublicKey(json_encode($result));
            $this->submit($result, $url);
        }
    }

    public static function decryptByFaveoPrivateKeyold($encrypted)
    {
        try {
            // Get the private Key
            $path = storage_path('app'.DIRECTORY_SEPARATOR.'private.key');
            $key_content = file_get_contents($path);
            if (! $privateKey = openssl_pkey_get_private($key_content)) {
                dd('Private Key failed');
            }
            $a_key = openssl_pkey_get_details($privateKey);

            // Decrypt the data in the small chunks
            $chunkSize = ceil($a_key['bits'] / 8);
            $output = '';

            while ("¥IM‰``ì‡Á›LVP›†>¯öóŽÌ3(¢z#¿î1¾­:±Zï©PqÊ´Â›7×:Fà¯¦   à•…Ä'öESW±ÉŸLÃvÈñÔs•ÍU)ÍL 8¬š‰A©·Å $}Œ•lA9™¡”¸èÅØv‘ÂOÈ6„_y5¤ì§—ÿíà(ow‰È&’v&T/FLƒigjÒZ eæaa”{©ªUBFÓ’Ga*ÀŒ×?£}-jÏùh¾Q/Ž“1YFq[Í‰¬òÚ‚œ½Éº5ah¶vZ#,ó@‚rOÆ±íVåèÜÖšU¦ÚmSÎ“Mý„ùP") {
                $chunk = substr($encrypted, 0, $chunkSize);
                $encrypted = substr($encrypted, $chunkSize);
                $decrypted = '';
                if (! openssl_private_decrypt($chunk, $decrypted, $privateKey)) {
                    dd('Failed to decrypt data');
                }
                $output .= $decrypted;
            }
            openssl_free_key($privateKey);

            // Uncompress the unencrypted data.
            $output = gzuncompress($output);
            dd($output);
            echo '<br /><br /> Unencrypted Data: '.$output;
        } catch (Exception $ex) {
            dd($ex);
        }
    }

    public function createEncryptionKeys()
    {
        try {
            $privateKey = openssl_pkey_new([
                'private_key_bits' => 2048, // Size of Key.
                'private_key_type' => OPENSSL_KEYTYPE_RSA,
            ]);
            //dd($privateKey);
            // Save the private key to private.key file. Never share this file with anyone.
            openssl_pkey_export_to_file($privateKey, 'private.key');

            // Generate the public key for the private key
            $a_key = openssl_pkey_get_details($privateKey);
            //dd($a_key);
            // Save the public key in public.key file. Send this file to anyone who want to send you the encrypted data.
            file_put_contents('public.key', $a_key['key']);

            // Free the private Key.
            openssl_free_key($privateKey);
        } catch (\Exception $ex) {
            dd($ex);
        }
    }

    public function checkOrder($faveo_decrypted_order)
    {
        try {
            $order = new Order();
//            $faveo_decrypted_order = self::decryptByFaveoPrivateKey($faveo_encrypted_order_number);

            $this_order = $order->where('number', 'LIKE', $faveo_decrypted_order)->first();
            if (! $this_order) {
                return;
            } else {
                return $this_order->number;
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function faveoVerification(Request $request)
    {
        try {
            $data = $request->input('data');
            $json = self::decryptByFaveoPrivateKey($data);
            $data = json_decode($json);
            //return $data->url;

            $domain = $data->url;

            $faveo_encrypted_order_number = $data->order_number;

            //$domain = $data->domain;

            $faveo_encrypted_key = $data->serial_key;

            $request_type = $data->request_type;

            $faveo_name = $data->name;

            $faveo_version = $data->version;

            $order_number = $this->checkOrder($faveo_encrypted_order_number);

            $domain = $this->checkDomain($domain);

            $serial_key = $this->checkSerialKey($faveo_encrypted_key, $order_number);
            //dd($serial_key);
            //return $serial_key;
            $result = [];
            if ($request_type == 'install') {
                $result = $this->verificationResult($order_number, $serial_key, $domain);
            }
            if ($request_type == 'check_update') {
                $result = $this->checkUpdate($order_number, $serial_key, $domain, $faveo_name, $faveo_version);
            }
            $result = self::encryptByPublicKey(json_encode($result));

            return $result;
        } catch (Exception $ex) {
            $result = ['status' => 'error', 'message' => $ex->getMessage().'  
            file=> '.$ex->getFile().' Line=>'.$ex->getLine()];
            $result = self::encryptByPublicKey(json_encode($result));

            return $result;
        }
    }

    public function submit($result, $url)
    {
        echo "<form action=$url method=post name=redirect>";
        echo '<input type=hidden name=_token value=csrf_token()/>';
        echo '<input type=hidden name=result value='.$result.'/>';
        echo '</form>';
        echo"<script language='javascript'>document.redirect.submit();</script>";
    }

    public function checkUpdate($order_number, $serial_key, $domain, $faveo_name, $faveo_version)
    {
        try {
            if ($order_number && $domain && $serial_key) {
                $order = $this->verifyOrder($order_number, $serial_key, $domain);
                //var_dump($order);
                if ($order) {
                    return $this->checkFaveoDetails($order_number, $faveo_name, $faveo_version);
                } else {
                    return ['status' => 'fails', 'message' => 'this-is-an-invalid-request'];
                }
            } else {
                return ['status' => 'fails', 'message' => 'this-is-an-invalid-request'];
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function checkFaveoDetails($order_number, $faveo_name, $faveo_version)
    {
        try {
            $order = new Order();
            $product = new Product();
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
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public static function encryptByPublicKey($data)
    {
        $path = storage_path().DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'public.key';
        //dd($path);
        $key_content = file_get_contents($path);
        $public_key = openssl_get_publickey($key_content);

        $encrypted = $e = null;
        openssl_seal($data, $encrypted, $e, [$public_key]);

        $sealed_data = base64_encode($encrypted);
        $envelope = base64_encode($e[0]);

        $result = ['seal' => $sealed_data, 'envelope' => $envelope];

        return json_encode($result);
    }

    public function downloadForFaveo(Request $request, Order $order)
    {
        try {
            $faveo_encrypted_order_number = $request->input('order_number');
            $faveo_serial_key = $request->input('serial_key');
            $beta = $request->input('beta', 1);

            $orderSerialKey = $order->where('number', $faveo_encrypted_order_number)
                ->value('serial_key');

            $this_order = $order
                ->where('number', $faveo_encrypted_order_number)
                ->first();
            if ($this_order && $orderSerialKey == $faveo_serial_key) {
                $product_id = $this_order->product;
                $product_controller = new \App\Http\Controllers\Product\ProductController();

                return $product_controller->adminDownload($product_id, '', true, $beta);
            } else {
                return response()->json(['Invalid Credentials']);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage(), 'line' => $e->getFile()], 500);
        }
    }

    public function latestVersion(Request $request, Product $product)
    {
        $v = \Validator::make($request->all(), [
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
            $title = ! $request->has('id') ? $this->changeProductName($request->input('title')) : $request->input('title');

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
                    ->get()->filter(function ($newVersion) use ($currenctVersion) {
                        return version_compare($this->getPHPCompatibleVersionString($newVersion->version), $currenctVersion) == 1;
                    })->sortBy('version', SORT_NATURAL)->toArray();

                    $message = ['version' => array_values($inBetweenVersions)];
                } else {
                    $message = ['error' => 'product_not_found'];
                }
            } else {//For older clients in which version is not sent as parameter
                // $product = $product->where('name', $title)->first();
                if ($product) {
                    $productId = $product->id;
                    $product = ProductUpload::where('product_id', $productId)->where('is_restricted', 1)->orderBy('id', 'asc')->first();

                    $message = ['version' => str_replace('v', '', $product->version)];
                } else {
                    $message = ['error' => 'product_not_found'];
                }
                $message = ['version' => str_replace('v', '', $product->version)];
            }
        } catch (\Exception $e) {
            app('log')->error($e->getMessage());
            $message = ['error' => $e->getMessage()];
        }

        return response()->json($message);
    }

    public function isNewVersionAvailable(Request $request, Product $product)
    {
        $v = \Validator::make($request->all(), [
            'title' => 'required',
        ], [
            'title.required' => __('validation.extend_product.title_required'),
        ]);
        if ($v->fails()) {
            $error = $v->errors();

            return response()->json(compact('error'));
        }
        try {
            $title = ! $request->has('id') ? $this->changeProductName($request->input('title')) : $request->input('title');

            $id = $request->input('id');

            $product = ($id) ?
                $product->where('id', $id)->select('id')->first() :
                $product->whereRaw('LOWER(`name`) LIKE ? ', strtolower($this->mapOldBoys($title)))->orWhere('id', $id)->select('id')->first();

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
                if (version_compare($this->getPHPCompatibleVersionString($version), $currenctVersion) == 1) {
                    $message = ['status' => 'true', 'message' => 'new-version-available'];
                    break;
                }
            }
        } catch (\Exception $ex) {
            $message = ['error' => $ex->getMessage()];
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
        return preg_replace('#v\.|v#', '', str_replace('_', '.', $version));
    }

    public function renewurl(ProductRenewalRequest $request)
    {
        try {
            $orderId = InstallationDetail::Where('installation_path', 'like', '%'.$request->input('domain').'%')->value('order_id');
            $subscription = Subscription::where('order_id', $orderId)->first();

            $basecron = new CronController();
            $order = $basecron->getOrderById($subscription->order_id);
            $oldinvoice = $basecron->getInvoiceByOrderId($subscription->order_id);
            $item = $basecron->getInvoiceItemByInvoiceId($oldinvoice->id);

            $product_details = Product::where('name', $item->product_name)->first();
            $plan = Plan::where('product', $product_details->id)->first('days');
            $oldcurrency = $oldinvoice->currency;

            $user = User::where('id', $subscription->user_id)->first();
            $planid = Plan::where('product', $product_details->id)->value('id');
            $cost = PlanPrice::where('plan_id', $planid)->where('currency', $oldcurrency)->value('renew_price');

            $renewController = new RenewController();
            $invoiceItems = $renewController->generateInvoice($product_details, $user, $order->id, $plan->id, $cost, $code = '', $item->agents, $oldcurrency);
            $invoiceid = $invoiceItems->invoice_id;
            $url = url("autopaynow/$invoiceid");

            return $url;
        } catch(\Exception $ex) {
            $message = ['error' => $ex->getMessage()];

            return response()->json($message);
        }
    }

    private function changeProductName($title)
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

    public function getPricingData(Request $request)
    {
        try {
            $groupId = $request->query('group');
            $countryCode = $request->query('country', '');

            $group = ProductGroup::findOrFail($groupId);

            $countryId = Country::where('country_code_char2', $countryCode)->value('country_id');
            $currencyAndSymbol = getCurrencyForClient($countryCode);

            $productsRelatedToGroup = \App\Model\Product\Product::where('group', $groupId)
                ->where('hidden', '!=', 1)
                ->join('plans', 'products.id', '=', 'plans.product')
                ->join('plan_prices', 'plans.id', '=', 'plan_prices.plan_id')
                ->where('plan_prices.country_id', '=', $countryId)
                ->orderByRaw('CAST(plan_prices.add_price AS DECIMAL(10, 2)) ASC')
                ->orderBy('created_at', 'ASC')
                ->select('products.*', 'plan_prices.add_price', 'plans.days', 'plan_prices.offer_price', 'plan_prices.price_description')
                ->get();

            if ($productsRelatedToGroup->isEmpty()) {
                $productsRelatedToGroup = \App\Model\Product\Product::where('group', $groupId)
                    ->where('hidden', '!=', 1)
                    ->join('plans', 'products.id', '=', 'plans.product')
                    ->join('plan_prices', 'plans.id', '=', 'plan_prices.plan_id')
                    ->where('plan_prices.country_id', '=', 0)
                    ->orderByRaw('CAST(plan_prices.add_price AS DECIMAL(10, 2)) ASC')
                    ->orderBy('created_at', 'ASC')
                    // ->select('products.*', 'plan_prices.add_price')
                    ->select('products.*', 'plan_prices.add_price', 'plans.days', 'plan_prices.offer_price', 'plan_prices.price_description')
                    ->get();
            }

            return response()->json(['products' => $productsRelatedToGroup, 'currency' => $currencyAndSymbol]);
        } catch (\Exception $ex) {
            return response()->json(['error' => $ex->getMessage()], 500);
        }
    }

    public function getGroupDatails()
    {
        $group = ProductGroup::where('hidden', '0')->pluck('id', 'name');

        return response()->json(['group' => $group]);
    }

    public function getDetailedBillingInfo(Request $request): \Illuminate\Http\JsonResponse
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

    public function getDetailsForAClient(Request $request)
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
        
        $uniqueElements = array_merge($uniqueElements,$product);

        $licenses = Order::where('client', $user)->whereIn('product', $uniqueElements)
            ->pluck('serial_key')
            ->toArray();

        $licenses = array_merge([$license], $licenses);

        $client = new Client();

        $licenseUrl = ApiKey::value('license_api_url');

        $response = $client->get($licenseUrl.'api/pluginLicense', [
            'query' => ['license_code' => json_encode($licenses)],
        ]);

        $updatedProducts = [];
        $products = json_decode($response->getBody()->getContents(), true);
        $realProducts = json_decode($products['data'], true);
        foreach ($realProducts as $realprod) {
            foreach ($realprod as $real) {
                $dependency = \DB::table('product_uploads')
                    ->where('product_id', $real['product_id'])
                    ->where('version', $real['version'])
                    ->latest()
                    ->value('dependencies');

                $real['dependency'] = $dependency ?? null;

                $updatedProducts[] = $real;
            }
        }

        return json_encode($updatedProducts);
    }


    public function getProductRelease(Request $request)
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

    private function mapOldBoys($title)
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
