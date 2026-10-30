<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Http\Controllers\Order;

use App\Http\Middleware\Install;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\DBTestCase;

/**
 * Renew endpoints were reachable without logging in — admin renew extends dates straight
 * away, so a guest could renew any order for free. Real middleware here (no withoutMiddleware).
 */
class RenewRouteAuthTest extends DBTestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(Install::class);
    }

    public function test_guests_cannot_reach_any_renew_endpoint(): void
    {
        $this->postJson('/admin/renew/1', ['plan' => 1, 'payment_method' => 'cash', 'cost' => 0])->assertStatus(401);
        $this->postJson('/client/renew/1', ['plan' => 1])->assertStatus(401);
        $this->getJson('/get-renew-cost?plan=1')->assertStatus(401);
    }

    public function test_a_client_cannot_use_admin_renew(): void
    {
        $this->getLoggedInUser('user');

        $response = $this->postJson('/admin/renew/1', ['plan' => 1, 'payment_method' => 'cash', 'cost' => 0]);

        $this->assertContains($response->status(), [302, 403]); // admin middleware turns non-admins away
    }
}
