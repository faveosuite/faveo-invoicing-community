<?php

namespace App\Http\Controllers\License;

use App\Http\Controllers\Controller;
use App\Model\Order\Order;
use App\Model\Product\Product;
use App\Streams\License\LicenseStreamHandler;
use Illuminate\Support\Facades\Http;

class LicenseController extends Controller
{
    private LicenseService $licenseService;

    public function __construct()
    {
        $this->licenseService = new LicenseService();
    }

    private function isStreams(): bool
    {
        return true;
    }

    private function getStreamSearchResults(string $searchType, string $searchKeyword): array
    {
        return LicenseStreamHandler::search($searchType, $searchKeyword)['result']['data']['data'] ?? [];
    }

    private function getStreamResultData(array $response): array
    {
        return $response['result']['data'] ?? [];
    }

    /**
     * Make HTTP POST request to license API.
     */
    private function postRequest(string $endpoint, array $data, bool $useToken = true): array
    {
        $url = $this->licenseService->getUrl().$endpoint;
        throttleApiRequest($url);

        $payload = array_merge(['api_key_secret' => $this->licenseService->getApiKeySecret()], $data);

        $request = Http::asForm()
            ->withOptions(['verify' => false, 'allow_redirects' => true]);

        if ($useToken) {
            $request->withToken($this->licenseService->getValidToken());
        }

        $response = $request->post($url, $payload);

        return $response->json() ?? [];
    }

    /**
     * Make HTTP GET request to license API.
     */
    private function getRequest(string $endpoint, array $query = [], bool $useToken = true): array
    {
        $url = $this->licenseService->getUrl().$endpoint;
        throttleApiRequest($url);

        $request = Http::withOptions(['verify' => false])
            ->timeout(90);

        if ($useToken) {
            $request->withToken($this->licenseService->getValidToken());
        }

        return $request->get($url, $query)->json() ?? [];
    }

    /**
     * Execute API call with streams or fallback to direct API.
     */
    private function executeWithStreams(string $streamMethod, array $streamParams, callable $legacyCallback): mixed
    {
        if ($this->isStreams()) {
            return $streamParams ?
                LicenseStreamHandler::$streamMethod(...$streamParams) :
                LicenseStreamHandler::$streamMethod();
        }

        return $legacyCallback();
    }

    /**
     * Return API key and API url.
     */
    public function getLicensekey(): array
    {
        if ($this->isStreams()) {
            return ['data' => $this->getStreamResultData(LicenseStreamHandler::getLicenseKey()), 'url' => ''];
        }

        $url = $this->licenseService->getUrl();
        $token = $this->licenseService->getValidToken();
        $getkey = $this->getRequest('api/admin/viewApiKeys', useToken: !empty($token));

        return ['data' => $getkey, 'url' => $url];
    }

    /*
    *  Add New Product
    */
    public function addNewProduct($product_name, $product_sku): void
    {
        if ($this->isStreams()) {
            LicenseStreamHandler::addNewProduct($product_name, $product_sku);
            return;
        }

        $this->postRequest('api/admin/products/add', [
            'product_title' => $product_name,
            'product_sku' => $product_sku,
            'product_status' => 1,
        ]);
    }

    /*
   *  Add New User
   */
    public function addNewUser($first_name, $last_name, $email): void
    {
        if ($this->isStreams()) {
            LicenseStreamHandler::addNewUser($first_name, $last_name, $email);
            return;
        }

        $this->postRequest('api/admin/clients/add', [
            'client_fname' => $first_name,
            'client_lname' => $last_name,
            'client_email' => $email,
            'client_role' => 'client',
            'client_status' => 1,
        ]);
    }

    /*
   *  Edit Product
   */
    public function editProduct($product_name, $product_sku): void
    {
        if ($this->isStreams()) {
            LicenseStreamHandler::editProduct($product_name, $product_sku);
            return;
        }

        $productId = $this->searchProductId($product_sku);
        $this->postRequest('api/admin/products/edit', [
            'product_id' => $productId,
            'product_title' => $product_name,
            'product_sku' => $product_sku,
            'product_status' => 1,
        ]);
    }

    /*
   *  Search for product id while updating client
   */
    public function searchProductId($product_sku): string
    {
        if ($this->isStreams()) {
            $data = $this->getStreamSearchResults('product', $product_sku);
            return !empty($data) ? ($data[0]['product_id'] ?? '') : '';
        }

        $result = $this->postRequest('api/admin/search', [
            'search_type' => 'product',
            'search_keyword' => $product_sku,
            'isLicenseSearchApi' => 1,
        ]);

        if (($result['api_error_detected'] ?? 1) == 0 && is_array($result['page_message'] ?? [])) {
            return $result['page_message'][0]->product_id ?? '';
        }

        return '';
    }

    public function deleteProductFromAPL($product): void
    {
        if ($this->isStreams()) {
            LicenseStreamHandler::deleteProduct($product->product_sku, $product->name, $product->sku);
            return;
        }

        $productId = $this->searchProductId($product->product_sku);
        $this->postRequest('api/admin/products/delete', [
            'product_id' => $productId,
            'product_title' => $product->name,
            'product_sku' => $product->sku,
            'product_status' => 1,
            'delete_record' => 1,
        ]);
    }

    /*
   *  Edit User
   */
    public function editUserInLicensing($first_name, $last_name, $email): void
    {
        if ($this->isStreams()) {
            LicenseStreamHandler::editUser($first_name, $last_name, $email);
            return;
        }

        $userId = $this->searchForUserId($email);
        $this->postRequest('api/admin/clients/edit', [
            'client_id' => $userId,
            'client_fname' => $first_name,
            'client_lname' => $last_name,
            'client_email' => $email,
            'client_role' => 'client',
            'client_status' => 1,
        ]);
    }

    /*
   *  Search for user id while updating client
   */
    public function searchForUserId($email): string
    {
        if ($this->isStreams()) {
            $data = $this->getStreamSearchResults('client', $email);
            return !empty($data) ? ($data[0]['client_id'] ?? '') : '';
        }

        $result = $this->postRequest('api/admin/search', [
            'search_type' => 'client',
            'search_keyword' => $email,
            'isLicenseSearchApi' => 1,
        ]);

        if (($result['api_error_detected'] ?? 1) == 0 && is_array($result['page_message'] ?? [])) {
            return $result['page_message'][0]->client_id ?? '';
        }

        return '';
    }

    /*
    *  Create New License For User
    */
    public function createNewLicene($orderid, $product, $user_id, $licenseExpiry, $updatesExpiry, $supportExpiry, $serial_key): void
    {
        if ($this->isStreams()) {
            LicenseStreamHandler::createNewLicense($orderid, $product, $user_id, $licenseExpiry, $updatesExpiry, $supportExpiry, $serial_key);
            return;
        }

        $sku = Product::where('id', $product)->value('product_sku');
        $order = Order::where('id', $orderid)->first();
        $ipAndDomain = $this->getIpAndDomain($order?->domain ?? '');

        $this->postRequest('api/admin/license/add', [
            'product_id' => $this->searchProductId($sku),
            'license_code' => $serial_key,
            'license_require_domain' => 1,
            'license_status' => 1,
            'license_order_number' => $order?->number ?? '',
            'license_domain' => $ipAndDomain['domain'],
            'license_ip' => $ipAndDomain['ip'],
            'license_limit' => 1,
            'license_expire_date' => $licenseExpiry?->toDateString() ?? '',
            'license_updates_date' => $updatesExpiry?->toDateString() ?? '',
            'license_support_date' => $supportExpiry?->toDateString() ?? '',
            'license_disable_ip_verification' => 0,
        ]);
    }

    /*
    *  Edit Existing License
    */
    public function updateLicensedDomain($licenseCode, $domain, $productId, $licenseExpiry, $updatesExpiry, $supportExpiry, $orderNo, $license_limit = 2, $requiredomain = 1): void
    {
        if ($this->isStreams()) {
            LicenseStreamHandler::updateLicensedDomain($licenseCode, $domain, $productId, $licenseExpiry, $updatesExpiry, $supportExpiry, $orderNo, $license_limit, $requiredomain);
            return;
        }

        $ipAndDomain = $this->getIpAndDomain($domain);
        $searchLicense = $this->searchLicenseId($licenseCode, $productId);

        $this->postRequest('api/admin/license/edit', [
            'product_id' => $searchLicense['productId'],
            'license_code' => $searchLicense['code'],
            'license_id' => $searchLicense['licenseId'],
            'license_order_number' => $orderNo,
            'license_require_domain' => $searchLicense['allowedInstalltion'],
            'license_status' => 1,
            'license_expire_date' => $this->formatDate($licenseExpiry),
            'license_updates_date' => $this->formatDate($updatesExpiry),
            'license_support_date' => $this->formatDate($supportExpiry),
            'license_domain' => $ipAndDomain['domain'],
            'license_ip' => $ipAndDomain['ip'],
            'license_limit' => $license_limit,
        ]);
    }

    /**
     * Get the Ip and domain that is to be entered in License Manager.
     *
     * @param  string  $domain
     */
    protected function getIpAndDomain($domain): array
    {
        if ($domain !== '') {
            return ip2long($domain)
                ? ['ip' => $domain, 'domain' => '', 'requireDomain' => 0]
                : ['ip' => '', 'domain' => $domain, 'requireDomain' => 1];
        }

        return ['ip' => '', 'domain' => '', 'requireDomain' => 0];
    }

    /**
     * Format date for API requests.
     */
    private function formatDate($date): string
    {
        return strtotime($date) > 1 ? date('Y-m-d', strtotime($date)) : '';
    }

    public function searchLicenseId($licenseCode, $productId): array
    {
        if ($this->isStreams()) {
            return $this->searchLicenseIdFromStreams($licenseCode, $productId);
        }

        $result = $this->postRequest('api/admin/search', [
            'search_type' => 'license',
            'search_keyword' => $licenseCode,
            'isLicenseSearchApi' => 1,
        ]);

        return $this->extractLicenseData($result, $productId);
    }

    /**
     * Extract license data from search result.
     */
    private function extractLicenseData(array $result, $productId): array
    {
        $default = ['productId' => '', 'code' => '', 'licenseId' => '', 'allowedInstalltion' => '', 'installationLimit' => ''];

        if (($result['api_error_detected'] ?? 1) != 0 || !is_array($result['page_message'] ?? [])) {
            return $default;
        }

        foreach ($result['page_message'] as $detail) {
            if ($detail->product_id == $productId) {
                return [
                    'productId' => $detail->product_id,
                    'code' => $detail->license_code,
                    'licenseId' => $detail->license_id,
                    'allowedInstalltion' => $detail->license_require_domain,
                    'installationLimit' => $detail->license_limit,
                ];
            }
        }

        return $default;
    }

    /**
     * Search license ID from streams.
     */
    private function searchLicenseIdFromStreams($licenseCode, $productId): array
    {
        $data = $this->getStreamSearchResults('license', $licenseCode);

        foreach ($data as $detail) {
            if (($detail['product_id'] ?? null) == $productId) {
                return [
                    'productId' => $detail['product_id'] ?? '',
                    'code' => $detail['license_code'] ?? '',
                    'licenseId' => $detail['license_id'] ?? '',
                    'allowedInstalltion' => $detail['license_require_domain'] ?? '',
                    'installationLimit' => $detail['license_limit'] ?? '',
                ];
            }
        }

        return ['productId' => '', 'code' => '', 'licenseId' => '', 'allowedInstalltion' => '', 'installationLimit' => ''];
    }

    //Update the Installation status as Inactive after Licensed Domain Is Chnaged
    public function updateInstalledDomain($licenseCode, $productId): void
    {
        if ($this->isStreams()) {
            LicenseStreamHandler::updateInstalledDomain($licenseCode, $productId);
            return;
        }

        $result = $this->searchInstallationId($licenseCode);

        if (($result['api_error_detected'] ?? 1) != 0 || !is_array($result['page_message'] ?? [])) {
            return;
        }

        foreach ($result['page_message'] as $detail) {
            if ($detail->product_id == $productId) {
                $this->postRequest('api/admin/installations/edit', [
                    'installation_id' => $detail->installation_id,
                    'installation_ip' => $detail->installation_ip,
                    'installation_status' => 0,
                    'delete_record' => 1,
                ]);
            }
        }
    }

    public function searchInstallationId($licenseCode): array
    {
        if ($this->isStreams()) {
            return $this->getStreamSearchResults('installation', $licenseCode);
        }

        return $this->postRequest('api/admin/search', [
            'search_type' => 'installation',
            'search_keyword' => $licenseCode,
            'isLicenseSearchApi' => 1,
        ]);
    }

    public function searchInstallationPath($licenseCode, $productId): array
    {
        if ($this->isStreams()) {
            return $this->searchInstallationPathFromStreams($licenseCode, $productId);
        }

        return $this->searchInstallationPathLegacy($licenseCode, $productId);
    }

    /**
     * Search installation path from streams.
     */
    private function searchInstallationPathFromStreams($licenseCode, $productId): array
    {
        $data = $this->getStreamSearchResults('installation', $licenseCode);

        return $this->extractInstallationData($data, $productId);
    }

    /**
     * Search installation path using legacy API.
     */
    private function searchInstallationPathLegacy($licenseCode, $productId): array
    {
        $result = $this->searchInstallationId($licenseCode);

        if (($result['api_error_detected'] ?? 1) != 0 || !is_array($result['page_message'] ?? [])) {
            return ['installed_path' => [], 'installed_ip' => [], 'installation_date' => [], 'installation_status' => []];
        }

        return $this->extractInstallationData($result['page_message'], $productId);
    }

    /**
     * Extract installation data from search results.
     */
    private function extractInstallationData(array $data, $productId): array
    {
        $installation_domain = [];
        $installation_ip = [];
        $installation_date = [];
        $installation_status = [];

        foreach ($data as $detail) {
            if (($detail['product_id'] ?? null) == $productId) {
                $installation_domain[] = $detail['installation_domain'] ?? '';
                $installation_ip[] = $detail['installation_ip'] ?? '';
                $installation_date[] = $detail['installation_date'] ?? '';
                $installation_status[] = $detail['installation_status'] ?? '';
            }
        }

        return [
            'installed_path' => $installation_domain,
            'installed_ip' => $installation_ip,
            'installation_date' => $installation_date,
            'installation_status' => $installation_status,
        ];
    }

    //Update  Expiration Date After Renewal
    public function updateExpirationDate($licenseCode, $expiryDate, $productId, $domain, $orderNo, $licenseExpiry, $supportExpiry, $license_limit = 2, $requiredomain = 1): void
    {
        if ($this->isStreams()) {
            LicenseStreamHandler::updateExpirationDate($licenseCode, $expiryDate, $productId, $domain, $orderNo, $licenseExpiry, $supportExpiry, $license_limit, $requiredomain);
            return;
        }

        $ipAndDomain = $this->getIpAndDomain($domain);
        $searchLicense = $this->searchLicenseId($licenseCode, $productId);

        $this->postRequest('api/admin/license/edit', [
            'product_id' => $searchLicense['productId'],
            'license_code' => $searchLicense['code'],
            'license_id' => $searchLicense['licenseId'],
            'license_order_number' => $orderNo,
            'license_domain' => $ipAndDomain['domain'],
            'license_ip' => $ipAndDomain['ip'],
            'license_require_domain' => $searchLicense['allowedInstalltion'],
            'license_status' => 1,
            'license_expire_date' => $licenseExpiry,
            'license_updates_date' => $expiryDate,
            'license_support_date' => $supportExpiry,
            'license_limit' => $license_limit,
        ]);
    }

    public function getNoOfAllowedInstallation($licenseCode, $productId)
    {
        return $this->searchLicenseId($licenseCode, $productId)['installationLimit'];
    }

    public function getInstallPreference($licenseCode, $productId)
    {
        return $this->searchLicenseId($licenseCode, $productId)['allowedInstalltion'];
    }

    public function deActivateTheLicense($licenseCode): void
    {
        if ($this->isStreams()) {
            LicenseStreamHandler::deActivateTheLicense($licenseCode);
            return;
        }

        try {
            $this->postRequest('api/admin/license/deactivate', ['license_code' => $licenseCode]);
        } catch (\Exception $ex) {
            \Logger::exception($ex);
        }
    }

    public function reissueDomain($installationPath): void
    {
        if ($this->isStreams()) {
            LicenseStreamHandler::reissueDomain($installationPath);
            return;
        }

        $this->postRequest('api/admin/installation/reissue', ['installation_path' => $installationPath]);
    }

    public function updateLicense($license_code, $oldLicense): void
    {
        if ($this->isStreams()) {
            LicenseStreamHandler::updateLicense($license_code, $oldLicense);
            return;
        }

        $this->postRequest('api/admin/license/updateLicenseCode', [
            'license_code' => $license_code,
            'old_license_code' => $oldLicense,
        ]);
    }

    public function licenseRedirect($orderNumber)
    {
        return redirect('/orders/'.Order::where('number', $orderNumber)->value('id'));
    }

    public function syncTheAddonForALicense($product_ids, $license_code, $options = []): void
    {
        if ($this->isStreams()) {
            LicenseStreamHandler::syncTheAddonForALicense($product_ids, $license_code, $options);
            return;
        }

        $this->postRequest('api/admin/license/syncAddonLicense', [
            'license_code' => $license_code,
            'product_ids' => $product_ids,
            'options' => json_encode($options),
        ]);
    }

    public function getInstallationLogsDetails($license_code): array
    {
        if ($this->isStreams()) {
            return $this->getStreamResultData(LicenseStreamHandler::getInstallationLogsDetails($license_code));
        }

        $result = $this->postRequest('api/admin/getInstallationLogs', ['license_code' => $license_code]);

        if (($result['api_error_detected'] ?? 1) != 0 || !is_array($result['page_message'] ?? [])) {
            return [];
        }

        return collect($result['page_message'])->map(fn ($item) => [
            'installation_domain' => $item->installation_domain,
            'installation_ip' => $item->installation_ip,
            'installation_last_active_date' => $item->installation_last_active_date,
            'installation_status' => $item->installation_status,
            'version_number' => $item->version_number,
        ])->toArray();
    }

    public function updateInstallationLogs($root_url, $version_number, $installation_ip, $licenseCode): void
    {
        if ($this->isStreams()) {
            LicenseStreamHandler::updateInstallationLogs($root_url, $version_number, $installation_ip, $licenseCode);
            return;
        }

        $this->postRequest('api/admin/updateInstallationLogs', [
            'root_url' => $root_url,
            'version_number' => $version_number,
            'installation_ip' => $installation_ip,
            'license_code' => $licenseCode,
        ]);
    }

    public function searchProductUsingLicense($licenseCode): array
    {
        if ($this->isStreams()) {
            return $this->getStreamSearchResults('license', $licenseCode);
        }

        $result = $this->postRequest('api/admin/search', [
            'search_type' => 'license',
            'search_keyword' => $licenseCode,
            'isLicenseSearchApi' => 1,
        ]);

        if (($result['api_error_detected'] ?? 1) == 0 && isset($result['page_message'][0])) {
            return collect($result['page_message'])->toArray();
        }

        return [];
    }

    public function searchProductUsingProductKey($productKey): string
    {
        if ($this->isStreams()) {
            $data = $this->getStreamSearchResults('product', $productKey);
            return !empty($data) ? ($data[0]['product_id'] ?? '') : '';
        }

        return $this->getRequest('api/admin/getProductIdbyKey', [
            'api_key_secret' => $this->licenseService->getApiKeySecret(),
            'product_key' => $productKey,
        ]);
    }

    public function getPluginInfo($licensesCodes): array
    {
        if ($this->isStreams()) {
            return $this->getStreamResultData(LicenseStreamHandler::getPluginInfo($licensesCodes));
        }

        return $this->postRequest('api/admin/getPluginInfo', [
            'license_codes' => json_encode($licensesCodes),
        ]);
    }
}
