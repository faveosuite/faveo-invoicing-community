<?php

namespace App\Plugins\Zoho\Models;

use Attribute;
use Carbon\Carbon;
use Crypt;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Date;
use Override;

/**
 * @property int $id
 * @property int $integration_id
 * @property string $access_token
 * @property string $refresh_token
 * @property \Illuminate\Support\Carbon $expires_at
 * @property string|null $scope
 * @property string|null $api_domain
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Plugins\Zoho\Models\ZohoIntegration $integration
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ZohoOAuthToken newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ZohoOAuthToken newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ZohoOAuthToken query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ZohoOAuthToken whereAccessToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ZohoOAuthToken whereApiDomain($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ZohoOAuthToken whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ZohoOAuthToken whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ZohoOAuthToken whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ZohoOAuthToken whereIntegrationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ZohoOAuthToken whereRefreshToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ZohoOAuthToken whereScope($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ZohoOAuthToken whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class ZohoOAuthToken extends Model
{
    /**
     * @var mixed|null
     */
    protected $table = 'zoho_oauth_tokens';

    protected $fillable = [
        'integration_id',
        'access_token',
        'refresh_token',
        'expires_at',
        'scope',
        'api_domain',
    ];

    protected $hidden = [
        'access_token',
        'refresh_token',
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

    /**
     * Access Token (encrypt/decrypt).
     */
    protected function accessToken(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => Crypt::decrypt($value),
            set: fn ($value) => Crypt::encrypt($value),
        );
    }

    /**
     * Refresh Token (encrypt/decrypt).
     */
    protected function refreshToken(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => Crypt::decrypt($value),
            set: fn ($value) => Crypt::encrypt($value),
        );
    }

    /**
     * Check if access token is expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public static function findRefreshToken($integrationId): ?ZohoOAuthToken
    {
        return static::query()
            ->whereIntegrationId($integrationId)
            ->first();
    }

    public static function findActiveAccessToken($integrationId): ?ZohoOAuthToken
    {
        return static::query()
            ->whereIntegrationId($integrationId)
            ->first();
    }

    public static function saveAccessToken(int $integrationId, string $accessToken, int $expiresIn): ?ZohoOAuthToken
    {
        return static::query()->updateOrCreate([
            'integration_id' => $integrationId,
        ], [
            'access_token' => $accessToken,
            'expires_at' => now()->addSeconds($expiresIn),
        ]);
    }

    public function isValid(?Carbon $validAt = null): bool
    {
        if ($this->expires_at === null) {
            return true;
        }

        return $this->expires_at->isAfter($validAt ?? Date::now());
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
        ];
    }
}
