<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Http\Controllers\Common;

use Tests\DBTestCase;

class MonitoringControllerTest extends DBTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
        $this->getLoggedInUser('admin');
    }

    public function test_invalid_type_returns_400(): void
    {
        $response = $this->getJson('/monitoring/check?type=invalid');
        $response->assertStatus(400);
        $response->assertJson(['success' => false]);
    }

    public function test_pulse_type_returns_200_with_data(): void
    {
        $response = $this->getJson('/monitoring/check?type=pulse');
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $response->assertJsonStructure(['data' => ['type', 'allowed']]);
    }

    public function test_horizon_type_returns_200_with_data(): void
    {
        $response = $this->getJson('/monitoring/check?type=horizon');
        $response->assertStatus(200);
        $response->assertJsonPath('data.type', 'horizon');
    }

    public function test_clockwork_type_returns_200_with_data(): void
    {
        $response = $this->getJson('/monitoring/check?type=clockwork');
        $response->assertStatus(200);
        $response->assertJsonPath('data.type', 'clockwork');
    }

    public function test_type_is_case_insensitive(): void
    {
        $response = $this->getJson('/monitoring/check?type=PULSE');
        $response->assertStatus(200);
    }
}
