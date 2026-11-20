<?php

declare(strict_types=1);

namespace App\Model\Cloud;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Override;

/**
 * One event in the outbox to Faveo Cloud (see the cloud_events migration).
 *
 * @property string $id
 * @property string $type
 * @property string|null $tenant
 * @property array<string, mixed> $payload
 * @property int $attempts
 * @property Carbon|null $next_attempt_at
 * @property Carbon|null $sent_at
 * @property Carbon|null $failed_at
 * @property string|null $last_error
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @mixin \Eloquent
 */
class CloudEvent extends Model
{
    use HasUuids;

    protected $fillable = ['type', 'tenant', 'payload', 'attempts', 'next_attempt_at', 'sent_at', 'failed_at', 'last_error'];

    #[Override]
    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'attempts' => 'integer',
            'next_attempt_at' => 'datetime',
            'sent_at' => 'datetime',
            'failed_at' => 'datetime',
        ];
    }
}
