<?php

declare(strict_types=1);

namespace App\Plugins\Zoho\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Override;

/**
 * @property int $id
 * @property string $platform
 * @property string $module
 * @property string $zoho_field_uid
 * @property string $zoho_key
 * @property string $display_name
 * @property string|null $field_type
 * @property bool $is_mandatory
 * @property array<array-key, mixed> $raw_metadata
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ZohoFields newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ZohoFields newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ZohoFields query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ZohoFields whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ZohoFields whereDisplayName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ZohoFields whereFieldType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ZohoFields whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ZohoFields whereIsMandatory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ZohoFields whereModule($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ZohoFields wherePlatform($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ZohoFields whereRawMetadata($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ZohoFields whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ZohoFields whereZohoFieldUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ZohoFields whereZohoKey($value)
 *
 * @mixin \Eloquent
 */
class ZohoFields extends Model
{
    protected $table = 'zoho_fields';

    /**
     * Mass assignable attributes.
     */
    protected $fillable = [
        'id',

        'platform',
        'module',

        'zoho_field_uid',
        'zoho_key',

        'display_name',
        'field_type',

        'is_mandatory',
        'raw_metadata',
    ];

    /**
     * Attribute casting.
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'is_mandatory' => 'boolean',
            'raw_metadata' => 'array',
        ];
    }
}
