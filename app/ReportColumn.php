<?php

declare(strict_types=1);

namespace App;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string|null $key
 * @property string|null $label
 * @property string|null $type
 * @property string|null $default
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, UserLinkReport> $userLinkReports
 * @property-read int|null $user_link_reports_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReportColumn newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReportColumn newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReportColumn query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReportColumn whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReportColumn whereDefault($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReportColumn whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReportColumn whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReportColumn whereLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReportColumn whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReportColumn whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class ReportColumn extends Model
{
    /**
     * @use HasFactory<Factory>
     */
    use HasFactory;

    protected $table = 'report_columns';

    protected $fillable = [
        'key',
        'label',
        'type',
        'default',
    ];

    /**
     * @return HasMany<UserLinkReport, $this>
     */
    public function userLinkReports(): HasMany
    {
        return $this->hasMany(UserLinkReport::class, 'column_id');
    }
}
