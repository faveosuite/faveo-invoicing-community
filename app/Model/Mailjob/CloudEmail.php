<?php

declare(strict_types=1);

namespace App\Model\Mailjob;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $result_message
 * @property string $user
 * @property string $result_password
 * @property string $domain
 * @property int $counter
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CloudEmail newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CloudEmail newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CloudEmail query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CloudEmail whereCounter($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CloudEmail whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CloudEmail whereDomain($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CloudEmail whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CloudEmail whereResultMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CloudEmail whereResultPassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CloudEmail whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CloudEmail whereUser($value)
 *
 * @mixin \Eloquent
 */
class CloudEmail extends Model
{
    protected $table = 'cloud_email';

    protected $guarded = [];
}
