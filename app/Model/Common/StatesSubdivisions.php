<?php

declare(strict_types=1);

namespace App\Model\Common;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $state_subdivision_id
 * @property string $state_subdivision_name
 * @property string $country_code
 * @property string|null $iso2
 * @property string|null $primary_level_name
 * @property numeric|null $latitude
 * @property numeric|null $longitude
 * @property int $country_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StatesSubdivisions newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StatesSubdivisions newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StatesSubdivisions query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StatesSubdivisions whereCountryCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StatesSubdivisions whereCountryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StatesSubdivisions whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StatesSubdivisions whereIso2($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StatesSubdivisions whereLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StatesSubdivisions whereLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StatesSubdivisions wherePrimaryLevelName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StatesSubdivisions whereStateSubdivisionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StatesSubdivisions whereStateSubdivisionName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StatesSubdivisions whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class StatesSubdivisions extends Model
{
    protected $table = 'states_subdivisions';

    protected $fillable = ['country_code_char2', 'country_code_char3', 'state_subdivision_name', 'state_subdivision_alternate_names',
        'primary_level_name', 'state_subdivision_code'];
}
