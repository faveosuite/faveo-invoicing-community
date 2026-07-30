<?php

declare(strict_types=1);

namespace App\Model\Payment;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Marks a gateway webhook event as processed, keyed on the gateway's own
 * event id — the unique constraint is what makes claiming one atomic (a
 * losing concurrent/redelivered claim throws, caught and treated as "already
 * processed"), not the create() call alone.
 *
 * @property int $id
 * @property string $gateway
 * @property string $event_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GatewayEvent newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GatewayEvent newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GatewayEvent query()
 *
 * @mixin \Eloquent
 */
class GatewayEvent extends Model
{
    protected $table = 'gateway_events';

    protected $fillable = ['gateway', 'event_id'];
}
