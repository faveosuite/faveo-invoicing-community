<?php

namespace Tests\Unit\Backend\Traits;

use App\ApiKey;
use App\Model\Common\StatusSetting;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\DBTestCase;

class ApiKeySettingsTest extends DBTestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
        $this->getLoggedInUser('admin');
    }

    // =========================================================================
    // POST /licenseStatus — ApiKeySettings::licenseStatus
    // =========================================================================

    public function test_license_status_with_invalid_key_returns_400(): void
    {
        $response = $this->postJson('/licenseStatus', ['unknown_key' => 1]);
        $response->assertStatus(400);
        $response->assertJson(['success' => false]);
    }

    public function test_license_status_updates_msg91_status(): void
    {
        StatusSetting::create([
            'emailverification_status' => 0,
            'msg91_status' => 0,
            'recaptcha_status' => 0,
        ]);
        $response = $this->postJson('/licenseStatus', ['mstatus' => 1]);
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    public function test_license_status_updates_recaptcha_status(): void
    {
        StatusSetting::create([
            'emailverification_status' => 0,
            'msg91_status' => 0,
            'recaptcha_status' => 0,
        ]);
        $response = $this->postJson('/licenseStatus', ['gcaptchastatus' => 1]);
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    public function test_license_status_updates_pipedrive_status(): void
    {
        StatusSetting::create([
            'emailverification_status' => 0,
            'msg91_status' => 0,
            'recaptcha_status' => 0,
        ]);
        $response = $this->postJson('/licenseStatus', ['pipedrivestatus' => 1]);
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    public function test_license_status_updates_terms_status(): void
    {
        StatusSetting::create([
            'emailverification_status' => 0,
            'msg91_status' => 0,
            'recaptcha_status' => 0,
        ]);
        $response = $this->postJson('/licenseStatus', ['termsStatus' => 1]);
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    public function test_license_status_updates_mailchimp_status(): void
    {
        StatusSetting::create([
            'emailverification_status' => 0,
            'msg91_status' => 0,
            'recaptcha_status' => 0,
        ]);
        $response = $this->postJson('/licenseStatus', ['mailchimpstatus' => 1]);
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    // =========================================================================
    // GET /file-storage — ApiKeySettings::showFileStorage
    // =========================================================================

    public function test_show_file_storage_returns_200(): void
    {
        $response = $this->getJson('/file-storage');
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $response->assertJsonStructure(['success', 'data']);
    }

    // =========================================================================
    // POST /updatepipedriveDetails — ApiKeySettings::updatepipedriveDetails
    // Makes live Pipedrive HTTP call — returns 400 with invalid key in test env
    // =========================================================================

    public function test_update_pipedrive_details_with_invalid_key_returns_400(): void
    {
        ApiKey::factory()->create();
        $response = $this->postJson('/updatepipedriveDetails', [
            'pipedrive_key' => 'invalid-test-key',
            'status' => 0,
        ]);
        // Pipedrive API rejects invalid key → errorResponse 400
        $response->assertStatus(400);
        $response->assertJson(['success' => false]);
    }

    // =========================================================================
    // POST /updateTermsDetails — ApiKeySettings::updateTermsDetails
    // =========================================================================

    public function test_update_terms_details_with_valid_urls_returns_200(): void
    {
        ApiKey::factory()->create();
        $response = $this->postJson('/updateTermsDetails', [
            'terms_url' => 'https://example.com/terms',
            'privacy_url' => 'https://example.com/privacy',
        ]);
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    // =========================================================================
    // POST /updatemobileDetails — validates fields, then calls MSG91 live
    // =========================================================================

    public function test_update_mobile_details_missing_auth_key_returns_422(): void
    {
        $response = $this->postJson('/updatemobileDetails', [
            'msg91_sender' => 'SENDER',
            'msg91_template_id' => 'TMPL123',
        ]);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['msg91_auth_key']);
    }

    public function test_update_mobile_details_with_invalid_auth_key_returns_400(): void
    {
        // MSG91 rejects invalid authkey → errorResponse 400
        ApiKey::factory()->create();
        $response = $this->postJson('/updatemobileDetails', [
            'msg91_auth_key' => 'invalid-key-xyz',
            'msg91_sender' => 'SENDER',
            'msg91_template_id' => 'TMPL123',
        ]);
        $response->assertStatus(400);
        $response->assertJson(['success' => false]);
    }
}
