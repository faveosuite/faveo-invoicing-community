<?php

namespace App\Plugins\Zoho\Models;

use Override;
use Illuminate\Database\Eloquent\Model;

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
