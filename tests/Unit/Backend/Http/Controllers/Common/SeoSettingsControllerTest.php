<?php

namespace Tests\Unit\Backend\Http\Controllers\Common;

use App\Facades\Attach;
use App\Model\Common\CommonSettings;
use App\Model\Common\Setting;
use App\Services\Seo\SeoFileGenerator;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Tests\DBTestCase;

class SeoSettingsControllerTest extends DBTestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
        $this->getLoggedInUser('admin');

        Setting::updateOrCreate(['id' => 1], ['favicon_title' => '', 'favicon_title_client' => '']);
        CommonSettings::where('option_name', 'seo')->delete();
    }

    // --- show() ---

    public function test_show_returns_200_with_defaults_when_nothing_is_configured(): void
    {
        $response = $this->getJson('/seo/settings');

        $response->assertStatus(200)->assertJson(['success' => true]);
        $response->assertJsonPath('data.general_description', '');
        $response->assertJsonPath('data.favicon_title', '');
        $response->assertJsonPath('data.general_og_image', null);
        $response->assertJsonPath('data.general_og_same_as_meta', false);
    }

    public function test_show_returns_configured_text_and_boolean_fields(): void
    {
        Setting::find(1)->update(['favicon_title' => 'Admin Title', 'favicon_title_client' => 'Client Title']);
        CommonSettings::upsert([
            ['option_name' => 'seo', 'optional_field' => 'general_description', 'option_value' => 'A description', 'status' => ''],
            ['option_name' => 'seo', 'optional_field' => 'pages_og_same_as_meta', 'option_value' => '1', 'status' => ''],
        ], ['option_name', 'optional_field'], ['option_value']);

        $response = $this->getJson('/seo/settings');

        $response->assertStatus(200);
        $response->assertJsonPath('data.favicon_title', 'Admin Title');
        $response->assertJsonPath('data.favicon_title_client', 'Client Title');
        $response->assertJsonPath('data.general_description', 'A description');
        $response->assertJsonPath('data.pages_og_same_as_meta', true);
    }

    public function test_show_resolves_the_configured_image_to_a_url(): void
    {
        Attach::shouldReceive('getUrlPath')->once()->with('images/general.png')->andReturn('https://cdn.test/general.png');
        CommonSettings::upsert(
            [['option_name' => 'seo', 'optional_field' => 'general_og_image', 'option_value' => 'general.png', 'status' => '']],
            ['option_name', 'optional_field'],
            ['option_value']
        );

        $response = $this->getJson('/seo/settings');

        $response->assertStatus(200)
            ->assertJsonPath('data.general_og_image', 'https://cdn.test/general.png');
    }

    // --- update() ---

    public function test_update_persists_favicon_title_fields_to_the_settings_table(): void
    {
        $this->mock(SeoFileGenerator::class, fn ($mock) => $mock->shouldReceive('generateAll')->once());

        $response = $this->postJson('/seo/settings', [
            'favicon_title' => 'New Admin Title',
            'favicon_title_client' => 'New Client Title',
        ]);

        $response->assertStatus(200)->assertJsonFragment(['message' => __('message.updated-successfully')]);
        $this->assertDatabaseHas('settings', [
            'id' => 1,
            'favicon_title' => 'New Admin Title',
            'favicon_title_client' => 'New Client Title',
        ]);
    }

    public function test_update_persists_text_fields_to_common_settings(): void
    {
        $this->mock(SeoFileGenerator::class, fn ($mock) => $mock->shouldReceive('generateAll')->once());

        $response = $this->postJson('/seo/settings', [
            'general_description' => 'Updated description',
            'pages_title_format' => '{name} | {company}',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('common_settings', [
            'option_name' => 'seo',
            'optional_field' => 'general_description',
            'option_value' => 'Updated description',
        ]);
        $this->assertDatabaseHas('common_settings', [
            'option_name' => 'seo',
            'optional_field' => 'pages_title_format',
            'option_value' => '{name} | {company}',
        ]);
    }

    public function test_update_persists_boolean_fields_as_1_or_0(): void
    {
        $this->mock(SeoFileGenerator::class, fn ($mock) => $mock->shouldReceive('generateAll')->once());

        $response = $this->postJson('/seo/settings', [
            'general_og_same_as_meta' => true,
            'pages_og_same_as_meta' => false,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('common_settings', [
            'option_name' => 'seo',
            'optional_field' => 'general_og_same_as_meta',
            'option_value' => '1',
        ]);
        $this->assertDatabaseHas('common_settings', [
            'option_name' => 'seo',
            'optional_field' => 'pages_og_same_as_meta',
            'option_value' => '0',
        ]);
    }

    public function test_update_uploads_and_persists_an_og_image_filename(): void
    {
        $this->mock(SeoFileGenerator::class, fn ($mock) => $mock->shouldReceive('generateAll')->once());
        Attach::shouldReceive('put')->once()->andReturn('general-abc123.png');

        $response = $this->post('/seo/settings', [
            'general_og_image' => UploadedFile::fake()->image('general.png'),
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('common_settings', [
            'option_name' => 'seo',
            'optional_field' => 'general_og_image',
            'option_value' => 'general-abc123.png',
        ]);
    }

    public function test_update_rejects_a_non_image_og_image_file(): void
    {
        $response = $this->postJson('/seo/settings', [
            'general_og_image' => UploadedFile::fake()->create('doc.pdf', 10, 'application/pdf'),
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['general_og_image']);
    }

    public function test_update_still_returns_success_even_if_seo_file_regeneration_fails(): void
    {
        // generateAll() failures are caught and report()ed inside the
        // controller — they must never fail the request itself.
        $this->mock(SeoFileGenerator::class, function ($mock) {
            $mock->shouldReceive('generateAll')->once()->andThrow(new \RuntimeException('disk full'));
        });

        $response = $this->postJson('/seo/settings', ['general_description' => 'Some text']);

        $response->assertStatus(200)->assertJsonFragment(['message' => __('message.updated-successfully')]);
    }
}
