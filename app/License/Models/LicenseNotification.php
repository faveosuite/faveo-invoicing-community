<?php

namespace App\License\Models;

use Illuminate\Database\Eloquent\Model;

class LicenseNotification extends Model
{
    protected $table = 'license_notifications';

    protected $fillable = [
        'notification_product_not_found',
        'notification_product_inactive',
        'notification_license_ok',
        'notification_license_not_found',
        'notification_invalid_ip',
        'notification_invalid_domain',
        'notification_domain_required',
        'notification_domain_in_use',
        'notification_license_suspended',
        'notification_license_expired',
        'notification_updates_expired',
        'notification_support_expired',
        'notification_license_cancelled',
        'notification_license_limit',
        'notification_installation_not_found',
        'notification_invalid_signature',
        'notification_host_banned',
        'notification_unknown_error',
    ];
}
