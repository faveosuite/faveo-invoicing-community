<?php

namespace Tests\Unit\Backend\Http\Controllers\Common;

use App\Facades\Attach;
use App\Model\Common\SeoDefaultPage;
use App\Services\Seo\SeoFileGenerator;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Tests\DBTestCase;

class SeoDefaultPageControllerTest extends DBTestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
        $this->getLoggedInUser('admin');

        SeoDefaultPage::query()->delete();
    }

    // --- index() ---

    public function test_index_returns_the_seeded_rows(): void
    {
        SeoDefaultPage::factory()->create(['page_key' => 'login']);
        SeoDefaultPage::factory()->create(['page_key' => 'forgot_password']);

        $response = $this->getJson('/seo/default-pages');

        $response->assertStatus(200)->assertJsonCount(2, 'data.data');
    }

    public function test_index_filters_by_search_query_against_page_key(): void
    {
        SeoDefaultPage::factory()->create(['page_key' => 'login']);
        SeoDefaultPage::factory()->create(['page_key' => 'forgot_password']);

        $response = $this->getJson('/seo/default-pages?search-query=login');

        $response->assertStatus(200)->assertJsonCount(1, 'data.data');
    }

    public function test_index_filters_by_search_query_against_meta_title(): void
    {
        SeoDefaultPage::factory()->create(['page_key' => 'login', 'meta_title' => 'Sign In Page']);
        SeoDefaultPage::factory()->create(['page_key' => 'forgot_password', 'meta_title' => 'Reset Access']);

        $response = $this->getJson('/seo/default-pages?search-query=Sign In');

        $response->assertStatus(200)->assertJsonCount(1, 'data.data');
    }

    public function test_index_returns_empty_when_no_rows_exist(): void
    {
        $response = $this->getJson('/seo/default-pages');

        $response->assertStatus(200)->assertJsonCount(0, 'data.data');
    }

    // --- show() ---

    public function test_show_returns_the_row_for_the_given_page_key(): void
    {
        SeoDefaultPage::factory()->create([
            'page_key' => 'login',
            'meta_title' => 'Sign In',
            'meta_description' => 'Access your account',
        ]);

        $response = $this->getJson('/seo/default-pages/login');

        $response->assertStatus(200);
        $response->assertJsonPath('data.meta_title', 'Sign In');
        $response->assertJsonPath('data.meta_description', 'Access your account');
    }

    public function test_show_resolves_the_og_image_to_a_url_when_set(): void
    {
        Attach::shouldReceive('getUrlPath')->once()->with('images/login-og.png')->andReturn('https://cdn.test/login-og.png');
        SeoDefaultPage::factory()->create(['page_key' => 'login', 'og_image' => 'login-og.png']);

        $response = $this->getJson('/seo/default-pages/login');

        $response->assertStatus(200)->assertJsonPath('data.og_image', 'https://cdn.test/login-og.png');
    }

    public function test_show_returns_404_for_an_unknown_page_key(): void
    {
        $response = $this->getJson('/seo/default-pages/does-not-exist');

        $response->assertStatus(404)->assertJsonFragment(['message' => __('message.no-record')]);
    }

    // --- update() ---

    public function test_update_persists_the_new_meta_fields(): void
    {
        SeoDefaultPage::factory()->create(['page_key' => 'login']);
        $this->mock(SeoFileGenerator::class, fn ($mock) => $mock->shouldReceive('generateAll')->once());

        $response = $this->patchJson('/seo/default-pages/login', [
            'meta_title' => 'New Sign In Title',
            'meta_description' => 'New description',
        ]);

        $response->assertStatus(200)->assertJsonFragment(['message' => __('message.updated-successfully')]);
        $this->assertDatabaseHas('seo_default_pages', [
            'page_key' => 'login',
            'meta_title' => 'New Sign In Title',
            'meta_description' => 'New description',
        ]);
    }

    public function test_update_returns_404_for_an_unknown_page_key(): void
    {
        $response = $this->patchJson('/seo/default-pages/does-not-exist', ['meta_title' => 'X']);

        $response->assertStatus(404)->assertJsonFragment(['message' => __('message.no-record')]);
    }

    public function test_update_cannot_change_the_page_key_itself(): void
    {
        SeoDefaultPage::factory()->create(['page_key' => 'login']);
        $this->mock(SeoFileGenerator::class, fn ($mock) => $mock->shouldReceive('generateAll')->once());

        $this->patchJson('/seo/default-pages/login', [
            'page_key' => 'hacked',
            'meta_title' => 'Still Login',
        ]);

        $this->assertDatabaseHas('seo_default_pages', ['page_key' => 'login', 'meta_title' => 'Still Login']);
        $this->assertDatabaseMissing('seo_default_pages', ['page_key' => 'hacked']);
    }

    public function test_update_uploads_and_persists_an_og_image_filename(): void
    {
        SeoDefaultPage::factory()->create(['page_key' => 'login']);
        $this->mock(SeoFileGenerator::class, fn ($mock) => $mock->shouldReceive('generateAll')->once());
        Attach::shouldReceive('put')->once()->andReturn('login-og-abc123.png');

        $response = $this->post('/seo/default-pages/login', [
            '_method' => 'PATCH',
            'og_image' => UploadedFile::fake()->image('login-og.png'),
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('seo_default_pages', [
            'page_key' => 'login',
            'og_image' => 'login-og-abc123.png',
        ]);
    }

    public function test_update_rejects_a_non_image_og_image_file(): void
    {
        SeoDefaultPage::factory()->create(['page_key' => 'login']);

        $response = $this->postJson('/seo/default-pages/login', [
            '_method' => 'PATCH',
            'og_image' => UploadedFile::fake()->create('doc.pdf', 10, 'application/pdf'),
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['og_image']);
    }

    public function test_update_persists_og_same_as_meta_as_a_boolean(): void
    {
        SeoDefaultPage::factory()->create(['page_key' => 'login', 'og_same_as_meta' => false]);
        $this->mock(SeoFileGenerator::class, fn ($mock) => $mock->shouldReceive('generateAll')->once());

        $response = $this->patchJson('/seo/default-pages/login', ['og_same_as_meta' => true]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('seo_default_pages', ['page_key' => 'login', 'og_same_as_meta' => 1]);
    }

    public function test_update_still_returns_success_even_if_seo_file_regeneration_fails(): void
    {
        SeoDefaultPage::factory()->create(['page_key' => 'login']);
        $this->mock(SeoFileGenerator::class, function ($mock) {
            $mock->shouldReceive('generateAll')->once()->andThrow(new \RuntimeException('disk full'));
        });

        $response = $this->patchJson('/seo/default-pages/login', ['meta_title' => 'X']);

        $response->assertStatus(200)->assertJsonFragment(['message' => __('message.updated-successfully')]);
    }
}
