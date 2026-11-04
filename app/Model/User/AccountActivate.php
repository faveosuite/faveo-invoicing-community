<?php

declare(strict_types=1);

namespace App\Model\User;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;

/**
 * @property int $email
 * @property string $token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccountActivate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccountActivate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccountActivate query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccountActivate whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccountActivate whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccountActivate whereToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccountActivate whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class AccountActivate extends Model
{
    protected $table = 'account_activates';

    protected $fillable = ['email', 'token'];

    protected $primaryKey = 'email';

    /**
     * Email verification codes are stored hashed (like passwords): reading the table
     * doesn't reveal a pending code. Callers keep the plain code only to email it.
     *
     * @return Attribute<string, string>
     */
    protected function token(): Attribute
    {
        return Attribute::make(set: fn (mixed $value): string => Hash::make((string) $value));
    }

    /** Whether the code the user typed is this record's code. */
    public function matches(mixed $code): bool
    {
        $hash = (string) $this->getRawOriginal('token', $this->getAttributes()['token'] ?? '');

        // A pre-hashing (plain) row never matches: the user just requests a new code.
        return $hash !== '' && password_get_info($hash)['algo'] !== null && Hash::check((string) $code, $hash);
    }
}
