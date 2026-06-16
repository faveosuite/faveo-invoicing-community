<?php

namespace App\License\tests\Backend\Controllers\AflCallbacks;

use App\License\Controllers\AflCallbacks\LicenseSchemeController;
use App\License\Helpers\LicenseValidator;
use App\License\Models\LicenseScheme;
use App\License\tests\Backend\LicenseTestCase;
use Mockery;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class LicenseSchemeControllerTest extends LicenseTestCase
{
    #[Test]
    #[Group('license-callbacks')]
    public function license_scheme_returns_scheme_when_installation_exists(): void
    {
        $product = $this->createProduct();
        $license = $this->createLicense(['product_id' => $product->id]);
        $installationHash = 'scheme-hash';
        $this->createInstallation([
            'license' => $license,
            'installation_domain' => 'example.com/helpdesk',
            'installation_hash' => $installationHash,
        ]);
        LicenseScheme::query()->delete();
        LicenseScheme::unguarded(fn () => LicenseScheme::create([
            'id' => 1,
            'scheme_query' => 'select 1',
            'scheme_status' => 1,
        ]));

        $validator = Mockery::mock(LicenseValidator::class);
        $validator->shouldReceive('resolveIp')->once()->andReturn('127.0.0.1');
        $validator->shouldReceive('isValidLicenseRequest')->once()->andReturn(true);
        $validator->shouldReceive('validateInstallationHash')->once()->andReturn(true);
        $validator->shouldReceive('verifyScriptSignature')->once()->andReturn(true);
        $validator->shouldReceive('validateProduct')->once()->andReturn($product);
        $validator->shouldReceive('findLicense')->once()->andReturn($license);
        $validator->shouldReceive('validateLicense')->once()->andReturn(['valid' => true, 'license' => $license]);

        $response = new LicenseSchemeController($validator)->licenseScheme($this->moduleRequest([
            'product_id' => $product->id,
            'root_url' => 'https://example.com/helpdesk',
            'client_email' => 'client@example.com',
            'license_code' => $license->license_code,
            'installation_hash' => $installationHash,
            'license_signature' => 'signature',
            'client_id' => $license->user_id,
        ], 'POST'));

        $this->assertSame('notification_license_ok', $response->headers->get('notification_case'));
        $data = json_decode((string) $response->headers->get('notification_data'), true);
        $this->assertSame('select 1', $data['scheme_query']);
    }
}
