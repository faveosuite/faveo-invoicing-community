<?php

namespace App\Model\Payment;

use App\BaseModel;
use App\Model\Common\Country;
use App\Model\Common\State;
use App\Traits\SystemActivityLogsTrait;

/**
 * @property int $id
 * @property int $tax_classes_id
 * @property int $level
 * @property int $active
 * @property string $name
 * @property string $country
 * @property string $state
 * @property string $rate
 * @property int $compound
 * @property int $priority
 * @property int $apply_to_shipping
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $c_gst
 * @property string $s_gst
 * @property string $i_gst
 * @property string $ut_gst
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activitiesAsSubject
 * @property-read int|null $activities_as_subject_count
 * @property-read \App\Model\Payment\TaxClass|null $taxClass
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tax newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tax newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tax query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tax whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tax whereApplyToShipping($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tax whereCGst($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tax whereCompound($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tax whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tax whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tax whereIGst($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tax whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tax whereLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tax whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tax wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tax whereRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tax whereSGst($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tax whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tax whereTaxClassesId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tax whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tax whereUtGst($value)
 *
 * @mixin \Eloquent
 */
class Tax extends BaseModel
{
    use SystemActivityLogsTrait;

    protected $table = 'taxes';

    protected $fillable = ['level', 'name', 'country', 'state', 'rate', 'active', 'tax_classes_id', 'compound'];

    protected string $logName = 'tax';

    protected string $logNameColumn = 'name';

    protected array $logAttributes = [
        'level', 'name', 'country', 'state', 'rate', 'active', 'tax_classes_id', 'compound',
    ];

    protected array $logUrl = [
        'segments' => ['tax', ':id', 'edit'],
    ];

    protected function getMappings(): array
    {
        return [
            'level' => ['Tax Level', fn ($value): string => $value === 1 ? 'Country' : ($value === 2 ? 'State' : 'City')],
            'name' => ['Tax Name', fn ($value) => $value],
            'country' => ['Country', fn ($value) => Country::where('country_code_char2', $value)->value('country_name')],
            'state' => [
                'State',
                fn ($value) => $value
                    ? State::where('iso2', $value)->value('state_subdivision_name')
                    : 'All States',
            ],
            'rate' => ['Tax Rate (%)', fn ($value) => $value],
            'active' => [$this->name.' tax status', fn ($value): array|string|null => $value === 1 ? __('message.active') : __('message.inactive')],
            'tax_classes_id' => ['Tax Class', fn ($value) => $value ? TaxClass::find($value)?->name : 'No Class'],
            'compound' => ['Is Compound Tax', fn ($value): string => $value === 1 ? 'Yes' : 'No'],
        ];
    }

    public function taxClass()
    {
        return $this->belongsTo(TaxClass::class, 'tax_classes_id');
    }
}
