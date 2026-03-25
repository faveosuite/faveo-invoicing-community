<?php

namespace App\Streams\License;

use App\Model\Order\Order;
use App\Model\Product\Product;
use App\Streams\RedisAdapterManager;
use App\Streams\RedisStreamProducer;
use Illuminate\Support\Str;
use RuntimeException;

class LicenseStreamHandler
{
    private const string REQUEST_STREAM = 'license_request';
    private const string RESPONSE_STREAM = 'license_responses';
    private const int TIMEOUT = 10;
    private const int POLL_INTERVAL_US = 100_000;

    protected RedisStreamProducer $producer;

    public function __construct()
    {
        $this->producer = new RedisStreamProducer(self::REQUEST_STREAM);
    }

    /**
     * Handle a license event by publishing to Redis stream and waiting for response.
     */
    protected static function handle(string $eventType, array $payload = []): array
    {
        $instance = new self();
        $correlationId = (string) Str::uuid();

        $requestId = $instance->producer->publish($eventType, array_merge($payload, [
            'reply_to' => self::RESPONSE_STREAM,
            'correlation_id' => $correlationId,
        ]));

        return $instance->waitForResponse($correlationId, $requestId);
    }

    public static function getLicenseKey(): array
    {
        return self::handle('license_get_key');
    }

    public static function addNewProduct(string $productName, string $productSku): array
    {
        return self::handle('license_add_product', [
            'product_title' => $productName,
            'product_sku' => $productSku,
            'product_status' => 1,
        ]);
    }

    public static function addNewUser(string $firstName, string $lastName, string $email): array
    {
        return self::handle('license_add_user', [
            'client_fname' => $firstName,
            'client_lname' => $lastName,
            'client_email' => $email,
            'client_role' => 'client',
            'client_status' => 1,
        ]);
    }

    public static function editProduct(string $productName, string $productSku): array
    {
        $productId = self::searchProductId($productSku);

        return self::handle('license_edit_product', [
            'product_id' => $productId,
            'product_title' => $productName,
            'product_sku' => $productSku,
            'product_status' => 1,
        ]);
    }

    public static function search(string $searchType, string $searchKeyword): array
    {
        return self::handle('license_search', [
            'search_type' => $searchType,
            'search_keyword' => $searchKeyword,
            'isLicenseSearchApi' => 1,
        ]);
    }

    public static function deleteProduct(string $productSku): array
    {
        $productId = self::searchProductId($productSku);

        return self::handle('license_delete_product', [
            'product_id' => $productId,
        ]);
    }

    public static function editUser(string $firstName, string $lastName, string $email): array
    {
        $userId = self::searchUserId($email);

        return self::handle('license_edit_user', [
            'client_id' => $userId,
            'client_fname' => $firstName,
            'client_lname' => $lastName,
            'client_email' => $email,
            'client_role' => 'client',
            'client_status' => 1,
        ]);
    }

    public static function createNewLicense($orderId, $product, $userId, $licenseExpiry, $updatesExpiry, $supportExpiry, string $serialKey): array
    {
        $sku = Product::where('id', $product)->value('product_sku');
        $order = Order::where('id', $orderId)->first();
        $ipAndDomain = self::getIpAndDomain($order?->domain ?? '');
        $productId = self::searchProductId($sku);

        return self::handle('license_create', [
            'product_id' => $productId,
            'license_code' => $serialKey,
            'license_require_domain' => 1,
            'license_status' => 1,
            'license_order_number' => $order?->number ?? '',
            'license_domain' => $ipAndDomain['domain'],
            'license_ip' => $ipAndDomain['ip'],
            'license_limit' => 1,
            'license_expire_date' => self::formatExpiry($licenseExpiry),
            'license_updates_date' => self::formatExpiry($updatesExpiry),
            'license_support_date' => self::formatExpiry($supportExpiry),
            'license_disable_ip_verification' => 0,
        ]);
    }

    public static function updateLicensedDomain(string $licenseCode, $domain, $productId, $licenseExpiry, $updatesExpiry, $supportExpiry, $orderNo, int $licenseLimit = 2, int $requireDomain = 1): array
    {
        $ipAndDomain = self::getIpAndDomain($domain);
        $searchLicense = self::searchLicenseId($licenseCode, $productId);

        return self::handle('license_update_domain', [
            'product_id' => $searchLicense['productId'],
            'license_code' => $searchLicense['code'],
            'license_id' => $searchLicense['licenseId'],
            'license_order_number' => $orderNo,
            'license_require_domain' => $searchLicense['allowedInstalltion'],
            'license_status' => 1,
            'license_expire_date' => self::formatExpiry($licenseExpiry),
            'license_updates_date' => self::formatExpiry($updatesExpiry),
            'license_support_date' => self::formatExpiry($supportExpiry),
            'license_domain' => $ipAndDomain['domain'],
            'license_ip' => $ipAndDomain['ip'],
            'license_limit' => $licenseLimit,
        ]);
    }

    public static function updateInstalledDomain(string $licenseCode, $productId): array
    {
        return self::handle('license_update_installed_domain', [
            'license_code' => $licenseCode,
            'product_id' => $productId,
        ]);
    }

    public static function updateExpirationDate(string $licenseCode, $expiryDate, $productId, $domain, $orderNo, $licenseExpiry, $supportExpiry, int $licenseLimit = 2, int $requireDomain = 1): array
    {
        $ipAndDomain = self::getIpAndDomain($domain);
        $searchLicense = self::searchLicenseId($licenseCode, $productId);

        return self::handle('license_update_expiration', [
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
            'license_limit' => $licenseLimit,
        ]);
    }

    public static function deActivateTheLicense(string $licenseCode): array
    {
        return self::handle('license_deactivate', [
            'license_code' => $licenseCode,
        ]);
    }

    public static function reissueDomain(string $installationPath): array
    {
        return self::handle('license_domain_reissue', [
            'installation_path' => $installationPath,
        ]);
    }

    public static function updateLicense(string $licenseCode, string $oldLicense): array
    {
        return self::handle('license_update_code', [
            'license_code' => $licenseCode,
            'old_license_code' => $oldLicense,
        ]);
    }

    public static function syncTheAddonForALicense($productIds, string $licenseCode, array $options = []): array
    {
        return self::handle('license_sync_addon', [
            'license_code' => $licenseCode,
            'product_ids' => $productIds,
            'options' => json_encode($options),
        ]);
    }

    public static function getInstallationLogsDetails(string $licenseCode): array
    {
        return self::handle('license_get_installation_logs', [
            'license_code' => $licenseCode,
        ]);
    }

    public static function updateInstallationLogs(string $rootUrl, string $versionNumber, string $installationIp, string $licenseCode): array
    {
        return self::handle('license_update_installation_logs', [
            'root_url' => $rootUrl,
            'version_number' => $versionNumber,
            'installation_ip' => $installationIp,
            'license_code' => $licenseCode,
        ]);
    }

    public static function getPluginInfo(array $licenseCodes): array
    {
        return self::handle('license_plugin_info', [
            'license_codes' => json_encode($licenseCodes),
        ]);
    }

    /**
     * Format expiry date for API requests.
     */
    private static function formatExpiry($expiry): string
    {
        if ($expiry === '') {
            return '';
        }

        return is_string($expiry) ? $expiry : $expiry->toDateString();
    }

    /**
     * Get the IP and domain for license API requests.
     */
    private static function getIpAndDomain($domain): array
    {
        if ($domain !== '') {
            return ip2long($domain)
                ? ['ip' => $domain, 'domain' => '', 'requireDomain' => 0]
                : ['ip' => '', 'domain' => $domain, 'requireDomain' => 1];
        }

        return ['ip' => '', 'domain' => '', 'requireDomain' => 0];
    }

    /**
     * Search for product ID by SKU.
     */
    private static function searchProductId(string $productSku): string
    {
        $data = self::search('product', $productSku)['result']['data']['data'] ?? [];

        return ! empty($data) ? ($data[0]['product_id'] ?? '') : '';
    }

    /**
     * Search for user ID by email.
     */
    private static function searchUserId(string $email): string
    {
        $data = self::search('client', $email)['result']['data']['data'] ?? [];

        return ! empty($data) ? ($data[0]['client_id'] ?? '') : '';
    }

    /**
     * Search for license ID and related data.
     */
    private static function searchLicenseId(string $licenseCode, $productId): array
    {
        $data = self::search('license', $licenseCode)['result']['data']['data'] ?? [];

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

    /**
     * Poll the response stream for a message matching the correlation ID.
     */
    protected function waitForResponse(string $correlationId, string $requestId): array
    {
        $redis = RedisAdapterManager::create();
        $start = time();
        $lastId = '0-0';

        while ((time() - $start) < self::TIMEOUT) {
            $messages = $redis->xrange(self::RESPONSE_STREAM, $lastId, '+', 100);

            foreach ($messages as $id => $message) {
                $data = json_decode($message['message'] ?? '{}', true);

                if (($data['payload']['correlation_id'] ?? null) === $correlationId) {
                    $result = $data['payload'] ?? [];

                    // Clean up: delete request and response messages
                    $redis->xdel(self::REQUEST_STREAM, [$requestId]);
                    $redis->xdel(self::RESPONSE_STREAM, [$id]);

                    return $result;
                }

                $lastId = $id;
            }

            if ($lastId !== '0-0') {
                $parts = explode('-', $lastId);
                $lastId = $parts[0].'-'.((int) $parts[1] + 1);
            }

            usleep(self::POLL_INTERVAL_US);
        }

        throw new RuntimeException("Timeout waiting for response (correlation_id: {$correlationId})");
    }
}
