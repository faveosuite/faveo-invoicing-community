<?php

declare(strict_types=1);

namespace App\Model\Product;

use App\Model\Payment\Plan;
use App\Services\DateRules;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Override;

/**
 * One feature a license can grant: an on/off feature, a numeric limit, a choice from a list, or
 * free text. One shared list for all products; plans pick the features they include and set
 * their extra prices. `code` is the permanent name products check (sla, agents); it never changes —
 * rename the label instead. `category` only groups features on admin screens.
 *
 * @property int $id
 * @property string $code
 * @property string|null $category
 * @property string $label
 * @property string $type
 * @property list<string>|null $options
 * @property string|null $default_value
 * @property bool $is_active
 * @property int $sort
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, EntitlementPrice> $prices
 *
 * @mixin \Eloquent
 */
class Entitlement extends Model
{
    public const array TYPES = ['boolean', 'number', 'select', 'text', 'date'];

    protected $fillable = ['code', 'category', 'label', 'type', 'options', 'default_value', 'is_active', 'sort'];

    /**
     * @return HasMany<EntitlementPrice, $this>
     */
    public function prices(): HasMany
    {
        return $this->hasMany(EntitlementPrice::class);
    }

    /**
     * Price of one unit on a plan in a currency, or null when the plan doesn't sell it there.
     * `purchase` = first purchase less the offer; `renewal` = the renewal price (falls back to
     * the purchase price); `list` = first purchase before the offer.
     *
     * @param  'purchase'|'renewal'|'list'  $kind
     */
    public function priceIn(string $currency, ?Plan $plan, string $kind = 'purchase'): ?float
    {
        $price = $plan ? $this->prices->first(fn (EntitlementPrice $p): bool => $p->plan_id === $plan->id && $p->currency === $currency) : null;
        if (! $price) {
            return null;
        }

        return match ($kind) {
            'renewal' => (float) ($price->renew_price ?? $price->unit_price),
            'list' => (float) $price->unit_price,
            default => (float) $price->unit_price * (1 - (float) ($price->offer_percent ?? 0) / 100),
        };
    }

    /** Can be bought as an extra on this plan: an active number or on/off feature the plan has a price for. Agents are bought through the agent count, not here. */
    public function canBeSoldOn(?Plan $plan): bool
    {
        return $plan !== null && $this->is_active
            && in_array($this->type, ['boolean', 'number'], strict: true)
            && $this->code !== 'agents'
            && $this->prices->contains(fn (EntitlementPrice $p): bool => $p->plan_id === $plan->id);
    }

    /** Stored string → the typed value the product receives. */
    public function cast(?string $raw): bool|int|string|null
    {
        if ($raw === null) {
            return $this->type === 'boolean' ? false : null;
        }

        return match ($this->type) {
            'boolean' => $raw === '1',
            'number' => (int) $raw,
            default => $raw,
        };
    }

    /**
     * Validates a value from the admin screen for this item's type and returns it in stored
     * form, or null for "not set". Returns false when the value doesn't fit the type.
     */
    public function normalize(mixed $value): string|false|null
    {
        if ($value === null || $value === '') {
            return null;
        }

        return match ($this->type) {
            'boolean' => in_array($value, [true, 1, '1', 'true'], strict: true) ? '1'
                : (in_array($value, [false, 0, '0', 'false'], strict: true) ? '0' : false),
            'number' => filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]) !== false ? (string) (int) $value : false,
            'select' => is_string($value) && in_array($value, $this->options ?? [], strict: true) ? $value : false,
            'date' => DateRules::normalize($value), // how the plan ends it, not a value
            default => is_scalar($value) && mb_strlen((string) $value) <= 255 ? (string) $value : false,
        };
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'options' => 'array',
            'is_active' => 'boolean',
            'sort' => 'integer',
        ];
    }
}
