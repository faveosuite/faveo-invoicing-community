<?php

namespace App\License\Models;

use App\Model\Product\Product;
use App\User;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

/**
 * @property int $id
 * @property int|null $product_id
 * @property int|null $user_id
 * @property string|null $license_code
 * @property \Illuminate\Support\Carbon $report_date_time
 * @property string|null $report_text
 * @property int|null $report_system
 * @property int $report_status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\License\Models\License|null $license
 * @property-read Product|null $product
 * @property-read User|null $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseReport newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseReport newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseReport pending()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseReport query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseReport whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseReport whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseReport whereLicenseCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseReport whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseReport whereReportDateTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseReport whereReportStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseReport whereReportSystem($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseReport whereReportText($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseReport whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseReport whereUserId($value)
 *
 * @mixin \Eloquent
 */
class LicenseReport extends Model
{
    protected $table = 'license_reports';

    protected $fillable = [
        'product_id',
        'user_id',
        'license_code',
        'report_date_time',
        'report_text',
        'report_system',
        'report_status',
    ];

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return BelongsTo<License, $this>
     */
    public function license(): BelongsTo
    {
        return $this->belongsTo(License::class, 'license_code', 'license_code');
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<\Illuminate\Database\Eloquent\Model>  $query
     */
    #[Scope]
    protected function pending(\Illuminate\Database\Eloquent\Builder $query): mixed
    {
        return $query->where('report_status', 0);
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'report_date_time' => 'datetime',
            'report_system' => 'integer',
            'report_status' => 'integer',
            'product_id' => 'integer',
            'user_id' => 'integer',
        ];
    }
}
