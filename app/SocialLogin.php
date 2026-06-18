<?php

declare(strict_types=1);

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $type
 * @property string $client_id
 * @property string|null $client_secret
 * @property string|null $redirect_url
 * @property int|null $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialLogin newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialLogin newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialLogin query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialLogin whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialLogin whereClientSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialLogin whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialLogin whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialLogin whereRedirectUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialLogin whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialLogin whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialLogin whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class SocialLogin extends Model
{
    use HasFactory;

    protected $table = 'social_logins';

    protected $fillable = [
        'type',
        'client_id',
        'client_secret',
        'redirect_url',
        'status',
    ];
}
