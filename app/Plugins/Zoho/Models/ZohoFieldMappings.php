<?php

namespace App\Plugins\Zoho\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ZohoFieldMappings extends Model
{
    protected $table = 'zoho_field_mappings';

    protected $guarded = [];

    public function faveoLocalField(): BelongsTo
    {
        return $this->belongsTo(
            FaveoLocalFields::class,
            'faveo_local_field_id',
            'id'
        );
    }

    public function zohoField(): BelongsTo
    {
        return $this->belongsTo(
            ZohoFields::class,
            'zoho_field_id',
            'id'
        );
    }
}
