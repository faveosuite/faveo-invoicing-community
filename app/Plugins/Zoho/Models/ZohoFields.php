<?php

namespace App\Plugins\Zoho\Models;

use Illuminate\Database\Eloquent\Model;

class ZohoFields extends Model
{
    protected $table = 'zoho_fields';

    /**
     * Mass assignable attributes.
     */
    protected $fillable = [
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
    protected $casts = [
        'is_mandatory' => 'boolean',
        'raw_metadata' => 'array',
    ];
}
