<?php

namespace App\Plugins\Zoho\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ZohoIntegration extends Model
{
    protected $table = 'zoho_integrations';

    protected $fillable = [
        'platform',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * OAuth token for this integration.
     */
    public function token(): HasOne
    {
        return $this->hasOne(ZohoOAuthToken::class, 'integration_id');
    }

    /**
     * OAuth client credentials.
     */
    public function client(): HasOne
    {
        return $this->hasOne(ZohoOAuthClient::class, 'integration_id');
    }
}
