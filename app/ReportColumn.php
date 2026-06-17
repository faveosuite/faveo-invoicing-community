<?php

declare(strict_types=1);

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string|null $key
 * @property string|null $label
 * @property string|null $type
 * @property string|null $default
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\UserLinkReport> $userLinkReports
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
    use HasFactory;

    protected $table = 'report_columns';

    protected $fillable = [
        'key',
        'label',
        'type',
        'default',
    ];

    public function userLinkReports()
    {
        return $this->hasMany(UserLinkReport::class, 'column_id');
    }
}
