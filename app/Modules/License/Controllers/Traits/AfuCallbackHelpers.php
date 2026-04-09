<?php

namespace App\Modules\License\Controllers\Traits;

use App\Modules\License\Models\VersionCallback;
use App\Modules\License\Models\VersionNotification;

trait AfuCallbackHelpers
{
    /**
     * Build notification response with headers (same format as license callbacks)
     */
    protected function notificationResponse(string $notificationCase, array $data = [])
    {
        return response()->json([])
            ->header('notification_case', $notificationCase)
            ->header('notification_text', $this->getNotificationText($notificationCase))
            ->header('notification_server_signature', $this->generateSignature())
            ->header('notification_data', json_encode($data));
    }

    /**
     * Get notification text from version_notifications table
     */
    protected function getNotificationText(string $case): string
    {
        $notification = VersionNotification::first();
        return $notification ? ($notification->{$case} ?? $case) : $case;
    }

    /**
     * Generate signature for version callbacks
     */
    protected function generateSignature(?int $productId = null): string
    {
        $rootUrl = url('/');
        $rootIps = @gethostbynamel(str_ireplace('www.', '', parse_url($rootUrl, PHP_URL_HOST) ?? ''));

        if (empty($rootIps)) {
            return '';
        }

        return hash('sha256', implode('', $rootIps) . $productId . gmdate('Y-m-d'));
    }

    /**
     * Log version callback
     */
    protected function logCallback(int $productId, int $versionId, string $type, string $ip, string $path): void
    {
        VersionCallback::create([
            'product_id'      => $productId,
            'version_id'      => $versionId,
            'callback_type'   => $type,
            'callback_ip'     => $ip,
            'callback_path'   => $path,
            'callback_date_time' => now(),
            'callback_status' => 1,
        ]);
    }
}
