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
 */
class SocialAccount extends Model
{
    protected $table = 'social_accounts';

    protected $fillable = ['user_id', 'provider', 'provider_id'];
}
