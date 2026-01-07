<?php

namespace App\Plugins\Zoho\Models;

use Attribute;
use Carbon\Carbon;
use Crypt;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    protected $hidden = [
        'access_token',
        'refresh_token',
    ];

    /**
     * Parent integration
     */
    public function integration(): BelongsTo
    {
        return $this->belongsTo(ZohoIntegration::class, 'integration_id');
    }

    /**
     * Access Token (encrypt/decrypt)
     */
    protected function accessToken(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => Crypt::decrypt($value),
            set: fn ($value) => Crypt::encrypt($value),
        );
    }

    /**
     * Refresh Token (encrypt/decrypt)
     */
    protected function refreshToken(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => Crypt::decrypt($value),
            set: fn ($value) => Crypt::encrypt($value),
        );
    }

    /**
     * Check if access token is expired
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

        return $this->expires_at->isAfter($validAt ?? Carbon::now());
    }
}