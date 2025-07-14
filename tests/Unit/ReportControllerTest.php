<?php

namespace Tests\Unit;

use App\User;
use Tests\DBTestCase;

class ReportControllerTest extends DBTestCase
{
    /**
     * A basic unit test example.
     */
    public function test_addRecords_db_return_success()
    {
        $user = User::factory()->create(['role' => 'admin']);
        $this->actingAs($user);
        $response = $this->call('POST', url('add_records'), [
            'records' => '14',
        ]);
        $response->assertStatus(302);
    }
}
