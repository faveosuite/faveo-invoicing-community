<?php

namespace App\Model\Payment;

use Illuminate\Database\Eloquent\Model;
use Override;

/**
 * @property int $id
 * @property string $name
 * @property string $days
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Model\Payment\Plan> $plans
 * @property-read int|null $plans_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Period newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Period newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Period query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Period whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Period whereDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Period whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Period whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Period whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Period extends Model
{
    protected $table = 'periods';

    protected $fillable = ['name', 'days'];

    public function plans(): mixed
    {
        return $this->belongstoMany(Plan::class, 'plans_periods_relation')->withTimestamps();
    }

    #[Override]
    public function delete(): bool
    {
        $this->plans()->detach();

        return (bool) parent::delete();
    }
}
