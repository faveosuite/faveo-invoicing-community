<?php

namespace App\License\tests\Backend\Controllers\AflCallbacks;

use App\License\Controllers\AflCallbacks\LicenseInstallController;
use App\License\Controllers\AflCallbacks\LicenseSchemeController;
use App\License\Controllers\AflCallbacks\LicenseVerifyController;
use App\License\Helpers\LicenseValidator;
use App\License\Models\Installation;
use App\License\Models\License;
use App\License\Models\LicenseScheme;
use App\License\Services\BannedHostService;
use App\License\Services\InstallationService;
use App\License\tests\Backend\LicenseTestCase;
use App\Model\Product\Product;
use Mockery;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

/**
 * The licensing callbacks for a plugin whose "Pro" and non-Pro products were merged
 * (Faveo v9.4.3.9.B.2): an older Faveo sends the retired product_id; a B.2+ Faveo sends
 * the current product_id plus old_product_id. Request checks (IP, hash, signature) are
 * stubbed; license lookup and validation are real.
 */
class MergedProductLicenseTest extends LicenseTestCase
{
    private const ROOT_URL = 'https://example.test/helpdesk';

    private Product $old;

    private Product $new;

    private License $license;

    protected function setUp(): void
    {
        parent::setUp();

        $this->old = $this->createProduct();
        $this->new = $this->createProduct();
        config(['merged_products.products' => [$this->new->id => $this->old->id]]);

        $this->license = $this->createLicense(['product_id' => $this->old->id]);
        $this->createInstallation([
            'license' => $this->license,
            'product_id' => $this->old->id,
            'installation_domain' => 'example.test/helpdesk',
            'installation_hash' => 'hash',
        ]);
    }

    private function validator(): LicenseValidator
    {
        $validator = Mockery::mock(LicenseValidator::class)->makePartial();
        $validator->shouldReceive('resolveIp')->andReturn('127.0.0.1');
        $validator->shouldReceive('isValidLicenseRequest', 'validateInstallationHash', 'verifyScriptSignature')->andReturn(true);
        $validator->shouldReceive('isBanned')->andReturn(false);

        return $validator;
    }

    /** As Faveo posts it; $oldProductId only from B.2+. */
    private function request(int $productId, ?int $oldProductId = null): array
    {
        return array_filter([
            'product_id' => $productId,
            'old_product_id' => $oldProductId,
            'root_url' => self::ROOT_URL,
            'client_email' => '',
            'license_code' => $this->license->license_code,
            'installation_hash' => 'hash',
            'license_signature' => 'signature',
            'isPlugin' => 'EleaAssistant',
        ], fn ($value): bool => $value !== null);
    }

    private function verify(int $productId, ?int $oldProductId = null): ?string
    {
        $controller = new LicenseVerifyController($this->validator(), new InstallationService, new BannedHostService);

        return $controller->licenseVerify($this->moduleRequest($this->request($productId, $oldProductId), 'POST'))
            ->headers->get('notification_case');
    }

    private function install(int $productId, ?int $oldProductId = null): ?string
    {
        $controller = new LicenseInstallController($this->validator(), new InstallationService, new BannedHostService);

        return $controller->licenseInstall($this->moduleRequest($this->request($productId, $oldProductId), 'POST'))
            ->headers->get('notification_case');
    }

    private function scheme(int $productId, ?int $oldProductId = null): ?string
    {
        LicenseScheme::query()->delete();
        LicenseScheme::unguarded(fn () => LicenseScheme::create(['id' => 2, 'scheme_query' => 'select 1', 'scheme_status' => 1]));
        $controller = new LicenseSchemeController($this->validator(), new BannedHostService);

        return $controller->licenseScheme($this->moduleRequest($this->request($productId, $oldProductId), 'POST'))
            ->headers->get('notification_case');
    }

    #[Test]
    #[Group('license-callbacks')]
    public function old_faveo_verifies_as_before(): void
    {
        $this->assertSame('notification_license_ok', $this->verify($this->old->id));
        $this->assertNull($this->license->fresh()->migrated_from_product_id);
    }

    #[Test]
    #[Group('license-callbacks')]
    public function b2_verify_moves_the_license_and_every_later_call_is_served(): void
    {
        $this->assertSame('notification_license_ok', $this->verify($this->new->id, $this->old->id));

        $license = $this->license->fresh();
        $this->assertSame($this->new->id, $license->product_id);
        $this->assertSame($this->old->id, $license->migrated_from_product_id);
        $this->assertTrue(Installation::where('license_code', $license->license_code)->where('product_id', $this->new->id)->exists());

        $this->assertSame('notification_license_ok', $this->verify($this->new->id, $this->old->id), 'B.2 again');
        $this->assertSame('notification_license_ok', $this->verify($this->old->id), 'a site still on an older Faveo');
        $this->assertSame('notification_license_ok', $this->scheme($this->new->id, $this->old->id), 'B.2 scheme');
        $this->assertSame('notification_license_ok', $this->scheme($this->old->id), 'older Faveo scheme');
    }

    #[Test]
    #[Group('license-callbacks')]
    public function b2_install_moves_the_license(): void
    {
        $this->assertSame('notification_license_ok', $this->install($this->new->id, $this->old->id));

        $license = $this->license->fresh();
        $this->assertSame($this->new->id, $license->product_id);
        $this->assertSame($this->old->id, $license->migrated_from_product_id);
        $this->assertSame(1, Installation::where('license_code', $license->license_code)->where('product_id', $this->new->id)->count());
    }

    #[Test]
    #[Group('license-callbacks')]
    public function a_forged_old_product_id_is_not_found_and_moves_nothing(): void
    {
        $other = $this->createProduct();

        $this->assertSame('notification_license_not_found', $this->verify($other->id, $this->old->id));
        $this->assertSame($this->old->id, $this->license->fresh()->product_id);
    }
}
