<?php

declare(strict_types=1);

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property string $type
 * @property string $column_id
 * @property int $order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\ReportColumn|null $reportColumn
 * @property-read \App\User|null $user
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
    use HasFactory;

    protected $table = 'users_link_reports';

    protected $fillable = [
        'user_id',
        'column_id',
        'type',
        'order',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reportColumn()
    {
        return $this->belongsTo(ReportColumn::class, 'column_id');
    }
}
