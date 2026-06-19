<?php

declare(strict_types=1);

namespace App\License\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string|null $notification_operation_ok
 * @property string|null $notification_product_not_found
 * @property string|null $notification_product_inactive
 * @property string|null $notification_product_no_versions
 * @property string|null $notification_version_not_found
 * @property string|null $notification_version_inactive
 * @property string|null $notification_version_expired
 * @property string|null $notification_install_limit_reached
 * @property string|null $notification_upgrade_limit_reached
 * @property string|null $notification_install_archive_not_found
 * @property string|null $notification_install_query_not_found
 * @property string|null $notification_upgrade_archive_not_found
 * @property string|null $notification_upgrade_query_not_found
 * @property string|null $notification_raw_install_query_not_found
 * @property string|null $notification_raw_upgrade_query_not_found
 * @property string|null $notification_installation_not_verified
 * @property string|null $notification_invalid_parameter
 * @property string|null $notification_invalid_signature
 * @property string|null $notification_host_banned
 * @property string|null $notification_unknown_error
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VersionNotification newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VersionNotification newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VersionNotification query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VersionNotification whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VersionNotification whereNotificationHostBanned($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VersionNotification whereNotificationInstallArchiveNotFound($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VersionNotification whereNotificationInstallLimitReached($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VersionNotification whereNotificationInstallQueryNotFound($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VersionNotification whereNotificationInstallationNotVerified($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VersionNotification whereNotificationInvalidParameter($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VersionNotification whereNotificationInvalidSignature($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VersionNotification whereNotificationOperationOk($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VersionNotification whereNotificationProductInactive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VersionNotification whereNotificationProductNoVersions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VersionNotification whereNotificationProductNotFound($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VersionNotification whereNotificationRawInstallQueryNotFound($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VersionNotification whereNotificationRawUpgradeQueryNotFound($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VersionNotification whereNotificationUnknownError($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VersionNotification whereNotificationUpgradeArchiveNotFound($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VersionNotification whereNotificationUpgradeLimitReached($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VersionNotification whereNotificationUpgradeQueryNotFound($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VersionNotification whereNotificationVersionExpired($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VersionNotification whereNotificationVersionInactive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VersionNotification whereNotificationVersionNotFound($value)
 * @mixin \Eloquent
 */
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
