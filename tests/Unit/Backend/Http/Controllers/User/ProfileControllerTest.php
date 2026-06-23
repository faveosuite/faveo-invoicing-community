<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Http\Controllers\User;

use Tests\DBTestCase;

class ProfileControllerTest extends DBTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\App\Http\Middleware\Install::class);
    }

    // --- GET /profile: role gates ---

    public function test_admin_gets_200(): void
    {
        $this->getLoggedInUser('admin');
        $this->getJson('/profile')->assertStatus(200);
    }

    public function test_client_is_redirected_302(): void
    {
        $this->getLoggedInUser('user');
        $this->getJson('/profile')->assertStatus(302);
    }

    public function test_guest_gets_401(): void
    {
        $this->getJson('/profile')->assertStatus(401);
    }

    // --- Response shape: verify actual profile data ---

    public function test_profile_response_has_all_required_data_keys(): void
    {
        $this->getLoggedInUser('admin');
        $response = $this->getJson('/profile');

        $response->assertStatus(200);
        $this->assertTrue($response->json('success'));

        $data = $response->json('data');
        foreach (['user', 'timezones', 'bussinesses', 'is2faEnabled'] as $key) {
            $this->assertArrayHasKey($key, $data, "Profile data missing key: $key");
        }
    }

    public function test_profile_user_object_contains_correct_admin_data(): void
    {
        $this->getLoggedInUser('admin');
        $response = $this->getJson('/profile');

        $response->assertStatus(200);
        $user = $response->json('data.user');

        // Verify the returned user matches the logged-in admin
        $this->assertSame($this->user->id, $user['id']);
        $this->assertSame($this->user->email, $user['email']);
        $this->assertSame('admin', $user['role']);

        // Verify the user object has the expected field set
        foreach (['id', 'first_name', 'last_name', 'email', 'role', 'user_name', 'company'] as $field) {
            $this->assertArrayHasKey($field, $user, "Profile user missing field: $field");
        }
    }

    // --- GET /profile/countries ---

    public function test_countries_returns_200_for_admin(): void
    {
        $this->getLoggedInUser('admin');
        $this->getJson('/profile/countries')->assertStatus(200);
    }

    // --- GET /profile/states/{countryCode} ---

    public function test_states_for_us_returns_200(): void
    {
        $this->getLoggedInUser('admin');
        $this->getJson('/profile/states/US')->assertStatus(200);
    }

    public function test_states_for_invalid_country_code_returns_200_without_crash(): void
    {
        $this->getLoggedInUser('admin');
        $this->getJson('/profile/states/XX')->assertStatus(200);
    }

    // --- PATCH /profile ---

    public function test_update_profile_guest_returns_401(): void
    {
        $this->patchJson('/profile', ['first_name' => 'Test'])->assertStatus(401);
    }
}
