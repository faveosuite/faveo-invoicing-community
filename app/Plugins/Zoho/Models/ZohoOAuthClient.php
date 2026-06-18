<?php

namespace App\Plugins\Zoho\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $integration_id
 * @property string $client_id
 * @property string $client_secret
 * @property string $redirect_uri
 * @property string $region
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Plugins\Zoho\Models\ZohoIntegration $integration
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ZohoOAuthClient newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ZohoOAuthClient newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ZohoOAuthClient query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ZohoOAuthClient whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ZohoOAuthClient whereClientSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ZohoOAuthClient whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ZohoOAuthClient whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ZohoOAuthClient whereIntegrationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ZohoOAuthClient whereRedirectUri($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ZohoOAuthClient whereRegion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ZohoOAuthClient whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
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

    /**
     * Parent integration.
     *
     * @return BelongsTo<ZohoIntegration, $this>
     */
    public function integration(): BelongsTo
    {
        return $this->belongsTo(ZohoIntegration::class, 'integration_id');
    }
}
