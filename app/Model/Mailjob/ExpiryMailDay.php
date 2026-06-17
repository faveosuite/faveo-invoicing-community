<?php

declare(strict_types=1);

namespace App\Model\Mailjob;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string|null $days
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $autorenewal_days
 * @property string|null $postexpiry_days
 * @property string|null $cloud_days
 * @property string $invoice_days
 * @property string|null $reoon_logs_days
 * @property int|null $msg91_days
 * @property int|null $system_logs_days
 * @property int|null $installation_logs_expire_days
 * @property int|null $license_reports_cleanup_days
 * @property int|null $license_callbacks_cleanup_days
 * @property int|null $license_crack_reports_cleanup_days
 * @property int|null $license_system_reports_cleanup_days
 * @property int|null $license_versions_cleanup_days
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpiryMailDay newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpiryMailDay newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpiryMailDay query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpiryMailDay whereAutorenewalDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpiryMailDay whereCloudDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpiryMailDay whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpiryMailDay whereDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpiryMailDay whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpiryMailDay whereInstallationLogsExpireDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpiryMailDay whereInvoiceDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpiryMailDay whereLicenseCallbacksCleanupDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpiryMailDay whereLicenseCrackReportsCleanupDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpiryMailDay whereLicenseReportsCleanupDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpiryMailDay whereLicenseSystemReportsCleanupDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpiryMailDay whereLicenseVersionsCleanupDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpiryMailDay whereMsg91Days($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpiryMailDay wherePostexpiryDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpiryMailDay whereReoonLogsDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpiryMailDay whereSystemLogsDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpiryMailDay whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class ExpiryMailDay extends Model
{
    protected $table = 'expiry_mail_days';

    protected $fillable = ['days', 'autorenewal_days', 'postexpiry_days', 'reoon_logs_days'];
}
