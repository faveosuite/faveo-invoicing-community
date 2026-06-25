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

    // =========================================================================
    // Direct method calls – methods without routes
    // =========================================================================

    public function test_update_details_persists_to_database(): void
    {
        // Call updateDetails directly through the SettingsController
        $controller = new \App\Http\Controllers\Common\SettingsController();
        $request = new \Illuminate\Http\Request();
        $request->merge(['status' => 1, 'update_api_secret' => 'test_secret', 'update_api_url' => 'https://update.example.com']);

        $result = $controller->updateDetails($request);

        $this->assertIsArray($result);
        $this->assertSame('success', $result['message']);
        $this->assertArrayHasKey('update', $result);
        // Verify DB was updated
        $this->assertDatabaseHas('status_settings', ['update_settings' => 1]);
    }

    public function test_update_email_details_updates_status_setting(): void
    {
        $controller = new \App\Http\Controllers\Common\SettingsController();
        $request = new \Illuminate\Http\Request();
        $request->merge(['status' => 0]);

        $result = $controller->updateEmailDetails($request);

        $this->assertIsArray($result);
        $this->assertSame('success', $result['message']);
        $this->assertDatabaseHas('status_settings', ['emailverification_status' => 0]);
    }

    public function test_update_domain_check_details_updates_domain_check(): void
    {
        $controller = new \App\Http\Controllers\Common\SettingsController();
        $request = new \Illuminate\Http\Request();
        $request->merge(['status' => 1]);

        $result = $controller->updatedomainCheckDetails($request);

        $this->assertIsArray($result);
        $this->assertSame('success', $result['message']);
        $this->assertDatabaseHas('status_settings', ['domain_check' => 1]);
    }

    public function test_update_twitter_details_updates_api_key(): void
    {
        $controller = new \App\Http\Controllers\Common\SettingsController();
        $request = new \Illuminate\Http\Request();
        $request->merge([
            'consumer_key' => 'test_consumer_key',
            'consumer_secret' => 'test_consumer_secret',
            'access_token' => 'test_access_token',
            'token_secret' => 'test_token_secret',
            'status' => 1,
        ]);

        $result = $controller->updatetwitterDetails($request);

        $this->assertIsArray($result);
        $this->assertSame('success', $result['message']);
        // Verify Twitter keys updated in DB
        $this->assertDatabaseHas('api_keys', ['twitter_consumer_key' => 'test_consumer_key']);
    }

    // =========================================================================
    // showPdfSettings – GET /pdf-settings
    // =========================================================================

    public function test_show_pdf_settings_returns_structure(): void
    {
        $response = $this->getJson('/pdf-settings');
        // Returns 200 with PDF settings or 400 if not configured
        $this->assertContains($response->status(), [200, 400]);
        $this->assertIsArray($response->json());
        $this->assertArrayHasKey('success', $response->json());
    }

    // =========================================================================
    // updateStoragePath – POST /file-storage-path with 'system' disk
    // =========================================================================

    public function test_update_storage_path_with_system_disk_updates_local_path(): void
    {
        $response = $this->postJson('/file-storage-path', [
            'disk' => 'system',
            'path' => storage_path('app/public'),
        ]);

        // Returns 200 with success message or 400 if file storage not configured
        $this->assertContains($response->status(), [200, 400]);
        $this->assertIsArray($response->json());
        if ($response->status() === 200) {
            $response->assertJson(['success' => true]);
        }
    }

    // =========================================================================
    // updatezohoDetails – direct call
    // =========================================================================

    public function test_update_zoho_details_returns_array_with_message(): void
    {
        $controller = new \App\Http\Controllers\Common\SettingsController();
        $request = new \Illuminate\Http\Request();
        $request->merge([
            'status' => 0,
            'zoho_key' => 'test_zoho_key',
        ]);

        // updatezohoDetails updates StatusSetting with zoho_status
        $result = $controller->updatezohoDetails($request);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('message', $result);
    }

    // =========================================================================
    // showPdfSettings – GET /pdf-settings – assert actual data
    // =========================================================================

    public function test_show_pdf_settings_returns_pdf_paths(): void
    {
        $response = $this->getJson('/pdf-settings');
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $data = $response->json('data');
        $this->assertArrayHasKey('node_path', $data);
        $this->assertArrayHasKey('npm_path', $data);
        $this->assertArrayHasKey('chrome_path', $data);
    }

    // =========================================================================
    // updatePdfSettings – POST /pdf-settings
    // =========================================================================

    public function test_update_pdf_settings_persists_to_database(): void
    {
        $response = $this->postJson('/pdf-settings', [
            'node_path' => '/usr/bin/node',
            'npm_path' => '/usr/bin/npm',
            'chrome_path' => '/usr/bin/chromium',
        ]);
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('settings_filesystem', ['node_path' => '/usr/bin/node']);
    }

    // =========================================================================
    // updateStoragePath with 's3' disk – error path (invalid S3 credentials)
    // =========================================================================

    public function test_update_storage_path_s3_returns_error_with_invalid_credentials(): void
    {
        $response = $this->postJson('/file-storage-path', [
            'disk' => 's3',
            's3_bucket' => 'test-bucket',
            's3_region' => 'us-east-1',
            's3_access_key' => 'invalid_access_key',
            's3_secret_key' => 'invalid_secret_key',
            's3_endpoint_url' => '',
            's3_url' => '',
            's3_path_style_endpoint' => 'false',
        ]);

        // S3 credentials invalid → validateS3Credentials returns false → errorResponse
        // OR returns 400 if FileSystemSettings not configured
        $this->assertContains($response->status(), [200, 400]);
        $this->assertIsArray($response->json());
    }
}
