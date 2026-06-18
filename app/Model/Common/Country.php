<?php

declare(strict_types=1);

namespace App\Model\Common;

use App\BaseModel;
use App\Model\Payment\Currency;
use App\User;
use Illuminate\Database\Eloquent\Builder;
use Override;

/**
 * @property int $country_id
 * @property string|null $country_code_char2
 * @property string|null $country_code_char3
 * @property string $country_name
 * @property string|null $numcode
 * @property string|null $phonecode
 * @property string|null $capital
 * @property numeric|null $latitude
 * @property numeric|null $longitude
 * @property string|null $emoji
 * @property string|null $emojiU
 * @property int $currency_id
 * @property int $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Currency $currency
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Model\Common\State> $states
 * @property-read int|null $states_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, User> $users
 * @property-read int|null $users_count
 * @method static Builder<static>|Country newModelQuery()
 * @method static Builder<static>|Country newQuery()
 * @method static Builder<static>|Country query()
 * @method static Builder<static>|Country whereCapital($value)
 * @method static Builder<static>|Country whereCountryCodeChar2($value)
 * @method static Builder<static>|Country whereCountryCodeChar3($value)
 * @method static Builder<static>|Country whereCountryId($value)
 * @method static Builder<static>|Country whereCountryName($value)
 * @method static Builder<static>|Country whereCreatedAt($value)
 * @method static Builder<static>|Country whereCurrencyId($value)
 * @method static Builder<static>|Country whereEmoji($value)
 * @method static Builder<static>|Country whereEmojiU($value)
 * @method static Builder<static>|Country whereLatitude($value)
 * @method static Builder<static>|Country whereLongitude($value)
 * @method static Builder<static>|Country whereNumcode($value)
 * @method static Builder<static>|Country wherePhonecode($value)
 * @method static Builder<static>|Country whereStatus($value)
 * @method static Builder<static>|Country whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Country extends BaseModel
{
    protected $table = 'countries';

    protected $primaryKey = 'country_id';

    protected $fillable = [
        'country_id', 'country_code_char2', 'country_code_char3', 'country_name', 'numcode', 'capital', 'phonecode', 'latitude', 'longitude', 'emoji', 'emojiU', 'currency_id',
        'status',
    ];

    #[Override]
    protected static function booted(): void
    {
        static::addGlobalScope('status', function (Builder $builder): void {
            $builder->where('status', operator: true);
        });
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Model\Payment\Currency, $this>
     */
    public function currency(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function users()
    {
        return $this->hasMany(User::class, 'country', 'country_code_char2');
    }

    public function states()
    {
        return $this->hasMany(State::class, 'country_id', 'country_id');
    }
}
