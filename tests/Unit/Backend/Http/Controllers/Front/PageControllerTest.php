<?php

namespace Tests\Unit\Backend\Http\Controllers\Front;

use App\Model\Front\FrontendPage;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\DBTestCase;

class PageControllerTest extends DBTestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
        $this->getLoggedInUser('admin');
    }

    public function test_get_all_pages_success(): void
    {
        FrontendPage::factory()->count(5)->create();

        $response = $this->getJson('/pages');

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data.data');
    }

    public function test_get_all_pages_empty(): void
    {
        $response = $this->getJson('/pages');

        $response->assertStatus(200)
            ->assertJsonCount(0, 'data.data');
    }

    // Test cases for DELETE /pages
    public function test_delete_bulk_pages_success(): void
    {
        $pages = FrontendPage::factory()->count(3)->create();
        $pageIds = $pages->pluck('id')->toArray();

        $response = $this->deleteJson('/pages', ['page_ids' => $pageIds]);

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => __('message.deleted-successfully')]);

        foreach ($pageIds as $id) {
            $this->assertDatabaseMissing('frontend_pages', ['id' => $id]);
        }
    }

    public function test_delete_bulk_pages_validation_error(): void
    {
        $response = $this->deleteJson('/pages', ['page_ids' => []]);

        $response->assertStatus(400)->assertJsonFragment([
            'message' => __('message.select-a-row'),
        ]);
    }

    public function test_get_page_success(): void
    {
        $page = FrontendPage::factory()->create();

        $response = $this->getJson('/page/'.$page->id);

        $response->assertStatus(200)
            ->assertJsonFragment(['type' => $page->type]);
    }

    public function test_get_page_not_found(): void
    {
        $response = $this->getJson('/page/9999');

        $response->assertStatus(400);
    }

    public function test_update_page_success(): void
    {
        $page = FrontendPage::factory()->create();
        $updateData = [
            'type' => 'New type',
            'content' => 'New Content',
        ];

        $response = $this->putJson('/page/'.$page->id, $updateData);

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => __('message.updated-successfully')]);

        $this->assertDatabaseHas('frontend_pages', array_merge(['id' => $page->id], $updateData));
    }

    public function test_update_page_not_found(): void
    {
        $response = $this->putJson('/page/9999');

        $response->assertStatus(400);
    }

    public function test_save_demo_page_success(): void
    {
        $response = $this->postJson('/save/demo', ['status' => 1]);

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => __('message.data_updated_successfully')]);

        $this->assertDatabaseHas('demo_pages', ['status' => 1]);
    }

    public function test_save_demo_page_validation_error(): void
    {
        $response = $this->postJson('/save/demo', ['status' => '']);

        $response->assertStatus(422);
    }
}
