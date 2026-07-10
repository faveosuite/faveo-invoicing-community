<?php

namespace App\License\Models;

use Illuminate\Database\Eloquent\Model;
use Override;
use RuntimeException;

/**
 * @property int $id
 * @property string $public_key
 * @property string $secret_key
 */
class LicenseSigningKey extends Model
{
    protected $table = 'license_signing_keys';

    protected $fillable = [
        'public_key',
        'secret_key',
    ];

    #[Override]
    protected function casts(): array
    {
        return [
            'secret_key' => 'encrypted',
        ];
    }

    /**
     * @return non-empty-string
     */
    public function publicKeyRaw(): string
    {
        return $this->decodeKeyComponent($this->public_key);
    }

    /**
     * @return non-empty-string
     */
    public function secretKeyRaw(): string
    {
        return $this->decodeKeyComponent($this->secret_key);
    }

    /**
     * @return non-empty-string
     */
    private function decodeKeyComponent(string $encoded): string
    {
        $decoded = base64_decode($encoded, strict: true);

        if ($decoded === false || $decoded === '') {
            throw new RuntimeException('Corrupt license signing key.');
        }

        return $decoded;
    }
}
