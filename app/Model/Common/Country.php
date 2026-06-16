<?php

namespace App\Model\Common;

use Override;
use App\Model\Payment\Currency;
use App\User;
use App\BaseModel;
use Illuminate\Database\Eloquent\Builder;

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
            $builder->where('status', true);
        });
    }

    public function currency()
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
