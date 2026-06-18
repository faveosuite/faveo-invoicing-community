<?php

namespace App\Plugins\Zoho\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $zoho_field_id
 * @property int|null $faveo_local_field_id
 * @property int $is_active
 * @property string|null $default_value
 * @property int $use_default_if_empty
 * @property string|null $option_mapping
 * @property string|null $selected_option
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Plugins\Zoho\Models\FaveoLocalFields|null $faveoLocalField
 * @property-read \App\Plugins\Zoho\Models\ZohoFields $zohoField
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ZohoFieldMappings newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ZohoFieldMappings newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ZohoFieldMappings query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ZohoFieldMappings whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ZohoFieldMappings whereDefaultValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ZohoFieldMappings whereFaveoLocalFieldId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ZohoFieldMappings whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ZohoFieldMappings whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ZohoFieldMappings whereOptionMapping($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ZohoFieldMappings whereSelectedOption($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ZohoFieldMappings whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ZohoFieldMappings whereUseDefaultIfEmpty($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ZohoFieldMappings whereZohoFieldId($value)
 * @mixin \Eloquent
 */
class ZohoFieldMappings extends Model
{
    protected $table = 'zoho_field_mappings';

    protected $guarded = [];

    /**
     * @return BelongsTo<FaveoLocalFields, $this>
     */
    public function faveoLocalField(): BelongsTo
    {
        return $this->belongsTo(
            FaveoLocalFields::class,
            'faveo_local_field_id',
            'id'
        );
    }

    /**
     * @return BelongsTo<ZohoFields, $this>
     */
    public function zohoField(): BelongsTo
    {
        return $this->belongsTo(
            ZohoFields::class,
            'zoho_field_id',
            'id'
        );
    }
}
