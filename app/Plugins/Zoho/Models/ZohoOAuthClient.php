<?php

namespace App\Plugins\Zoho\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ZohoOAuthClient extends Model
{
    protected $table = 'zoho_oauth_clients';

    protected $fillable = [
        'integration_id',
        'client_id',
        'client_secret',
        'redirect_uri',
        'region',
    ];

    protected $hidden = [
        'client_secret',
    ];

    /**
     * Parent integration.
     */
    public function integration(): BelongsTo
    {
        return $this->belongsTo(ZohoIntegration::class, 'integration_id');
    }
}
