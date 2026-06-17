<?php

declare(strict_types=1);

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property string $backup_codes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserBackupCodes newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserBackupCodes newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserBackupCodes query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserBackupCodes whereBackupCodes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserBackupCodes whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserBackupCodes whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserBackupCodes whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserBackupCodes whereUserId($value)
 * @mixin \Eloquent
 */
class UserBackupCodes extends Model
{
    protected $table = 'user_backup_codes';

    protected $fillable = ['user_id', 'backup_codes'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
