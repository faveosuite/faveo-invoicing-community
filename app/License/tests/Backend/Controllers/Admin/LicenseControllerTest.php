<?php

namespace App\License\tests\Backend\Controllers\Admin;

use App\License\Controllers\Admin\LicenseController;
use App\License\Models\Installation;
use App\License\Models\InstallationLog;
use App\License\Models\LicenseCallback;
use App\License\Models\LicenseOption;
use App\License\Models\LicensePlugin;
use App\License\Requests\LicenseRequest;
use App\License\tests\Backend\LicenseTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class LicenseControllerTest extends LicenseTestCase
{
    private LicenseController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->controller = new LicenseController;
    }

    #[Test]
    #[Group('license-admin')]
    public function license_add_creates_a_license_with_a_manual_code(): void
    {
        $product = $this->createProduct();
        $code = 'MANUAL'.strtoupper(substr(uniqid(), -8));
        $request = LicenseRequest::create('/api/admin/license/add', 'POST', [
            'product_id' => $product->id,
            'license_code' => $code,
            'license_order_number' => 123456,
            'license_require_domain' => 0,
            'license_limit' => 3,
            'license_expire_date' => '2027-04-21',
            'license_updates_date' => '2027-05-21',
            'license_support_date' => '2027-06-21',
            'license_comments' => 'Created from test',
            'license_status' => 1,
        ]);

        $response = $this->controller->licenseAdd($request);
        $json = $this->assertSuccessfulJson($response, 201);

        $this->assertSame($code, $json['data']);
        $this->assertDatabaseHas('licenses', [
            'product_id' => $product->id,
            'license_code' => $code,
            'license_order_number' => 123456,
            'license_limit' => 3,
            'license_status' => 1,
        ]);
    }

    #[Test]
    #[Group('license-admin')]
    public function license_add_generates_code_when_client_id_is_given(): void
    {
        $product = $this->createProduct();
        $user = $this->createUser();
        $request = LicenseRequest::create('/api/admin/license/add', 'POST', [
            'product_id' => $product->id,
            'client_id' => $user->id,
            'license_order_number' => 456789,
            'license_require_domain' => 0,
            'license_limit' => 1,
            'license_status' => 1,
        ]);

        $response = $this->controller->licenseAdd($request);
        $json = $this->assertSuccessfulJson($response, 201);

        $this->assertNotEmpty($json['data']);
        $this->assertDatabaseHas('licenses', [
            'product_id' => $product->id,
            'user_id' => $user->id,
            'license_order_number' => 456789,
            'license_status' => 1,
        ]);
    }

    #[Test]
    #[Group('license-admin')]
    public function license_add_rejects_invalid_guard_values(): void
    {
        $request = LicenseRequest::create('/api/admin/license/add', 'POST', [
            'product_id' => 'bad-product',
            'license_code' => 'BADCODE',
            'license_require_domain' => 7,
            'license_status' => 9,
        ]);

        $response = $this->controller->licenseAdd($request);

        $this->assertErrorJson($response, 400);
    }

    #[Test]
    #[Group('license-admin')]
    public function license_add_requires_either_client_or_license_code(): void
    {
        $request = LicenseRequest::create('/api/admin/license/add', 'POST', [
            'product_id' => $this->createProduct()->id,
            'license_require_domain' => 0,
            'license_status' => 1,
        ]);

        $response = $this->controller->licenseAdd($request);

        $this->assertErrorJson($response, 400);
    }

    #[Test]
    #[Group('license-admin')]
    public function license_update_changes_mutable_fields_and_resets_email_dates(): void
    {
        $license = $this->createLicense([
            'license_expire_date' => '2027-01-01',
            'license_expire_email_date' => '2027-01-01',
            'license_updates_date' => '2027-01-02',
            'license_updates_email_date' => '2027-01-02',
            'license_support_date' => '2027-01-03',
            'license_support_email_date' => '2027-01-03',
        ]);

        $request = $this->moduleRequest([
            'id' => $license->id,
            'license_code' => $license->license_code,
            'license_order_number' => 987654,
            'license_ip' => '127.0.0.2',
            'license_domain' => 'updated.example.com',
            'license_require_domain' => 1,
            'license_limit' => 5,
            'license_expire_date' => '2027-02-01',
            'license_updates_date' => '2027-02-02',
            'license_support_date' => '2027-02-03',
            'license_comments' => 'Updated from test',
            'license_status' => 0,
        ], 'POST');

        $response = $this->controller->licenseUpdate($request);

        $this->assertSuccessfulJson($response);
        $license->refresh();
        $this->assertSame(987654, (int) $license->license_order_number);
        $this->assertSame('127.0.0.2', $license->license_ip);
        $this->assertSame('updated.example.com', $license->license_domain);
        $this->assertSame(5, $license->license_limit);
        $this->assertSame(0, $license->license_status);
        $this->assertNotNull($license->license_cancel_date);
        $this->assertNull($license->license_expire_email_date);
        $this->assertNull($license->license_updates_email_date);
        $this->assertNull($license->license_support_email_date);
    }

    #[Test]
    #[Group('license-admin')]
    public function license_update_returns_error_for_unknown_license(): void
    {
        $response = $this->controller->licenseUpdate($this->moduleRequest([
            'id' => 99999999,
        ], 'POST'));

        $this->assertErrorJson($response, 400);
    }

    #[Test]
    #[Group('license-admin')]
    public function delete_license_removes_license_and_related_records(): void
    {
        $license = $this->createLicense();
        $this->createLicenseCallback(['license' => $license]);
        $this->createInstallation(['license' => $license]);
        $this->createInstallationLog(['license' => $license]);

        $response = $this->controller->deleteLicense($this->moduleRequest(['id' => $license->id], 'POST'));
        $json = $this->assertSuccessfulJson($response);

        $this->assertSame(1, $json['data']);
        $this->assertDatabaseMissing('licenses', ['id' => $license->id]);
        $this->assertSame(0, LicenseCallback::where('license_code', $license->license_code)->count());
        $this->assertSame(0, Installation::where('license_code', $license->license_code)->count());
        $this->assertSame(0, InstallationLog::where('license_code', $license->license_code)->count());
    }

    #[Test]
    #[Group('license-admin')]
    public function delete_license_returns_zero_for_missing_record(): void
    {
        $response = $this->controller->deleteLicense($this->moduleRequest(['id' => 99999999], 'POST'));
        $json = $this->assertSuccessfulJson($response);

        $this->assertArrayNotHasKey('data', $json);
    }

    #[Test]
    #[Group('license-admin')]
    public function show_filters_sorts_and_formats_license_rows(): void
    {
        $product = $this->createProduct(['name' => 'Searchable License Product']);
        $user = $this->createUser(['email' => 'license-search-'.uniqid().'@example.test']);
        $license = $this->createLicense([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'license_code' => 'SEARCH'.strtoupper(substr(uniqid(), -8)),
            'license_domain' => 'search-license.test',
        ]);
        $this->createInstallation(['license' => $license]);
        $this->createLicenseCallback(['license' => $license]);

        $response = $this->controller->show($this->moduleRequest([
            'search_query' => 'search-license.test',
            'sort_field' => 'license_code',
            'sort_order' => 'asc',
            'perPage' => 5,
        ]));
        $json = $this->assertSuccessfulJson($response);
        $row = $json['data']['data'][0];

        $this->assertSame($license->id, $row['id']);
        $this->assertSame($product->name, $row['product_title']);
        $this->assertSame($user->email, $row['client_email']);
        $this->assertSame(1, $row['installation_counts']);
        $this->assertSame(1, $row['call_backs_count']);
    }

    #[Test]
    #[Group('license-admin')]
    public function edit_returns_license_product_and_client_payload(): void
    {
        $product = $this->createProduct(['name' => 'Editable Product']);
        $user = $this->createUser([
            'first_name' => 'License',
            'last_name' => 'Client',
            'email' => 'editable-client-'.uniqid().'@example.test',
        ]);
        $license = $this->createLicense(['product_id' => $product->id, 'user_id' => $user->id]);

        $response = $this->controller->edit($license->id);
        $json = $this->assertSuccessfulJson($response);

        $this->assertSame($license->id, $json['data']['license']['id']);
        $this->assertSame($product->name, $json['data']['product_name'][0]['product_title']);
        $this->assertSame('License Client', $json['data']['client_name'][0]['full_name']);
        $this->assertSame($user->email, $json['data']['license']['user']['email']);
    }

    #[Test]
    #[Group('license-admin')]
    public function cloud_reissue_deletes_installations_for_license_code(): void
    {
        $license = $this->createLicense();
        $this->createInstallation(['license' => $license]);

        $this->controller->reissueLicenseCloud($this->moduleRequest([
            'license_code' => $license->license_code,
        ], 'POST'));

        $this->assertSame(0, Installation::where('license_code', $license->license_code)->count());
    }

    #[Test]
    #[Group('license-admin')]
    public function license_deactivate_sets_status_to_inactive(): void
    {
        $license = $this->createLicense(['license_status' => 1]);

        $this->controller->licenseDeactivate($this->moduleRequest([
            'license_code' => $license->license_code,
        ], 'POST'));

        $this->assertSame(0, $license->refresh()->license_status);
    }

    #[Test]
    #[Group('license-admin')]
    public function update_the_license_code_changes_matching_code(): void
    {
        $license = $this->createLicense();
        $newCode = 'NEW'.strtoupper(substr(uniqid(), -8));

        $updated = $this->controller->updateTheLicenseCode($this->moduleRequest([
            'old_license_code' => $license->license_code,
            'license_code' => $newCode,
        ], 'POST'));

        $this->assertSame(1, $updated);
        $this->assertSame($newCode, $license->refresh()->license_code);
    }

    #[Test]
    #[Group('license-admin')]
    public function sync_creation_adds_plugins_and_options(): void
    {
        $license = $this->createLicense();
        $pluginOne = $this->createProduct();
        $pluginTwo = $this->createProduct();

        $response = $this->controller->syncTheCreationOfLicense($this->moduleRequest([
            'license_code' => $license->license_code,
            'ids' => $pluginOne->id.','.$pluginTwo->id,
            'options' => json_encode([
                ['key' => 'edition', 'value' => 'enterprise'],
                ['key' => '', 'value' => 'ignored'],
            ]),
        ], 'POST'));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertDatabaseHas('license_plugins', ['license_id' => $license->id, 'product_id' => $pluginOne->id]);
        $this->assertDatabaseHas('license_plugins', ['license_id' => $license->id, 'product_id' => $pluginTwo->id]);
        $this->assertDatabaseHas('license_options', [
            'option_key' => 'edition',
            'option_group' => (string) $license->id,
            'option_value' => 'enterprise',
        ]);
    }

    #[Test]
    #[Group('license-admin')]
    public function sync_creation_returns_not_found_for_unknown_license(): void
    {
        $response = $this->controller->syncTheCreationOfLicense($this->moduleRequest([
            'license_code' => 'missing-license',
            'ids' => '1',
        ], 'POST'));

        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame('License not found', $this->jsonContent($response)['error']);
    }

    #[Test]
    #[Group('license-admin')]
    public function individual_license_info_returns_options_for_license(): void
    {
        $license = $this->createLicense();
        $option = LicenseOption::create([
            'option_key' => 'seat_count',
            'option_value' => '25',
            'option_group' => (string) $license->id,
        ]);

        $response = $this->controller->individualLicenseInfo($this->moduleRequest([
            'license_code' => $license->license_code,
        ]));
        $json = $this->assertSuccessfulJson($response);

        $this->assertSame($option->id, $json['data'][0]['id']);
        $this->assertSame('seat_count', $json['data'][0]['key']);
        $this->assertSame('25', $json['data'][0]['value']);
    }

    #[Test]
    #[Group('license-admin')]
    public function give_license_take_order_returns_order_number(): void
    {
        $license = $this->createLicense(['license_order_number' => 778899]);

        $response = $this->controller->giveLicenseTakeOrder($this->moduleRequest([
            'license_code' => $license->license_code,
        ]));
        $json = $this->assertSuccessfulJson($response);

        $this->assertSame(778899, (int) $json['data']);
    }

    #[Test]
    #[Group('license-admin')]
    public function get_plugin_info_returns_uninstalled_plugin_version_data(): void
    {
        $license = $this->createLicense(['license_expire_date' => null]);
        $plugin = $this->createProduct(['name' => 'Plugin Product']);
        $version = $this->createVersion($plugin, ['version' => '9.9.9', 'file' => 'plugin.zip']);
        LicensePlugin::create(['license_id' => $license->id, 'product_id' => $plugin->id]);

        $response = $this->controller->getPluginInfo($this->moduleRequest([
            'license_code' => json_encode([$license->license_code]),
        ]));
        $json = $this->assertSuccessfulJson($response);
        $pluginData = $json['data'][0][0];

        $this->assertSame($plugin->id, $pluginData['id']);
        $this->assertSame($plugin->name, $pluginData['product_name']);
        $this->assertSame($version->version, $pluginData['version']);
        $this->assertSame($license->license_code, $pluginData['license_code']);
    }
}
