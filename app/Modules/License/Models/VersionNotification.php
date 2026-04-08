<?php

namespace App\Modules\License\Models;

use Illuminate\Database\Eloquent\Model;

class VersionNotification extends Model
{
    protected $table = 'version_notifications';

    protected $fillable = [
        'notification_version_ok',
        'notification_version_not_found',
        'notification_update_available',
        'notification_no_update',
        'notification_update_failed',
        'notification_invalid_request',
        'notification_banned_host',
        'notification_connection_ok',
        'notification_connection_failed',
    ];
}
