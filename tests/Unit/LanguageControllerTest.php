<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\DBTestCase;

class LanguageControllerTest extends DBTestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();

        $this->getLoggedInUser('admin');

        $this->withoutMiddleware();
    }

    public function test_get_languages_success()
    {
        $response = $this->getJson('languages');
        $response->assertStatus(200);
    }
    public function test_applies_search_filter_in_languages()
    {
        // Search by tamil
        $tamil = $this->getJson('/languages?search-query=tamil');

        $tamil->assertStatus(200)
              ->assertJsonFragment(['name' => 'Tamil'])
              ->assertJsonMissing(['name' => 'French']);

        // Search by Hindhi
        $hindhi = $this->getJson('/languages?search-query=hindi');
        $hindhi->assertStatus(200)
                 ->assertJsonFragment(['name' => 'Hindi'])
                 ->assertJsonMissing(['name' => 'French']);

        // Search by English
        $english = $this->getJson('/languages?search-query=English');

        $english->assertStatus(200)
                ->assertJsonFragment(['name' => 'English  - United States'])
                ->assertJsonMissing(['name' => 'French']);
    }

    public function test_updates_language_status_successfully()
    {
        // Update Language
        $payload = [
            'locale' => 'ta',
            'status' => true,
        ];

        $response = $this->postJson('/language-toggle', $payload);

        $response->assertStatus(200)
                 ->assertJsonFragment(['message' => __('message.language_status_updated_successfully')]);

        $this->assertDatabaseHas('languages', [
            'locale' => 'ta',
            'status' => 1,
        ]);
    }

    public function test_fails_toggle_language_status_validation()
    {
        // Check the Missing locale
        $response = $this->postJson('/language-toggle', [
            'locale' => '',
            'status' => 1,
        ]);

        $response->assertStatus(400)
                  ->assertJsonFragment([
                      'success' => false,
                      'message' => __('message.something_went_wrong')
                  ]);
    }

    public function test_returns_error_if_language_not_found()
    {
        // Update Language with invalid locale
        $payload = [
            'locale' => 'xx',
            'status' => true,
        ];

        $response = $this->postJson('/language-toggle', $payload);

        $response->assertStatus(400)
                 ->assertJsonFragment(['message' => __('message.language_not_found')]);
    }

}