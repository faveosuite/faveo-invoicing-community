<?php

namespace App\License\tests\Backend\Services;

use App\License\Models\LicenseSigningKey;
use App\License\Services\Ed25519SigningService;
use App\License\tests\Backend\LicenseTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class Ed25519SigningServiceTest extends LicenseTestCase
{
    private Ed25519SigningService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new Ed25519SigningService;
    }

    #[Test]
    #[Group('ed25519-signing-service')]
    public function sign_produces_a_signature_that_verify_accepts(): void
    {
        $payload = '<license_code>LIC-1234</license_code><license_expire_date>2027-01-01</license_expire_date>';

        $signature = $this->service->sign($payload);

        $this->assertTrue($this->service->verify($payload, $signature));
    }

    #[Test]
    #[Group('ed25519-signing-service')]
    public function verify_rejects_a_tampered_payload(): void
    {
        $payload = '<license_expire_date>2027-01-01</license_expire_date>';
        $signature = $this->service->sign($payload);

        $tampered = '<license_expire_date>2099-01-01</license_expire_date>';

        $this->assertFalse($this->service->verify($tampered, $signature));
    }

    #[Test]
    #[Group('ed25519-signing-service')]
    public function verify_rejects_a_malformed_signature(): void
    {
        $payload = '<license_code>LIC-1234</license_code>';
        $this->service->sign($payload); // ensure a key pair exists

        $this->assertFalse($this->service->verify($payload, 'not-valid-base64-signature!!'));
        $this->assertFalse($this->service->verify($payload, base64_encode('too-short')));
    }

    #[Test]
    #[Group('ed25519-signing-service')]
    public function the_same_key_pair_is_reused_across_calls(): void
    {
        $this->service->sign('first payload');
        $publicKeyAfterFirst = $this->service->publicKey();

        $this->service->sign('second payload');
        $publicKeyAfterSecond = $this->service->publicKey();

        $this->assertSame($publicKeyAfterFirst, $publicKeyAfterSecond);
        $this->assertSame(1, LicenseSigningKey::count());
    }

    #[Test]
    #[Group('ed25519-signing-service')]
    public function secret_key_is_encrypted_at_rest(): void
    {
        $this->service->sign('payload');

        $key = LicenseSigningKey::first();
        $rawColumnValue = $key->getRawOriginal('secret_key');

        $this->assertNotSame($key->secret_key, $rawColumnValue);
        $this->assertStringNotContainsString($key->secret_key, $rawColumnValue);
    }
}
