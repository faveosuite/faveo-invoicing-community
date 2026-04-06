<?php

namespace App\License\Models;

use Illuminate\Database\Eloquent\Model;

class VersionNotification extends Model
{
    protected $table = 'version_notifications';

    public $timestamps = false;

    protected $fillable = [
        'notification_operation_ok',
        'notification_product_not_found',
        'notification_product_inactive',
        'notification_product_no_versions',
        'notification_version_not_found',
        'notification_version_inactive',
        'notification_version_expired',
        'notification_install_limit_reached',
        'notification_upgrade_limit_reached',
        'notification_install_archive_not_found',
        'notification_install_query_not_found',
        'notification_upgrade_archive_not_found',
        'notification_upgrade_query_not_found',
        'notification_raw_install_query_not_found',
        'notification_raw_upgrade_query_not_found',
        'notification_installation_not_verified',
        'notification_invalid_parameter',
        'notification_invalid_signature',
        'notification_host_banned',
        'notification_unknown_error',
    ];
}
