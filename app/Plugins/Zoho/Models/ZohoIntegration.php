<?php

namespace App\Plugins\Zoho\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Override;

/**
 * @property int $id
 * @property string $platform
 * @property string|null $description
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Plugins\Zoho\Models\ZohoOAuthClient|null $client
 * @property-read \App\Plugins\Zoho\Models\ZohoOAuthToken|null $token
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ZohoIntegration newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ZohoIntegration newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ZohoIntegration query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ZohoIntegration whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ZohoIntegration whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ZohoIntegration whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ZohoIntegration whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ZohoIntegration wherePlatform($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ZohoIntegration whereUpdatedAt($value)
 * @mixin \Eloquent
 */
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
     *
     * @return HasOne<ZohoOAuthToken, $this>
     */
    public function token(): HasOne
    {
        return $this->hasOne(ZohoOAuthToken::class, 'integration_id');
    }

    /**
     * OAuth client credentials.
     *
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
