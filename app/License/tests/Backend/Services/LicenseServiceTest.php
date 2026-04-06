<?php

namespace App\License\tests\Backend\Services;

use App\License\Models\Installation;
use App\License\Models\LicenseOption;
use App\License\Models\LicensePlugin;
use App\License\Services\LicenseService;
use App\License\tests\Backend\LicenseTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class LicenseServiceTest extends LicenseTestCase
{
    private LicenseService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new LicenseService();
    }

    #[Test]
    #[Group('license-service')]
    public function create_persists_a_license_with_generated_defaults_and_client_alias(): void
    {
        $product = $this->createProduct();
        $user = $this->createUser();

        $license = $this->service->create([
            'product_id' => $product->id,
            'client_id' => $user->id,
            'license_order_number' => 'ORDER-100',
        ]);

        $this->assertSame($product->id, $license->product_id);
        $this->assertSame($user->id, $license->user_id);
        $this->assertSame('ORDER-100', $license->license_order_number);
        $this->assertSame(1, $license->license_limit);
        $this->assertSame(1, $license->license_status);
        $this->assertMatchesRegularExpression('/^[A-F0-9]{4}(-[A-F0-9]{4}){3}$/', $license->license_code);
        $this->assertNull($license->license_expire_email_date);
        $this->assertDatabaseHas('licenses', ['id' => $license->id]);
    }

    #[Test]
    #[Group('license-service')]
    public function update_resets_notification_dates_and_sets_cancel_date(): void
    {
        $license = $this->createLicense([
            'license_expire_date' => '2027-01-01',
            'license_expire_email_date' => '2027-01-01',
            'license_updates_date' => '2027-01-02',
            'license_updates_email_date' => '2027-01-02',
            'license_support_date' => '2027-01-03',
            'license_support_email_date' => '2027-01-03',
            'license_status' => 1,
        ]);

        $updated = $this->service->update($license->id, [
            'license_expire_date' => '2027-02-01',
            'license_updates_date' => '2027-02-02',
            'license_support_date' => '2027-02-03',
            'license_status' => 0,
            'license_comments' => 'Updated by service',
        ]);

        $license->refresh();

        $this->assertTrue($updated);
        $this->assertSame('Updated by service', $license->license_comments);
        $this->assertSame(0, $license->license_status);
        $this->assertNotNull($license->license_cancel_date);
        $this->assertNull($license->license_expire_email_date);
        $this->assertNull($license->license_updates_email_date);
        $this->assertNull($license->license_support_email_date);
    }

    #[Test]
    #[Group('license-service')]
    public function status_changes_and_license_code_update_work(): void
    {
        $license = $this->createLicense(['license_status' => 1]);
        $newCode = 'SVC'.strtoupper(substr(uniqid(), -10));

        $this->assertTrue($this->service->deactivate($license->license_code));
        $this->assertSame(0, $license->refresh()->license_status);
        $this->assertSame(1, $this->service->updateLicenseCode($license->license_code, $newCode));
        $this->assertSame($newCode, $license->refresh()->license_code);
    }

    #[Test]
    #[Group('license-service')]
    public function sync_addons_upserts_plugins_and_current_schema_options(): void
    {
        $license = $this->createLicense();
        $plugin = $this->createProduct();

        $this->service->syncAddons($license->license_code, [$plugin->id, $plugin->id, '', null], [
            ['key' => 'edition', 'value' => 'enterprise'],
            ['key' => '', 'value' => 'ignored'],
        ]);
        $this->service->syncAddons($license->license_code, [$plugin->id], [
            ['key' => 'edition', 'value' => 'ultimate'],
        ]);

        $this->assertSame(1, LicensePlugin::where('license_id', $license->id)->where('product_id', $plugin->id)->count());
        $this->assertDatabaseHas('license_options', [
            'option_key' => 'edition',
            'option_group' => (string) $license->id,
            'option_value' => 'ultimate',
        ]);
        $this->assertDatabaseMissing('license_options', ['option_key' => '']);
    }

    #[Test]
    #[Group('license-service')]
    public function license_info_and_individual_info_return_addons_and_options(): void
    {
        $license = $this->createLicense();
        $plugin = $this->createProduct(['name' => 'Service Plugin']);
        $version = $this->createVersion($plugin, ['version' => '3.2.1', 'file' => 'plugin-3.2.1.zip']);

        LicensePlugin::create(['license_id' => $license->id, 'product_id' => $plugin->id]);
        $option = LicenseOption::create([
            'option_key' => 'seat_count',
            'option_value' => '25',
            'option_group' => (string) $license->id,
        ]);

        $info = $this->service->getLicenseInfo($license->license_code);
        $individualInfo = $this->service->getIndividualLicenseInfo($license->license_code);

        $this->assertSame($license->license_code, $info['license']['license_code']);
        $this->assertSame($plugin->id, $info['addons'][0]['product_id']);
        $this->assertSame($version->version, $info['addons'][0]['latest_version']);
        $this->assertSame('25', $info['addons'][0]['product_attributes_license']['seat_count']);
        $this->assertSame($option->id, $individualInfo[0]['id']);
        $this->assertSame('seat_count', $individualInfo[0]['key']);
        $this->assertSame('25', $individualInfo[0]['value']);
        $this->assertNull($this->service->getLicenseInfo('missing-license'));
        $this->assertSame([], $this->service->getIndividualLicenseInfo('missing-license'));
    }

    #[Test]
    #[Group('license-service')]
    public function plugin_licenses_return_latest_active_version_metadata(): void
    {
        $license = $this->createLicense();
        $plugin = $this->createProduct(['name' => 'Plugin License Product', 'product_sku' => 'PLUGIN-SVC']);
        $this->createVersion($plugin, ['version' => '1.0.0', 'file' => 'old.zip', 'status' => 1]);
        $latest = $this->createVersion($plugin, ['version' => '2.0.0', 'file' => 'latest.zip', 'status' => 1]);

        LicensePlugin::create(['license_id' => $license->id, 'product_id' => $plugin->id]);

        $result = $this->service->getPluginLicenses([$license->license_code]);

        $this->assertSame($plugin->id, $result[0]['product_id']);
        $this->assertSame('Plugin License Product', $result[0]['product_name']);
        $this->assertSame('PLUGIN-SVC', $result[0]['product_sku']);
        $this->assertSame($latest->version, $result[0]['latest_version']);
        $this->assertSame($latest->file, $result[0]['latest_version_file']);
    }

    #[Test]
    #[Group('license-service')]
    public function finders_order_lookup_and_reissue_return_expected_data(): void
    {
        $product = $this->createProduct();
        $user = $this->createUser();
        $license = $this->createLicense([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'license_order_number' => 'ORDER-SVC',
        ]);

        $this->assertSame($license->id, $this->service->findByCode($license->license_code)->id);
        $this->assertSame('ORDER-SVC', $this->service->getOrderNumber($license->license_code));
        $this->assertNull($this->service->getOrderNumber('missing-license'));

        $this->createInstallation(['license' => $license]);
        $this->assertSame(1, $this->service->reissueLicenseCloud($license->license_code));
        $this->assertSame(0, Installation::where('license_code', $license->license_code)->count());
    }

    #[Test]
    #[Group('license-service')]
    public function parse_ip_domain_and_generated_codes_are_valid(): void
    {
        $this->assertSame(['ip' => '127.0.0.1', 'domain' => '', 'requireDomain' => 0], LicenseService::parseIpAndDomain('127.0.0.1'));
        $this->assertSame(['ip' => '', 'domain' => 'example.test', 'requireDomain' => 1], LicenseService::parseIpAndDomain('example.test'));
        $this->assertSame(['ip' => '', 'domain' => '', 'requireDomain' => 0], LicenseService::parseIpAndDomain(''));
        $this->assertMatchesRegularExpression('/^[A-F0-9]{4}(-[A-F0-9]{4}){3}$/', $this->service->generateLicenseCode());
    }
}
