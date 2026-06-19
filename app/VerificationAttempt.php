<?php

declare(strict_types=1);

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property string $mobile_attempt
 * @property int $email_attempt
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VerificationAttempt newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VerificationAttempt newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VerificationAttempt query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VerificationAttempt whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VerificationAttempt whereEmailAttempt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VerificationAttempt whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VerificationAttempt whereMobileAttempt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VerificationAttempt whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VerificationAttempt whereUserId($value)
 * @mixin \Eloquent
 */
class VerificationAttempt extends Model
{
    /**
     * @use HasFactory<\Illuminate\Database\Eloquent\Factories\Factory>
     */
    use HasFactory;

    protected $table = 'verification_attempts';

    protected $primaryKey = 'user_id';

    protected $fillable = ['user_id', 'mobile_attempt', 'email_attempt'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<User, $this>
     */
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
