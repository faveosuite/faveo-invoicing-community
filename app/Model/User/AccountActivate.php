<?php

declare(strict_types=1);

namespace App\Model\User;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $email
 * @property string $token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccountActivate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccountActivate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccountActivate query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccountActivate whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccountActivate whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccountActivate whereToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccountActivate whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class AccountActivate extends Model
{
    protected $table = 'account_activates';

    protected $fillable = ['email', 'token'];

    protected $primaryKey = 'email';
}
