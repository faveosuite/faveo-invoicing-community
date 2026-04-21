<?php

namespace App\License\tests\Services;

use App\License\Models\Installation;
use App\License\Models\LicenseBannedHost;
use App\License\Models\LicenseCallback;
use App\License\Models\LicenseWhitelistIp;
use App\License\Services\CallbackService;
use App\License\Services\InstallationService;
use App\License\Services\LicenseService;
use App\License\Services\VersionService;
use App\License\tests\LicenseTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class CallbackServiceTest extends LicenseTestCase
{
    private CallbackService $service;

    protected function setUp(): void
    {
        parent::setUp();

        LicenseBannedHost::where('banned_host_ip', '127.0.0.1')->delete();
        LicenseWhitelistIp::where('whitelist_host_ip', '127.0.0.1')->delete();

        $this->service = new CallbackService(
            new LicenseService(),
            new InstallationService(),
            new VersionService()
        );
    }

    #[Test]
    #[Group('license-service')]
    public function license_verification_returns_ok_payload_and_logs_callback(): void
    {
        $license = $this->createLicense([
            'license_ip' => '127.0.0.1',
            'license_domain' => 'example.test',
            'license_require_domain' => 1,
            'license_status' => 1,
            'license_expire_date' => now()->addDay()->format('Y-m-d'),
        ]);

        $response = $this->service->processLicenseVerification($this->moduleRequest([
            'license_code' => $license->license_code,
            'root_url' => 'https://example.test/path',
        ], 'POST'));

        $this->assertSame('notification_license_ok', $response['notification_case']);
        $this->assertSame('ok', $response['status']);
        $this->assertSame($license->license_code, $response['license_code']);
        $this->assertSame($license->product_id, $response['product_id']);
        $this->assertDatabaseHas('license_callbacks', [
            'license_code' => $license->license_code,
            'callback_ip' => '127.0.0.1',
            'callback_domain' => 'https://example.test/path',
        ]);
    }

    #[Test]
    #[Group('license-service')]
    public function license_verification_returns_not_found_cancelled_suspended_and_expired_errors(): void
    {
        $missing = $this->service->processLicenseVerification($this->moduleRequest([
            'license_code' => 'missing-license',
        ], 'POST'));

        $cancelled = $this->createLicense(['license_ip' => '127.0.0.1', 'license_status' => 0]);
        $suspended = $this->createLicense(['license_ip' => '127.0.0.1', 'license_status' => 2]);
        $expired = $this->createLicense([
            'license_ip' => '127.0.0.1',
            'license_status' => 1,
            'license_expire_date' => now()->subDay()->format('Y-m-d'),
        ]);

        $this->assertSame('notification_license_not_found', $missing['notification_case']);
        $this->assertSame('notification_license_cancelled', $this->service->processLicenseVerification($this->moduleRequest([
            'license_code' => $cancelled->license_code,
        ], 'POST'))['notification_case']);
        $this->assertSame('notification_license_suspended', $this->service->processLicenseVerification($this->moduleRequest([
            'license_code' => $suspended->license_code,
        ], 'POST'))['notification_case']);
        $this->assertSame('notification_license_expired', $this->service->processLicenseVerification($this->moduleRequest([
            'license_code' => $expired->license_code,
        ], 'POST'))['notification_case']);
        $this->assertSame(1, LicenseCallback::where('license_code', 'missing-license')->count());
    }

    #[Test]
    #[Group('license-service')]
    public function license_verification_rejects_invalid_domain_and_ip(): void
    {
        $domainLicense = $this->createLicense([
            'license_ip' => null,
            'license_domain' => 'allowed.test',
            'license_require_domain' => 1,
        ]);
        $ipLicense = $this->createLicense([
            'license_ip' => '10.0.0.9',
            'license_require_domain' => 0,
        ]);

        $invalidDomain = $this->service->processLicenseVerification($this->moduleRequest([
            'license_code' => $domainLicense->license_code,
            'root_url' => 'https://blocked.test',
        ], 'POST'));
        $invalidIp = $this->service->processLicenseVerification($this->moduleRequest([
            'license_code' => $ipLicense->license_code,
            'root_url' => 'https://example.test',
        ], 'POST'));

        $this->assertSame('notification_invalid_domain', $invalidDomain['notification_case']);
        $this->assertSame('notification_invalid_ip', $invalidIp['notification_case']);
    }

    #[Test]
    #[Group('license-service')]
    public function banned_hosts_short_circuit_verification_and_installation_callbacks(): void
    {
        $license = $this->createLicense(['license_ip' => '127.0.0.1']);
        LicenseBannedHost::create([
            'banned_host_ip' => '127.0.0.1',
            'comments' => 'Blocked by test',
        ]);

        $verification = $this->service->processLicenseVerification($this->moduleRequest([
            'license_code' => $license->license_code,
        ], 'POST'));
        $installation = $this->service->processLicenseInstallation($this->moduleRequest([
            'license_code' => $license->license_code,
        ], 'POST'));

        $this->assertTrue($this->service->isHostBanned('127.0.0.1'));
        $this->assertSame('notification_host_banned', $verification['notification_case']);
        $this->assertSame('notification_host_banned', $installation['notification_case']);
        $this->assertSame(0, LicenseCallback::where('license_code', $license->license_code)->count());
        $this->assertSame(0, Installation::where('license_code', $license->license_code)->count());
    }

    #[Test]
    #[Group('license-service')]
    public function license_installation_registers_installation_and_handles_missing_or_limited_licenses(): void
    {
        $missing = $this->service->processLicenseInstallation($this->moduleRequest([
            'license_code' => 'missing-install-license',
        ], 'POST'));

        $limited = $this->createLicense(['license_limit' => 1]);
        $this->createInstallation(['license' => $limited, 'installation_status' => 1]);

        $limitResponse = $this->service->processLicenseInstallation($this->moduleRequest([
            'license_code' => $limited->license_code,
            'root_url' => 'https://limit.test',
        ], 'POST'));

        $license = $this->createLicense(['license_limit' => 2]);
        $ok = $this->service->processLicenseInstallation($this->moduleRequest([
            'license_code' => $license->license_code,
            'root_url' => 'https://install-ok.test',
        ], 'POST'));

        $this->assertSame('notification_license_not_found', $missing['notification_case']);
        $this->assertSame('notification_license_limit', $limitResponse['notification_case']);
        $this->assertSame('notification_license_ok', $ok['notification_case']);
        $this->assertDatabaseHas('installations', [
            'license_code' => $license->license_code,
            'installation_domain' => 'https://install-ok.test',
            'installation_ip' => '127.0.0.1',
            'installation_status' => 1,
        ]);
    }

    #[Test]
    #[Group('license-service')]
    public function whitelist_lookup_returns_true_only_for_registered_ips(): void
    {
        LicenseWhitelistIp::create([
            'whitelist_host_ip' => '127.0.0.1',
            'whitelist_host_comments' => 'Allowed by test',
        ]);

        $this->assertTrue($this->service->isIpWhitelisted('127.0.0.1'));
        $this->assertFalse($this->service->isIpWhitelisted('10.10.10.10'));
    }
}
