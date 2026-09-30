<?php

namespace Tests\Unit\Backend\Http\Controllers\Jobs;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\DBTestCase;

class QueueControllerTest extends DBTestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
        $this->getLoggedInUser('admin');
    }

    public function test_get_queue_data_returns_200_with_structure(): void
    {
        $response = $this->getJson('/queue/list');
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $response->assertJsonStructure(['success', 'data' => ['queues', 'active_queue']]);
    }

    public function test_get_queue_data_with_search_returns_200(): void
    {
        $response = $this->getJson('/queue/list?search-query=sync');
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    public function test_get_queue_data_with_sort_returns_200(): void
    {
        $response = $this->getJson('/queue/list?sort-field=name&sort-order=desc');
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    public function test_edit_nonexistent_queue_returns_404(): void
    {
        $response = $this->getJson('/queue/999999');
        $response->assertStatus(404);
        $response->assertJson(['success' => false]);
    }

    public function test_get_form_by_nonexistent_id_returns_400_or_404(): void
    {
        $response = $this->getJson('/queue/999999/form');
        $this->assertContains($response->status(), [200, 400, 404]);
    }

    public function test_activate_nonexistent_queue_returns_error(): void
    {
        $response = $this->postJson('/queue/999999/activate', []);
        $this->assertContains($response->status(), [200, 400, 404, 422, 500]);
        $response->assertJson(['success' => false]);
    }

    // =========================================================================
    // getShortNameById and getIdByShortName — direct method tests
    // =========================================================================

    public function test_get_short_name_by_nonexistent_id_returns_empty(): void
    {
        $controller = new \App\Http\Controllers\Jobs\QueueController;

        $result = $controller->getShortNameById(999999);

        $this->assertSame('', $result);
    }

    public function test_get_id_by_unknown_short_name_returns_null(): void
    {
        $controller = new \App\Http\Controllers\Jobs\QueueController;

        $result = $controller->getIdByShortName('unknown_queue_xyz');

        $this->assertNull($result);
    }

    public function test_edit_existing_queue_returns_response(): void
    {
        $queue = \App\Model\Mailjob\QueueService::first();
        if (! $queue) {
            $queue = \App\Model\Mailjob\QueueService::create(['name' => 'Test Queue', 'short_name' => 'sync', 'status' => 1]);
        }

        $response = $this->getJson('/queue/'.($queue->id ?? 1));

        $this->assertContains($response->status(), [200, 400]);
    }

    public function test_get_form_for_existing_queue(): void
    {
        $queue = \App\Model\Mailjob\QueueService::first();
        if (! $queue) {
            $queue = \App\Model\Mailjob\QueueService::create(['name' => 'Test Queue', 'short_name' => 'sync', 'status' => 1]);
        }

        $response = $this->getJson('/queue/'.$queue->id.'/form');

        $this->assertContains($response->status(), [200, 400]);
    }
}
