<?php

declare(strict_types=1);

namespace App\License\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string|null $notification_product_not_found
 * @property string|null $notification_product_inactive
 * @property string|null $notification_license_ok
 * @property string|null $notification_license_not_found
 * @property string|null $notification_invalid_ip
 * @property string|null $notification_invalid_domain
 * @property string|null $notification_domain_required
 * @property string|null $notification_domain_in_use
 * @property string|null $notification_license_suspended
 * @property string|null $notification_license_expired
 * @property string|null $notification_updates_expired
 * @property string|null $notification_support_expired
 * @property string|null $notification_license_cancelled
 * @property string|null $notification_license_limit
 * @property string|null $notification_installation_not_found
 * @property string|null $notification_invalid_signature
 * @property string|null $notification_host_banned
 * @property string|null $notification_unknown_error
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseNotification newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseNotification newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseNotification query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseNotification whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseNotification whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseNotification whereNotificationDomainInUse($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseNotification whereNotificationDomainRequired($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseNotification whereNotificationHostBanned($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseNotification whereNotificationInstallationNotFound($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseNotification whereNotificationInvalidDomain($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseNotification whereNotificationInvalidIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseNotification whereNotificationInvalidSignature($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseNotification whereNotificationLicenseCancelled($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseNotification whereNotificationLicenseExpired($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseNotification whereNotificationLicenseLimit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseNotification whereNotificationLicenseNotFound($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseNotification whereNotificationLicenseOk($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseNotification whereNotificationLicenseSuspended($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseNotification whereNotificationProductInactive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseNotification whereNotificationProductNotFound($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseNotification whereNotificationSupportExpired($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseNotification whereNotificationUnknownError($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseNotification whereNotificationUpdatesExpired($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseNotification whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
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
