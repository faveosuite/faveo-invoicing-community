<?php

namespace App\Model\Payment;

use App\BaseModel;
use App\Model\Common\Country;
use App\Model\Common\State;
use App\Traits\SystemActivityLogsTrait;
use Override;

/**
 * A generic tax rate (WooCommerce-style). See the create_tax_rates migration
 * for the semantics of priority / compound / tax_class.
 *
 * @property int $id
 * @property string $name
 * @property string $country
 * @property string $state
 * @property float $rate
 * @property int $priority
 * @property bool $compound
 * @property string $tax_class
 * @property int $display_order
 * @property bool $active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activitiesAsSubject
 * @property-read int|null $activities_as_subject_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Model\Payment\TaxRateLocation> $locations
 * @property-read int|null $locations_count
 * @property-read \App\Model\Payment\TaxClass|null $taxClass
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxRate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxRate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxRate query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxRate whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxRate whereCompound($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxRate whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxRate whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxRate whereDisplayOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxRate whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxRate whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxRate wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxRate whereRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxRate whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxRate whereTaxClass($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxRate whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class TaxRate extends BaseModel
{
    use SystemActivityLogsTrait;

    protected $table = 'tax_rates';

    protected $fillable = [
        'name', 'country', 'state', 'rate', 'priority',
        'compound', 'tax_class', 'display_order', 'active',
    ];

    protected string $logName = 'tax';

    protected string $logNameColumn = 'name';

    protected array $logAttributes = [
        'name', 'country', 'state', 'rate', 'priority', 'compound', 'tax_class', 'active',
    ];

    protected array $logUrl = [
        'segments' => ['tax', ':id', 'edit'],
    ];

    protected function getMappings(): array
    {
        return [
            'name' => ['Tax Name', fn ($value) => $value],
            'country' => [
                'Country',
                fn ($value) => $value
                    ? Country::where('country_code_char2', $value)->value('country_name')
                    : 'All Countries',
            ],
            'state' => [
                'State',
                fn ($value) => $value
                    ? State::where('iso2', $value)->value('state_subdivision_name')
                    : 'All States',
            ],
            'rate' => ['Tax Rate (%)', fn ($value) => $value],
            'priority' => ['Priority', fn ($value) => $value],
            'compound' => ['Is Compound Tax', fn ($value): string => $value ? 'Yes' : 'No'],
            'tax_class' => ['Tax Class', fn ($value) => $value ?: 'Standard'],
            'active' => ['Tax Status', fn ($value): array|string|null => $value ? __('message.active') : __('message.inactive')],
        ];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Model\Payment\TaxRateLocation, $this>
     */
    public function locations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(TaxRateLocation::class, 'tax_rate_id');
    }

    public function taxClass()
    {
        return $this->belongsTo(TaxClass::class, 'tax_class', 'slug');
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'rate' => 'float',
            'priority' => 'integer',
            'compound' => 'boolean',
            'display_order' => 'integer',
            'active' => 'boolean',
        ];
    }
}
