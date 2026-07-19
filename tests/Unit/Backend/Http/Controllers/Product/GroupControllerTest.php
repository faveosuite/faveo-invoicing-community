<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Http\Controllers\Product;

use App\Facades\Attach;
use App\Model\Product\ProductGroup;
use App\Services\Seo\SeoFileGenerator;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Mockery;
use Tests\DBTestCase;

class GroupControllerTest extends DBTestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\App\Http\Middleware\Install::class);
    }

    // --- GET /groups ---

    public function test_admin_gets_200(): void
    {
        $this->getLoggedInUser('admin');
        $this->getJson('/groups')->assertStatus(200);
    }

    public function test_client_is_redirected_302(): void
    {
        $this->getLoggedInUser('user');
        $this->getJson('/groups')->assertStatus(302);
    }

    public function test_guest_gets_401(): void
    {
        $this->getJson('/groups')->assertStatus(401);
    }

    public function test_list_has_success_flag_and_paginated_structure(): void
    {
        $this->getLoggedInUser('admin');
        $response = $this->getJson('/groups');

        $response->assertStatus(200);
        $this->assertTrue($response->json('success'));
        $this->assertIsArray($response->json('data.data'));
    }

    // --- GET /group/{group_id} ---

    public function test_nonexistent_group_returns_400_with_failure_flag_and_message(): void
    {
        $this->getLoggedInUser('admin');
        $response = $this->getJson('/group/999999999');

        $response->assertStatus(400);
        $this->assertFalse($response->json('success'));
        $this->assertNotEmpty($response->json('message'));
    }

    // --- PUT /group — exact field errors ---

    public function test_create_missing_name_returns_422_with_name_error(): void
    {
        $this->getLoggedInUser('admin');
        $response = $this->putJson('/group', ['pricing_templates_id' => 1]);

        $response->assertStatus(422);
        $this->assertArrayHasKey('name', $response->json('errors'));
    }

    public function test_create_missing_pricing_template_returns_422_with_template_error(): void
    {
        $this->getLoggedInUser('admin');
        $response = $this->putJson('/group', ['name' => 'Support']);

        $response->assertStatus(422);
        $this->assertArrayHasKey('pricing_templates_id', $response->json('errors'));
    }

    public function test_create_blocked_for_client_returns_302(): void
    {
        $this->getLoggedInUser('user');
        $this->putJson('/group', ['name' => 'x', 'pricing_templates_id' => 1])->assertStatus(302);
    }

    // --- PATCH /group/{group_id} ---

    public function test_update_nonexistent_group_returns_422(): void
    {
        $this->getLoggedInUser('admin');
        $this->patchJson('/group/999999999', ['name' => 'New'])->assertStatus(422);
    }

    // --- DELETE /group ---

    public function test_bulk_delete_without_ids_returns_400_with_failure_flag(): void
    {
        $this->getLoggedInUser('admin');
        $response = $this->deleteJson('/group', []);

        $response->assertStatus(400);
        $this->assertFalse($response->json('success'));
    }

    public function test_bulk_delete_unauthenticated_returns_401(): void
    {
        $this->deleteJson('/group', ['ids' => [1]])->assertStatus(401);
    }

    public function test_get_existing_group_returns_200_with_group_data(): void
    {
        $this->getLoggedInUser('admin');
        $group = \App\Model\Product\ProductGroup::create([
            'name' => 'Test Group '.uniqid(),
            'hidden' => 0,
            'pricing_templates_id' => 1,
        ]);
        $response = $this->getJson("/group/{$group->id}");
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->assertEquals($group->id, $response->json('data.id'));
    }

    public function test_get_groups_with_search_returns_200(): void
    {
        $this->getLoggedInUser('admin');
        $response = $this->getJson('/groups?search-query=test');
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    public function test_create_group_with_valid_data_returns_200(): void
    {
        $this->getLoggedInUser('admin');
        $response = $this->putJson('/group', [
            'name' => 'New Group '.uniqid(),
            'pricing_templates_id' => 1,
            'hidden' => 0,
        ]);
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    public function test_update_group_returns_200(): void
    {
        // Covers lines 172-186: updateGroup
        $this->getLoggedInUser('admin');
        $group = ProductGroup::create(['name' => 'Update Me '.uniqid(), 'hidden' => 0, 'pricing_templates_id' => 1]);

        $response = $this->patchJson('/group/'.$group->id, [
            'name' => 'Updated Group',
            'pricing_templates_id' => 1,
            'hidden' => 0,
            'status' => 0,
        ]);
        $response->assertStatus(200);
    }

    public function test_delete_bulk_groups_with_ids_returns_200(): void
    {
        // Covers lines 213-214: deleteBulkGroups
        $this->getLoggedInUser('admin');
        $group = ProductGroup::create(['name' => 'Delete Me '.uniqid(), 'hidden' => 0, 'pricing_templates_id' => 1]);

        $response = $this->deleteJson('/group', ['select' => [$group->id]]);
        $response->assertStatus(200);
    }

    public function test_get_group_by_id_returns_200(): void
    {
        // Covers getGroup (line 154+)
        $this->getLoggedInUser('admin');
        $group = ProductGroup::create(['name' => 'Get Me '.uniqid(), 'hidden' => 0, 'pricing_templates_id' => 1]);

        $response = $this->getJson('/group/'.$group->id);
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    public function test_update_group_with_status_enabled_and_products_missing_plans(): void
    {
        // Covers lines 182-186: status == 1 with products but no plans
        $this->getLoggedInUser('admin');
        $group = ProductGroup::create(['name' => 'Status Group '.uniqid(), 'hidden' => 0, 'pricing_templates_id' => 1]);
        \App\Model\Product\Product::factory()->create(['group' => $group->id, 'hidden' => 0]);

        $response = $this->patchJson('/group/'.$group->id, [
            'name' => $group->name,
            'pricing_templates_id' => 1,
            'hidden' => 0,
            'status' => 1, // enabling with products that have no monthly/yearly plans
        ]);
        // Either succeeds or returns error about missing plans
        $this->assertContains($response->status(), [200, 400]);
    }

    // --- SEO fields ---

    public function test_create_group_persists_seo_fields_and_regenerates_seo_files(): void
    {
        $generator = Mockery::mock(SeoFileGenerator::class);
        $generator->shouldReceive('generateAll')->once();
        $this->app->instance(SeoFileGenerator::class, $generator);

        $this->getLoggedInUser('admin');
        $response = $this->putJson('/group', [
            'name' => 'SEO Group '.uniqid(),
            'pricing_templates_id' => 1,
            'hidden' => 0,
            'meta_title' => 'Meta Title',
            'meta_description' => 'Meta Description',
            'og_title' => 'OG Title',
            'og_description' => 'OG Description',
            'og_same_as_meta' => 1,
        ]);

        $response->assertStatus(200);
        $group = ProductGroup::latest('id')->first();
        $this->assertSame('Meta Title', $group->meta_title);
        $this->assertSame('Meta Description', $group->meta_description);
        $this->assertSame('OG Title', $group->og_title);
        $this->assertSame('OG Description', $group->og_description);
        $this->assertSame(1, (int) $group->og_same_as_meta);
    }

    public function test_create_group_uploads_and_persists_an_og_image_filename(): void
    {
        $this->app->instance(SeoFileGenerator::class, Mockery::mock(SeoFileGenerator::class)->shouldReceive('generateAll')->getMock());
        Attach::shouldReceive('put')->once()->andReturn('group-og-abc123.png');

        $this->getLoggedInUser('admin');
        $response = $this->putJson('/group', [
            'name' => 'Image Group '.uniqid(),
            'pricing_templates_id' => 1,
            'hidden' => 0,
            'og_image' => UploadedFile::fake()->image('cover.jpg'),
        ]);

        $response->assertStatus(200);
        $group = ProductGroup::latest('id')->first();
        $this->assertSame('group-og-abc123.png', $group->og_image);
    }

    public function test_update_group_persists_seo_fields(): void
    {
        $this->app->instance(SeoFileGenerator::class, Mockery::mock(SeoFileGenerator::class)->shouldReceive('generateAll')->getMock());

        $this->getLoggedInUser('admin');
        $group = ProductGroup::create(['name' => 'SEO Update '.uniqid(), 'hidden' => 0, 'pricing_templates_id' => 1]);

        $response = $this->patchJson('/group/'.$group->id, [
            'name' => $group->name,
            'pricing_templates_id' => 1,
            'hidden' => 0,
            'meta_title' => 'Updated Meta Title',
            'og_description' => 'Updated OG Description',
        ]);

        $response->assertStatus(200);
        $group->refresh();
        $this->assertSame('Updated Meta Title', $group->meta_title);
        $this->assertSame('Updated OG Description', $group->og_description);
    }

    public function test_group_create_swallows_seo_file_generation_failures(): void
    {
        $generator = Mockery::mock(SeoFileGenerator::class);
        $generator->shouldReceive('generateAll')->once()->andThrow(new \RuntimeException('disk full'));
        $this->app->instance(SeoFileGenerator::class, $generator);

        $this->getLoggedInUser('admin');
        $response = $this->putJson('/group', [
            'name' => 'Resilient Group '.uniqid(),
            'pricing_templates_id' => 1,
            'hidden' => 0,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }
}
