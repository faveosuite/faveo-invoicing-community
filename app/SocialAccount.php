<?php

declare(strict_types=1);

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Links a user to an account at an OAuth provider (google, github, twitter, linkedin).
 *
 * @property int $id
 * @property int $user_id
 * @property string $provider
 * @property string $provider_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialAccount newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialAccount newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialAccount query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialAccount whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialAccount whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialAccount whereProvider($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialAccount whereProviderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialAccount whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialAccount whereUserId($value)
 *
 * @mixin \Eloquent
 */
class SocialAccount extends Model
{
    protected $table = 'social_accounts';

    protected $fillable = ['user_id', 'provider', 'provider_id'];
}
