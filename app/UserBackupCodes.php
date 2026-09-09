<?php

declare(strict_types=1);

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property string $backup_codes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User|null $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserBackupCodes newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserBackupCodes newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserBackupCodes query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserBackupCodes whereBackupCodes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserBackupCodes whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserBackupCodes whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserBackupCodes whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserBackupCodes whereUserId($value)
 *
 * @mixin \Eloquent
 */
class UserBackupCodes extends Model
{
    protected $table = 'user_backup_codes';

    protected $fillable = ['user_id', 'backup_codes'];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
