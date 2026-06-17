<?php

declare(strict_types=1);

namespace App\Plugins\Zoho\Models;

use Illuminate\Database\Eloquent\Model;
use Override;

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
