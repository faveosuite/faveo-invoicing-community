<?php

namespace App\License\tests\Backend\Helpers;

use App\License\Helpers\LicenseValidator;
use App\License\tests\Backend\LicenseTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class LicenseValidatorTest extends LicenseTestCase
{
    #[Test]
    #[Group('license-callbacks')]
    public function validate_license_rejects_ip_based_root_url_when_require_domain_is_enabled(): void
    {
        $license = $this->createLicense([
            'license_ip' => null,
            'license_domain' => null,
            'license_require_domain' => 1,
        ]);

        $result = new LicenseValidator()->validateLicense($license, $license->product_id, 'client@example.com', '127.0.0.1', 'http://127.0.0.1/app');

        $this->assertFalse($result['valid']);
        $this->assertSame('domain_required', $result['error']);
    }

    #[Test]
    #[Group('license-callbacks')]
    public function validate_license_allows_ip_based_root_url_when_require_domain_is_disabled(): void
    {
        $license = $this->createLicense([
            'license_ip' => null,
            'license_domain' => null,
            'license_require_domain' => 0,
        ]);

        $result = new LicenseValidator()->validateLicense($license, $license->product_id, 'client@example.com', '127.0.0.1', 'http://127.0.0.1/app');

        $this->assertTrue($result['valid']);
    }

    /** No IP/domain restrictions unless a test sets them. */
    private function openLicense(array $attributes = []): \App\License\Models\License
    {
        return $this->createLicense($attributes + ['license_ip' => null, 'license_domain' => null, 'license_require_domain' => 0]);
    }

    private function check(?\App\License\Models\License $license, string $ip = '127.0.0.1', string $rootUrl = 'https://example.test/app', ?string $email = null, ?int $productId = null): array
    {
        return new LicenseValidator()->validateLicense($license, $productId ?? $license->product_id, $email, $ip, $rootUrl);
    }

    #[Test]
    #[Group('license-callbacks')]
    public function validate_license_without_license_or_email_is_not_found(): void
    {
        $this->assertSame(['valid' => false, 'error' => 'license_not_found'], $this->check(null, productId: 1));
    }

    #[Test]
    #[Group('license-callbacks')]
    public function validate_license_falls_back_to_the_clients_active_license_by_email(): void
    {
        $license = $this->openLicense();
        $user = \App\User::find($license->user_id);
        $user->update(['active' => 1]);

        $result = $this->check(null, email: $user->email, productId: $license->product_id);

        $this->assertTrue($result['valid']);
        $this->assertSame($license->id, $result['license']->id);
    }

    #[Test]
    #[Group('license-callbacks')]
    public function validate_license_rejects_suspended_and_cancelled_licenses(): void
    {
        $this->assertSame(['valid' => false, 'error' => 'license_suspended'], $this->check($this->openLicense(['license_status' => 2])));

        $cancelled = $this->openLicense(['license_status' => 0, 'license_cancel_date' => '2024-05-01']);
        $this->assertSame(['valid' => false, 'error' => 'license_cancelled', 'data' => ['cancel_date' => $cancelled->license_cancel_date]], $this->check($cancelled));
    }

    #[Test]
    #[Group('license-callbacks')]
    public function validate_license_rejects_expired_but_ignores_malformed_expiry(): void
    {
        $expired = $this->openLicense(['license_expire_date' => '2001-01-01']);
        $this->assertSame(['valid' => false, 'error' => 'license_expired', 'data' => ['expire_date' => '2001-01-01']], $this->check($expired));

        $this->assertTrue($this->check($this->openLicense(['license_expire_date' => 'not-a-date']))['valid']);
    }

    #[Test]
    #[Group('license-callbacks')]
    public function is_past_date_reads_stored_datetimes_and_treats_empty_as_never(): void
    {
        $validator = new LicenseValidator;

        // Stored values carry a time: the bug was that this format skipped the expiry check.
        $this->assertTrue($validator->isPastDate(now()->subDay()->format('Y-m-d').' 00:00:00'));
        $this->assertFalse($validator->isPastDate(now()->format('Y-m-d').' 00:00:00')); // valid on its last day
        $this->assertFalse($validator->isPastDate(now()->addDay()->format('Y-m-d').' 00:00:00'));
        $this->assertTrue($validator->isPastDate('2001-01-01'));

        foreach ([null, '', '0000-00-00 00:00:00', 'not-a-date'] as $never) {
            $this->assertFalse($validator->isPastDate($never), var_export($never, true));
        }
    }

    #[Test]
    #[Group('license-callbacks')]
    public function validate_license_rejects_a_saved_license_whose_expiry_day_has_passed(): void
    {
        // Re-read, so the expiry comes back from the DB as "Y-m-d 00:00:00", as in production.
        $license = $this->openLicense(['license_expire_date' => now()->subDay()->toDateString()])->fresh();

        $this->assertSame('license_expired', $this->check($license)['error'] ?? null);
    }

    #[Test]
    #[Group('license-callbacks')]
    public function validate_license_checks_comma_separated_ips(): void
    {
        $license = $this->openLicense(['license_ip' => '10.0.0.1, 127.0.0.1']);

        $this->assertTrue($this->check($license, ip: '127.0.0.1')['valid']);
        $this->assertSame(['valid' => false, 'error' => 'invalid_ip', 'data' => ['ip' => '10.0.0.2']], $this->check($license, ip: '10.0.0.2'));
    }

    #[Test]
    #[Group('license-callbacks')]
    public function validate_license_checks_comma_separated_domains_case_insensitively(): void
    {
        $license = $this->openLicense(['license_domain' => 'other.test, Example.test']);

        $this->assertTrue($this->check($license, rootUrl: 'https://EXAMPLE.test/app')['valid']);
        $this->assertSame(['valid' => false, 'error' => 'invalid_domain', 'data' => ['domain' => 'https://nope.test']], $this->check($license, rootUrl: 'https://nope.test'));
    }

    #[Test]
    #[Group('license-callbacks')]
    public function validate_license_require_domain_needs_a_valid_url_with_resolving_host(): void
    {
        $license = $this->openLicense(['license_require_domain' => 1]);

        // localhost resolves without network access.
        $this->assertTrue($this->check($license, rootUrl: 'http://localhost/app')['valid']);
        $this->assertSame(['valid' => false, 'error' => 'domain_required', 'data' => ['domain' => 'not a url']], $this->check($license, rootUrl: 'not a url'));
    }
}
