<?php

declare(strict_types=1);

namespace App\Model\Product;

use App\License\Models\License;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Override;

/**
 * One right an order has: a feature code with its value and dates, and where it came from. Owned
 * by the order's subscription, delivered to its license when it has one. See the entitlements
 * migration for the sources.
 *
 * @property int $id
 * @property int|null $subscription_id
 * @property int|null $license_id
 * @property int|null $user_id
 * @property string $code
 * @property string|null $value
 * @property string $source
 * @property Carbon|null $starts_at
 * @property Carbon|null $ends_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read License|null $license
 *
 * @mixin \Eloquent
 */
class Grant extends Model
{
    public const string PLAN = 'plan';

    public const string EXTRA = 'extra';

    public const string ADMIN = 'admin';

    /** A date feature (updates, support, …): ends_at is when it ends, null = never. */
    public const string DATE = 'date';

    protected $fillable = ['subscription_id', 'license_id', 'user_id', 'code', 'value', 'source', 'starts_at', 'ends_at'];

    /**
     * @return BelongsTo<License, $this>
     */
    public function license(): BelongsTo
    {
        return $this->belongsTo(License::class);
    }

    /**
     * Grants in force today.
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeActive(Builder $query): Builder
    {
        $today = now()->toDateString();

        return $query->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', $today))
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', $today));
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'subscription_id' => 'integer',
            'license_id' => 'integer',
            'user_id' => 'integer',
            'starts_at' => 'date',
            'ends_at' => 'date',
        ];
    }
}
