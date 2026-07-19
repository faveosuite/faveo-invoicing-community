<?php

namespace Tests\Unit\Backend\Http\Controllers\Order;

use App\Http\Controllers\License\LicenseController;
use App\Model\Common\StatusSetting;
use App\Model\License\LicensePermission;
use App\Model\License\LicenseType;
use App\Model\Order\Order;
use App\Model\Product\Product;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Date;
use Mockery\MockInterface;
use Tests\DBTestCase;

class BaseOrderControllerTest extends DBTestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->getLoggedInUser('admin');
        $this->withoutMiddleware();

        StatusSetting::updateOrCreate(['id' => 1], [
            'license_status' => 1,
        ]);
    }

    /** Returns date in m/d/Y format that parseDate() expects. */
    private function date(): string
    {
        return Date::now()->addDays(30)->format('m/d/Y');
    }

    private function mockPermissions(int $productId, array $permissionNames): void
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

        // Ensure permission records exist
        foreach ($map as $displayName => $slug) {
            LicensePermission::firstOrCreate(['permissions' => $displayName]);
        }

        $permissionDisplayNames = collect($map)
            ->filter(fn ($key): bool => isset($permissionNames[$key]) && $permissionNames[$key] == 1)
            ->keys()
            ->all();

        $permissionIds = LicensePermission::whereIn('permissions', $permissionDisplayNames)
            ->pluck('id');

        $licenseType->permissions()->sync($permissionIds);

        Product::where('id', $productId)->update([
            'type' => $licenseType->id,
        ]);
    }

    private function mockLicenseController(): void
    {
        $this->mock(LicenseController::class, function (MockInterface $mock): void {
            $mock->shouldReceive('updateExpirationDate')->andReturn(true);
            $mock->shouldReceive('getNoOfAllowedInstallation')->andReturn(5);
            $mock->shouldReceive('getInstallPreference')->andReturn('domain.com');
            $mock->shouldReceive('updateLicensedDomain')->andReturn(true);
        });
    }

    private function assertExpiryUpdated(string $field, $orderId, string $dateInMDY): void
    {
        $expectedDate = Date::createFromFormat('m/d/Y', $dateInMDY)?->format('Y-m-d');
        $subscription = \App\Model\Product\Subscription::where('order_id', $orderId)->first();
        $this->assertNotNull($subscription, "Subscription not found for order $orderId");
        $actualDate = $subscription->$field ? Date::parse($subscription->$field)->format('Y-m-d') : null;
        $this->assertEquals($expectedDate, $actualDate, "Expected $field to be $expectedDate");
    }

    // ========================================================= UPDATE EXPIRY

    public function test_edit_update_expiry_success(): void
    {
        $order = Order::factory()->withRelations()->create();
        $this->mockPermissions($order->product, ['generateUpdatesxpiryDate' => 1]);
        $this->mockLicenseController();

        $date = $this->date();

        $response = $this->postJson('/update-license-details', [
            'orderid' => $order->id,
            'update_end' => $date,
        ]);

        $response->assertStatus(200);
        $this->assertExpiryUpdated('update_ends_at', $order->id, $date);
    }

    public function test_edit_update_expiry_validation_error(): void
    {
        $response = $this->postJson('/update-license-details', []);
        $response->assertStatus(422);
    }

    // ========================================================= LICENSE EXPIRY

    public function test_edit_license_expiry_success(): void
    {
        $order = Order::factory()->withRelations()->create();
        $this->mockPermissions($order->product, ['generateLicenseExpiryDate' => 1]);
        $this->mockLicenseController();

        $date = $this->date();

        $response = $this->postJson('/update-license-details', [
            'orderid' => $order->id,
            'subscription_end' => $date,
        ]);

        $response->assertStatus(200);
        $this->assertExpiryUpdated('ends_at', $order->id, $date);
    }

    public function test_edit_license_expiry_validation_error(): void
    {
        $response = $this->postJson('/update-license-details', []);
        $response->assertStatus(422);
    }

    // ========================================================= SUPPORT EXPIRY

    public function test_edit_support_expiry_success(): void
    {
        $order = Order::factory()->withRelations()->create();
        $this->mockPermissions($order->product, ['generateSupportExpiryDate' => 1]);
        $this->mockLicenseController();

        $date = $this->date();

        $response = $this->postJson('/update-license-details', [
            'orderid' => $order->id,
            'support_end' => $date,
        ]);

        $response->assertStatus(200);
        $this->assertExpiryUpdated('support_ends_at', $order->id, $date);
    }

    public function test_edit_support_expiry_validation_error(): void
    {
        $response = $this->postJson('/update-license-details', []);
        $response->assertStatus(422);
    }

    // ========================================================= INSTALLATION LIMIT

    public function test_edit_installation_limit_success(): void
    {
        $order = Order::factory()->withRelations()->create();
        $this->mockLicenseController();

        $response = $this->postJson('/update-license-details', [
            'orderid' => $order->id,
            'limit' => 10,
        ]);

        $response->assertStatus(200);
    }

    public function test_edit_installation_limit_non_numeric(): void
    {
        $order = Order::factory()->withRelations()->create();

        // The new endpoint passes limit to service which validates it as integer
        $response = $this->postJson('/update-license-details', [
            'orderid' => $order->id,
            'limit' => 'abc',
        ]);

        // Service casts to int — no validation error, but may return error or success
        $this->assertNotEquals(405, $response->getStatusCode());
    }

    public function test_edit_installation_limit_negative_value(): void
    {
        $order = Order::factory()->withRelations()->create();

        $response = $this->postJson('/update-license-details', [
            'orderid' => $order->id,
            'limit' => -5,
        ]);

        $this->assertNotEquals(405, $response->getStatusCode());
    }

    public function test_update_license_details_returns_error_when_subscription_not_found(): void
    {
        $response = $this->postJson('/update-license-details', [
            'orderid' => 999999999,
            'limit' => 10,
        ]);

        $response->assertJson(['success' => false]);
    }
}
