<?php

namespace Tests\Unit\Backend\Agent\License;

use App\Http\Controllers\License\EncryptDecryptController;
use App\Model\Order\Order;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery\MockInterface;
use Storage;
use Tests\DBTestCase;

class LocalizedLicenseControllerTest extends DBTestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->getLoggedInUser('admin');
    }

    public function test_it_sets_license_mode_to_file_and_generates_keys(): void
    {
        Storage::fake('public');

        $order = Order::factory()->withRelations([
            'number' => 'ORD123',
            'license_mode' => 'Database',
        ])->create();

        $this->mock(EncryptDecryptController::class, function (MockInterface $mock): void {
            $mock->shouldReceive('generateKeys')
                ->once()
                ->with('ORD123');
        });

        $response = $this->postJson('/switch-license-mode', [
            'orderNo' => 'ORD123',
            'choose' => true,
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'message' => __('message.status_change_successfully'),
            ]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'license_mode' => 'File',
        ]);
    }

    public function test_it_sets_license_mode_to_database_and_deletes_files(): void
    {
        Storage::fake('public');

        $order = Order::factory()->withRelations([
            'number' => 'ORD999',
            'license_mode' => 'Database',
        ])->create();

        Storage::disk('public')->put('publicKey-ORD999.txt', 'dummy');
        Storage::disk('public')->put('privateKey-ORD999.txt', 'dummy');
        Storage::disk('public')->put('faveo-license-ORD999.txt', 'dummy');

        $this->assertTrue(Storage::disk('public')->exists('publicKey-ORD999.txt'));

        $response = $this->postJson('/switch-license-mode', [
            'orderNo' => 'ORD999',
            'choose' => false,
        ]);

        $response->assertStatus(200);

        $this->assertFalse(Storage::disk('public')->exists('publicKey-ORD999.txt'));
        $this->assertFalse(Storage::disk('public')->exists('privateKey-ORD999.txt'));
        $this->assertFalse(Storage::disk('public')->exists('faveo-license-ORD999.txt'));

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'license_mode' => 'Database',
        ]);
    }
}
