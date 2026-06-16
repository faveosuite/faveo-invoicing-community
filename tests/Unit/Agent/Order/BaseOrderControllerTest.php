<?php

namespace Tests\Unit\Agent\Order;

use Illuminate\Support\Facades\Date;
use App\Http\Controllers\License\LicenseController;
use App\Model\Common\StatusSetting;
use App\Model\License\LicensePermission;
use App\Model\License\LicenseType;
use App\Model\Order\Order;
use App\Model\Product\Product;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery\MockInterface;
use Tests\DBTestCase;

class BaseOrderControllerTest extends DBTestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->getLoggedInUser('admin');

        StatusSetting::updateOrCreate(['id' => 1], [
            'license_status' => 1,
        ]);
    }

    /**
     * Shared helpers.
     */
    private function mockPermissions(int $productId, array $permissionNames)
    {
        $licenseType = LicenseType::updateOrCreate(
            ['id' => 1],
            ['name' => 'Download Perpetual']
        );

        $map = [
            'Generate Updates Expiry Date' => 'generateUpdatesxpiryDate',
            'Generate License Expiry Date' => 'generateLicenseExpiryDate',
            'Generate Support Expiry Date' => 'generateSupportExpiryDate',
            'Can be Downloaded' => 'downloadPermission',
            'No Permissions' => 'noPermissions',
            'Allow Downloads Before Updates Expire' => 'allowDownloadTillExpiry',
        ];

        $permissionDisplayNames = collect($map)
            ->filter(fn ($key) => isset($permissionNames[$key]) && $permissionNames[$key] == 1)
            ->keys()
            ->all();

        $permissionIds = LicensePermission::whereIn('permissions', $permissionDisplayNames)
            ->pluck('id');

        $licenseType->permissions()->sync($permissionIds);

        Product::where('id', $productId)->update([
            'type' => $licenseType->id,
        ]);
    }

    private function mockLicenseController()
    {
        $this->mock(LicenseController::class, function (MockInterface $mock): void {
            $mock->shouldReceive('updateExpirationDate')->andReturn(true);
            $mock->shouldReceive('getNoOfAllowedInstallation')->andReturn(5);
            $mock->shouldReceive('getInstallPreference')->andReturn('domain.com');
            $mock->shouldReceive('updateLicensedDomain')->andReturn(true);
        });
    }

    private function date()
    {
        return Date::now()->addDays(30)->toDateString();
    }

    private function assertExpiryUpdated($field, $orderId, $date)
    {
        $this->assertDatabaseHas('subscriptions', [
            'order_id' => $orderId,
            $field => Date::parse($date)->endOfDay()->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * =========================================================
     * UPDATE EXPIRY TESTS
     * =========================================================.
     */
    public function test_edit_update_expiry_success()
    {
        $order = Order::factory()->withRelations()->create();

        $this->mockPermissions($order->product, ['generateUpdatesxpiryDate' => 1]);
        $this->mockLicenseController();

        $date = $this->date();

        $response = $this->postJson('/edit-update-expiry', [
            'orderid' => $order->id,
            'date' => $date,
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => 'Updates Expiry Date Updated Successfully']);

        $this->assertExpiryUpdated('update_ends_at', $order->id, $date);
    }

    public function test_edit_update_expiry_permission_denied()
    {
        $order = Order::factory()->withRelations()->create();

        $this->mockPermissions($order->product, ['generateUpdatesxpiryDate' => 0]);
        $this->mockLicenseController();

        $response = $this->postJson('/edit-update-expiry', [
            'orderid' => $order->id,
            'date' => $this->date(),
        ]);

        $response->assertStatus(400)
            ->assertJsonFragment(['message' => __('message.license_permission_denied')]);
    }

    public function test_edit_update_expiry_validation_error()
    {
        $order = Order::factory()->withRelations()->create();

        $response = $this->postJson('/edit-update-expiry', [
            'orderid' => $order->id,
            'date' => '',
        ]);

        $response->assertStatus(422);
    }

    public function test_edit_update_expiry_file_license_handling()
    {
        $order = Order::factory()
            ->withRelations(['license_mode' => 'File', 'is_downloadable' => 1])
            ->create();

        $this->mockPermissions($order->product, ['generateUpdatesxpiryDate' => 1]);
        $this->mockLicenseController();

        $this->postJson('/edit-update-expiry', [
            'orderid' => $order->id,
            'date' => $this->date(),
        ]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'is_downloadable' => 0,
        ]);
    }

    /**
     * =========================================================
     * LICENSE EXPIRY TESTS
     * =========================================================.
     */
    public function test_edit_license_expiry_success()
    {
        $order = Order::factory()->withRelations()->create();

        $this->mockPermissions($order->product, ['generateLicenseExpiryDate' => 1]);
        $this->mockLicenseController();

        $date = $this->date();

        $response = $this->postJson('/edit-license-expiry', [
            'orderid' => $order->id,
            'date' => $date,
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => 'License Expiry Date Updated Successfully']);

        $this->assertExpiryUpdated('ends_at', $order->id, $date);
    }

    public function test_edit_license_expiry_permission_denied()
    {
        $order = Order::factory()->withRelations()->create();

        $this->mockPermissions($order->product, ['generateLicenseExpiryDate' => 0]);
        $this->mockLicenseController();

        $response = $this->postJson('/edit-license-expiry', [
            'orderid' => $order->id,
            'date' => $this->date(),
        ]);

        $response->assertStatus(400)
            ->assertJsonFragment(['message' => __('message.license_permission_denied')]);
    }

    public function test_edit_license_expiry_validation_error()
    {
        $order = Order::factory()->withRelations()->create();

        $response = $this->postJson('/edit-license-expiry', [
            'orderid' => $order->id,
            'date' => '',
        ]);

        $response->assertStatus(422);
    }

    public function test_edit_license_expiry_file_license_handling()
    {
        $order = Order::factory()
            ->withRelations(['license_mode' => 'File', 'is_downloadable' => 1])
            ->create();

        $this->mockPermissions($order->product, ['generateLicenseExpiryDate' => 1]);
        $this->mockLicenseController();

        $this->postJson('/edit-license-expiry', [
            'orderid' => $order->id,
            'date' => $this->date(),
        ]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'is_downloadable' => 0,
        ]);
    }

    /**
     * =========================================================
     * SUPPORT EXPIRY TESTS
     * =========================================================.
     */
    public function test_edit_support_expiry_success()
    {
        $order = Order::factory()->withRelations()->create();

        $this->mockPermissions($order->product, ['generateSupportExpiryDate' => 1]);
        $this->mockLicenseController();

        $date = $this->date();

        $response = $this->postJson('/edit-support-expiry', [
            'orderid' => $order->id,
            'date' => $date,
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => 'Support Expiry Date Updated Successfully']);

        $this->assertExpiryUpdated('support_ends_at', $order->id, $date);
    }

    public function test_edit_support_expiry_permission_denied()
    {
        $order = Order::factory()->withRelations()->create();

        $this->mockPermissions($order->product, ['generateSupportExpiryDate' => 0]);
        $this->mockLicenseController();

        $response = $this->postJson('/edit-support-expiry', [
            'orderid' => $order->id,
            'date' => $this->date(),
        ]);

        $response->assertStatus(400)
            ->assertJsonFragment(['message' => __('message.license_permission_denied')]);
    }

    public function test_edit_support_expiry_validation_error()
    {
        $order = Order::factory()->withRelations()->create();

        $response = $this->postJson('/edit-support-expiry', [
            'orderid' => $order->id,
            'date' => '',
        ]);

        $response->assertStatus(422);
    }

    public function test_edit_support_expiry_file_license_handling()
    {
        $order = Order::factory()
            ->withRelations(['license_mode' => 'File', 'is_downloadable' => 1])
            ->create();

        $this->mockPermissions($order->product, ['generateSupportExpiryDate' => 1]);
        $this->mockLicenseController();

        $this->postJson('/edit-support-expiry', [
            'orderid' => $order->id,
            'date' => $this->date(),
        ]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'is_downloadable' => 0,
        ]);
    }

    /**
     * =========================================================
     * INSTALLATION LIMIT TESTS
     * =========================================================.
     */
    public function test_edit_installation_limit_success()
    {
        $order = Order::factory()->withRelations()->create();

        $this->mockLicenseController();

        $response = $this->postJson('/edit-installation-limit', [
            'orderid' => $order->id,
            'limit' => 10,
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => 'Installation Limit Updated']);
    }

    public function test_edit_installation_limit_non_numeric()
    {
        $order = Order::factory()->withRelations()->create();

        $response = $this->postJson('/edit-installation-limit', [
            'orderid' => $order->id,
            'limit' => 'abc',
        ]);

        $response->assertStatus(422);
    }

    public function test_edit_installation_limit_negative_value()
    {
        $order = Order::factory()->withRelations()->create();

        $response = $this->postJson('/edit-installation-limit', [
            'orderid' => $order->id,
            'limit' => -5,
        ]);

        $response->assertStatus(422);
    }
}
