<?php

namespace App\License\Controllers\Traits;

use App\License\Models\VersionCallback;
use App\License\Models\VersionNotification;

trait AfuCallbackHelpers
{
    /**
     * Build notification response with headers (same format as license callbacks).
     * Original only sends notification_data when notification_case is 'notification_operation_ok'.
     */
    protected function notificationResponse(string $notificationCase, array $data = []): \Illuminate\Http\JsonResponse
    {
        // Only returns notification_data when everything is OK
        $responseData = ($notificationCase === 'notification_operation_ok') ? $data : [];

        return response()->json($responseData)
            ->header('notification_case', $notificationCase)
            ->header('notification_text', $this->getNotificationText($notificationCase))
            ->header('notification_server_signature', $this->generateSignature());
    }

    /**
     * Get notification text from version_notifications table.
     */
    protected function getNotificationText(string $case): string
    {
        $notification = VersionNotification::first();

        return $notification ? ($notification->{$case} ?? $case) : $case;
    }

    /**
     * Generate signature for version callbacks.
     * Original: hash('sha256', implode('', $root_ips_array) . $product_key . $product_id . gmdate('Y-m-d')).
     */
    protected function generateSignature(?int $productId = null, ?string $productKey = null): string
    {
        $rootUrl = url('/');
        $rootIps = @gethostbynamel(str_ireplace('www.', '', parse_url($rootUrl, PHP_URL_HOST) ?? ''));

        if (! is_array($rootIps)) {
            $rootIps = [];
        }

        sort($rootIps);

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
