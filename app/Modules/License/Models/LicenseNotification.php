<?php

namespace App\Modules\License\Models;

use Illuminate\Database\Eloquent\Model;

class LicenseNotification extends Model
{
    protected $table = 'license_notifications';

    protected $fillable = [
        'notification_product_not_found',
        'notification_license_ok',
        'notification_license_not_found',
        'notification_license_expired',
        'notification_license_suspended',
        'notification_license_limit_exceeded',
        'notification_installation_ok',
        'notification_installation_failed',
        'notification_updates_ok',
        'notification_updates_not_found',
        'notification_support_expired',
        'notification_domain_mismatch',
        'notification_ip_mismatch',
        'notification_invalid_request',
        'notification_banned_host',
        'notification_connection_ok',
        'notification_connection_failed',
    ];
}
