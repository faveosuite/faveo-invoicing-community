<?php

declare(strict_types=1);

namespace App;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property string $type
 * @property string $column_id
 * @property int $order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read ReportColumn|null $reportColumn
 * @property-read User|null $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserLinkReport newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserLinkReport newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserLinkReport query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserLinkReport whereColumnId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserLinkReport whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserLinkReport whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserLinkReport whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserLinkReport whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserLinkReport whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserLinkReport whereUserId($value)
 *
 * @mixin \Eloquent
 */
class UserLinkReport extends Model
{
    /**
     * @use HasFactory<Factory>
     */
    use HasFactory;

    protected $table = 'users_link_reports';

    protected $fillable = [
        'user_id',
        'column_id',
        'type',
        'order',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<ReportColumn, $this>
     */
    public function reportColumn(): BelongsTo
    {
        return $this->belongsTo(ReportColumn::class, 'column_id');
    }
}
