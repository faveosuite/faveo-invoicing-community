<?php

namespace App\License\Controllers\Traits;

use App\License\Models\VersionCallback;
use App\License\Models\VersionNotification;
use App\Model\Product\Product;
use App\Model\Product\ProductUpload;
use Illuminate\Http\JsonResponse;

trait AfuCallbackHelpers
{
    /**
     * Build notification response with headers (same format as license callbacks).
     * Original only sends notification_data when notification_case is 'notification_operation_ok'.
     *
     * $product/$version are passed in whenever the caller has already resolved them, so
     * their shortcodes (%PRODUCT_TITLE%, %VERSION_NUMBER%, ...) can be substituted.
     *
     * @param  array<mixed>  $data
     */
    protected function notificationResponse(string $notificationCase, array $data = [], ?Product $product = null, ?ProductUpload $version = null): JsonResponse
    {
        // Only returns notification_data when everything is OK
        $responseData = ($notificationCase === 'notification_operation_ok') ? $data : [];

        $productKey = (string) request()->input('product_key', '');
        $productId = $product->id ?? request()->input('product_id');

        return response()->json($responseData)
            ->header('notification_case', $notificationCase)
            ->header('notification_text', $this->getNotificationText($notificationCase, $product, $version))
            ->header('notification_server_signature', $this->generateSignature($productId, $productKey));
    }

    /**
     * Get notification text from version_notifications table, with shortcodes replaced.
     * Matches returnUpdateServerNotification() from original.
     */
    protected function getNotificationText(string $case, ?Product $product = null, ?ProductUpload $version = null): string
    {
        $notification = VersionNotification::first();
        $text = (string) ($notification ? ($notification->{$case} ?? $case) : $case);

        $placeholders = [
            '%PRODUCT_ID%', '%PRODUCT_TITLE%', '%PRODUCT_DESCRIPTION%',
            '%PRODUCT_URL_HOMEPAGE%', '%PRODUCT_URL_DOWNLOAD%',
            '%VERSION_NUMBER%', '%VERSION_EXPIRE_DATE%', '%IP_ADDRESS%',
        ];
        $replacements = [
            (string) ($product->id ?? request()->input('product_id', '')),
            (string) ($product->name ?? ''),
            (string) ($product->description ?? ''),
            (string) ($product->product_url_homepage ?? ''),
            (string) ($product->product_url_download ?? ''),
            (string) ($version->version ?? ''),
            (string) ($version->version_expire_date ?? ''),
            (string) request()->ip(),
        ];

        return str_ireplace($placeholders, $replacements, $text);
    }

    /**
     * Generate signature for version callbacks.
     * Original: hash('sha256', implode('', $root_ips_array) . $product_key . $product_id . gmdate('Y-m-d')).
     */
    protected function generateSignature(mixed $productId = null, ?string $productKey = null): string
    {
        $rootUrl = url('/');
        $rootIps = @gethostbynamel(str_ireplace('www.', '', (string) (parse_url($rootUrl, PHP_URL_HOST) ?? '')));

        if (empty($rootIps)) {
            // Matches original: DNS resolution failed, no signature can be trusted.
            return '';
        }

        return hash('sha256', implode('', $rootIps).$productKey.$productId.gmdate('Y-m-d'));
    }

    /**
     * Log version callback.
     */
    protected function logCallback(int $productId, int $versionId, int $callbackType, string $ip, string $path, int $status = 1): void
    {
        VersionCallback::create([
            'product_id' => $productId,
            'version_id' => $versionId,
            'callback_type' => $callbackType,
            'callback_ip' => $ip,
            'callback_path' => $path,
            'callback_date_time' => now(),
            'callback_status' => $status,
        ]);
    }

    /**
     * Filter sensitive elements from response data.
     * Original removes: product_key, version files/queries, counts, comments.
     *
     * @param  array<mixed>  $data
     * @param  array<mixed>  $extraKeysToRemove
     * @return array<mixed>
     */
    protected function filterSensitiveData(array $data, array $extraKeysToRemove = []): array
    {
        $keysToRemove = array_merge([
            'product_key',
            'version_install_file',
            'version_install_query',
            'version_raw_install_query',
            'version_upgrade_file',
            'version_upgrade_query',
            'version_raw_upgrade_query',
            'version_install_count',
            'version_upgrade_count',
            'version_comments',
        ], $extraKeysToRemove);

        $flipped = array_flip($keysToRemove);
        $filtered = array_diff_key($data, $flipped);

        // Recursively filter nested arrays (e.g. product_versions)
        foreach ($filtered as $key => $value) {
            if (is_array($value)) {
                if (array_is_list($value)) {
                    $filtered[$key] = array_map(fn ($item) => is_array($item)
                        ? array_diff_key($item, $flipped)
                        : $item, $value);
                } else {
                    $filtered[$key] = array_diff_key($value, $flipped);
                }
            }
        }

        return $filtered;
    }
}
