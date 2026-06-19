<?php

namespace Tests\Unit;

use App\SocialLogin;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\DBTestCase;

class SocialLoginsControllerTest extends DBTestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->getLoggedInUser('admin');

        $this->withoutMiddleware();
    }

    public function test_get_social_logins_initial_page_structure(): void
    {
        $response = $this->getJson('/social-logins');
        $response->assertStatus(200);

        $response->assertJsonFragment(['type' => 'Google']);
        $response->assertJsonFragment(['type' => 'Github']);
        $response->assertJsonFragment(['type' => 'Linkedin']);
    }

    public function test_search_function_for_social_login(): void
    {
        // search query google
        $response = $this->getJson('/social-logins?search-query=google');
        $response->assertStatus(200)
            ->assertJsonFragment(['type' => 'Google']);

        // search query Github
        $response = $this->getJson('/social-logins?search-query=Github');
        $response->assertStatus(200)
            ->assertJsonFragment(['type' => 'Github']);

        // search query Linkedin
        $response = $this->getJson('/social-logins?search-query=linkedin');
        $response->assertStatus(200)
            ->assertJsonFragment(['type' => 'Linkedin']);
    }

    public function test_update_credentials_for_google_github_linkedin(): void
    {
        // Update credentials for google
        $googlePayload = [
            'type' => 'Google',
            'client_id' => 'gid',
            'client_secret' => 'gsecret',
            'redirect_url' => 'https://google.example/cb',
            'status' => 1,
        ];
        $google = $this->postJson('/update-social-login', $googlePayload);
        $google->assertStatus(200)
            ->assertJsonFragment(['message' => __('message.social_login_settings_updated')]);

        // Update credentials for github
        $githubPayload = [
            'type' => 'Github',
            'client_id' => 'gid',
            'client_secret' => 'gsecret',
            'redirect_url' => 'https://github.example/cb',
            'status' => 1,
        ];
        $google = $this->postJson('/update-social-login', $githubPayload);
        $google->assertStatus(200)
            ->assertJsonFragment(['message' => __('message.social_login_settings_updated')]);

        // Update credentials for linkedin
        $linkedinPayload = [
            'type' => 'Linkedin',
            'client_id' => 'gid',
            'client_secret' => 'gsecret',
            'redirect_url' => 'https://linkedin.example/cb',
            'status' => 1,
        ];
        $google = $this->postJson('/update-social-login', $linkedinPayload);
        $google->assertStatus(200)
            ->assertJsonFragment(['message' => __('message.social_login_settings_updated')]);
    }

    public function test_returns_single_social_login_for_edit(): void
    {
        // Google
        $row = SocialLogin::updateOrCreate([
            'type' => 'Google',
            'client_id' => 'gid',
            'client_secret' => 'gsecret',
            'redirect_url' => 'https://google.example/cb',
            'status' => 1,
        ]);

        $response = $this->getJson('/edit/SocialLogins/'.$row->id);

        $response->assertStatus(200)
            ->assertJsonFragment(['id' => $row->id, 'type' => 'Google']);

        // Github
        $row = SocialLogin::updateOrCreate([
            'type' => 'Github',
            'client_id' => 'gid',
            'client_secret' => 'gsecret',
            'redirect_url' => 'https://github.example/cb',
            'status' => 1,
        ]);

        $response = $this->getJson('/edit/SocialLogins/'.$row->id);

        $response->assertStatus(200)
            ->assertJsonFragment(['id' => $row->id, 'type' => 'Github']);

        // Linkedin
        $row = SocialLogin::updateOrCreate([
            'type' => 'Linkedin',
            'client_id' => 'gid',
            'client_secret' => 'gsecret',
            'redirect_url' => 'https://linkedin.example/cb',
            'status' => 1,
        ]);

        $response = $this->getJson('/edit/SocialLogins/'.$row->id);

        $response->assertStatus(200)
            ->assertJsonFragment(['id' => $row->id, 'type' => 'Linkedin']);
    }

    public function test_returns_validation_errors_when_required_fields_missing(): void
    {
        // Check validation error in google
        $googlePayload = [
            'type' => 'Google',
        ];

        $response = $this->postJson('/update-social-login', $googlePayload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['client_id', 'client_secret', 'redirect_url']);

        // Check validation error in github
        $githubPayload = [
            'type' => 'Github',
        ];

        $response = $this->postJson('/update-social-login', $githubPayload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['client_id', 'client_secret', 'redirect_url']);

        // Check validation error in linkedin
        $linkedinPayload = [
            'type' => 'Linkedin',
        ];

        $response = $this->postJson('/update-social-login', $linkedinPayload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['client_id', 'client_secret', 'redirect_url']);
    }
}
