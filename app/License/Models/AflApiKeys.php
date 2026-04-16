<?php

namespace App\License\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AflApiKeys extends Model
{
    use HasFactory;

    protected $fillable = [
        'api_key_secret',
        'api_key_ip',
        'api_key_clients_add',
        'api_key_clients_edit',
        'api_key_licenses_add',
        'api_key_licenses_edit',
        'api_key_products_add',
        'api_key_products_edit',
        'api_key_installations_edit',
        'api_key_search',
        'api_key_status',
        'api_key_versions_add',
        'api_key_verisons_edit',
        'api_key_search',
        'api_key_status',
        'api_key_description',
    ];

    protected $primaryKey = 'api_key_id';
}
