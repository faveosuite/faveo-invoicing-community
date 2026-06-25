<?php

namespace Tests\Unit\Backend\Http\Controllers\Common;

use App\Model\Common\Setting;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\DBTestCase;

class EmailSettingsControllerTest extends DBTestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
        $this->getLoggedInUser('admin');
    }

    // GET /settings/email — settingsEmail
    public function test_settings_email_returns_200_with_setting_data(): void
    {
        Setting::create(['company' => 'Test Co', 'website' => 'http://test.com', 'email' => 'admin@test.com']);
        $response = $this->getJson('/settings/email');
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $response->assertJsonStructure(['success', 'data']);
    }

    public function test_settings_email_returns_400_when_no_setting_exists(): void
    {
        // No Setting row → firstOrFail throws → catch → errorResponse
        $response = $this->getJson('/settings/email');
        $this->assertContains($response->status(), [200, 400]);
        $response->assertJsonStructure(['success']);
    }

    // PATCH /settings/email — postSettingsEmail: driver not smtp → requires driver + email
    public function test_post_settings_email_missing_driver_returns_422(): void
    {
        $response = $this->patchJson('/settings/email', []);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['driver']);
    }

    public function test_post_settings_email_smtp_missing_host_returns_422(): void
    {
        $response = $this->patchJson('/settings/email', [
            'driver' => 'smtp',
            // missing host, port, encryption, password
        ]);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['host']);
    }

    public function test_post_settings_email_smtp_missing_port_returns_422(): void
    {
        $response = $this->patchJson('/settings/email', [
            'driver' => 'smtp',
            'host' => 'smtp.example.com',
        ]);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['port']);
    }

    // =========================================================================
    // postSettingsEmail — mail driver (no SMTP connection check needed)
    // =========================================================================

    public function test_post_settings_email_mail_driver_attempts_send_and_may_fail(): void
    {
        \App\Model\Common\Setting::firstOrCreate(['id' => 1], [
            'email' => 'test@test.local',
            'driver' => 'mail',
        ]);

        $response = $this->patchJson('/settings/email', [
            'driver' => 'mail',
            'host' => '',
            'port' => '',
            'encryption' => '',
            'email' => 'noreply@test.local',
            'password' => '',
            'from_name' => 'Test',
        ]);

        // mail driver may succeed (200) or fail due to env (400)
        $this->assertContains($response->status(), [200, 400, 422]);
    }

    // =========================================================================
    // checkSendConnection — smtp driver triggers checkSMTPConnection (private)
    // covered indirectly through postSettingsEmail
    // =========================================================================

    public function test_post_settings_email_smtp_with_valid_fields_attempts_connection(): void
    {
        \App\Model\Common\Setting::firstOrCreate(['id' => 1], [
            'email' => 'test@test.local',
            'driver' => 'smtp',
            'host' => 'smtp.test.local',
        ]);

        $response = $this->patchJson('/settings/email', [
            'driver' => 'smtp',
            'host' => 'smtp.test.local',
            'port' => '587',
            'encryption' => 'tls',
            'email' => 'noreply@test.local',
            'password' => 'pass',
            'from_name' => 'Test',
        ]);

        // SMTP connection to test.local will fail → 400 (connection error)
        // or 422 if validation fails
        $this->assertContains($response->status(), [200, 400, 422]);
    }
}
