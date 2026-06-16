<?php

namespace App\Plugins\Zoho\Models;

use Override;
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

    /**
     * OAuth token for this integration.
     * @return HasOne<ZohoOAuthToken, $this>
     */
    public function token(): HasOne
    {
        return $this->hasOne(ZohoOAuthToken::class, 'integration_id');
    }

    /**
     * OAuth client credentials.
     * @return HasOne<ZohoOAuthClient, $this>
     */
    public function client(): HasOne
    {
        return $this->hasOne(ZohoOAuthClient::class, 'integration_id');
    }
    #[Override]
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
