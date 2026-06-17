<?php

declare(strict_types=1);

namespace App\Model\Payment;

use App\BaseModel;
use App\Model\Common\Country;
use App\Traits\SystemActivityLogsTrait;
use Illuminate\Database\Eloquent\Builder;
use Override;

/**
 * @property int $id
 * @property string|null $code
 * @property string|null $symbol
 * @property string|null $name
 * @property string|null $dashboard_currency
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $status
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activitiesAsSubject
 * @property-read int|null $activities_as_subject_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Country> $country
 * @property-read int|null $country_count
 *
 * @method static Builder<static>|Currency newModelQuery()
 * @method static Builder<static>|Currency newQuery()
 * @method static Builder<static>|Currency query()
 * @method static Builder<static>|Currency whereCode($value)
 * @method static Builder<static>|Currency whereCreatedAt($value)
 * @method static Builder<static>|Currency whereDashboardCurrency($value)
 * @method static Builder<static>|Currency whereId($value)
 * @method static Builder<static>|Currency whereName($value)
 * @method static Builder<static>|Currency whereStatus($value)
 * @method static Builder<static>|Currency whereSymbol($value)
 * @method static Builder<static>|Currency whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Currency extends BaseModel
{
    use SystemActivityLogsTrait;

    protected $table = 'currencies';

    protected $fillable = ['code', 'symbol', 'name', 'status', 'id', 'dashboard_currency'];

    protected string $logName = 'currency';

    protected string $logNameColumn = 'name';

    protected array $logAttributes = [
        'code', 'symbol', 'name', 'status',
    ];

    protected array $logUrl = [
        'segments' => ['currency'],
    ];

    #[Override]
    protected static function booted(): void
    {
        static::addGlobalScope('active_country', function (Builder $builder): void {
            $builder->whereHas('country', function (\Illuminate\Contracts\Database\Query\Builder $query): void {
                $query->where('status', operator: true);
            });
        });
    }

    protected function getMappings(): array
    {
        return [
            'code' => ['Currency Code', fn ($value) => $value],
            'symbol' => ['Currency Symbol', fn ($value) => $value],
            'name' => ['Currency Name', fn ($value) => $value],
            'status' => [$this->name.' currency status', fn ($value): array|string|null => $value === 1 ? __('message.active') : __('message.inactive')],
        ];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Model\Common\Country, $this>
     */
    public function country(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Country::class, 'currency_id');
    }
}
