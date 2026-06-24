<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Models;

use App\ApiKey;
use App\CloudPopUp;
use App\Comment;
use App\DefaultPage;
use App\Demo_page;
use App\ExportDetail;
use App\FileSystemSettings;
use App\Payment_log;
use App\ReportColumn;
use App\ReportSetting;
use App\ThirdPartyApp;
use App\UserLinkReport;
use App\VerificationAttempt;
use App\WhatsappIntegrationUser;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Tests\TestCase;

class RootModelsTest extends TestCase
{
    // ───────────── CloudPopUp ─────────────

    public function test_cloud_popup_table_name(): void
    {
        $this->assertSame('cloud_pop_up', (new CloudPopUp())->getTable());
    }

    public function test_cloud_popup_guarded_is_empty(): void
    {
        $this->assertSame([], (new CloudPopUp())->getGuarded());
    }

    public function test_cloud_popup_get_mappings(): void
    {
        $model = new CloudPopUp();
        $ref = new \ReflectionMethod($model, 'getMappings');
        $mappings = $ref->invoke($model);
        $this->assertArrayHasKey('cloud_top_message', $mappings);
        $this->assertArrayHasKey('cloud_label_field', $mappings);
        $this->assertArrayHasKey('cloud_label_radio', $mappings);
    }

    // ───────────── DefaultPage ─────────────

    public function test_default_page_table_name(): void
    {
        $this->assertSame('default_pages', (new DefaultPage())->getTable());
    }

    public function test_default_page_fillable(): void
    {
        $model = new DefaultPage();
        $this->assertContains('page_id', $model->getFillable());
        $this->assertContains('page_url', $model->getFillable());
    }

    public function test_default_page_get_mappings(): void
    {
        $model = new DefaultPage();
        $ref = new \ReflectionMethod($model, 'getMappings');
        $mappings = $ref->invoke($model);
        $this->assertArrayHasKey('page_id', $mappings);
        $this->assertArrayHasKey('page_url', $mappings);
    }

    // ───────────── Demo_page ─────────────

    public function test_demo_page_table_name(): void
    {
        $this->assertSame('demo_pages', (new Demo_page())->getTable());
    }

    public function test_demo_page_fillable(): void
    {
        $model = new Demo_page();
        $this->assertContains('id', $model->getFillable());
        $this->assertContains('status', $model->getFillable());
    }

    public function test_demo_page_get_mappings_active(): void
    {
        $model = new Demo_page();
        $ref = new \ReflectionMethod($model, 'getMappings');
        $mappings = $ref->invoke($model);
        $this->assertArrayHasKey('status', $mappings);
        $result = $mappings['status'][1](1);
        $this->assertNotNull($result);
    }

    public function test_demo_page_get_mappings_inactive(): void
    {
        $model = new Demo_page();
        $ref = new \ReflectionMethod($model, 'getMappings');
        $mappings = $ref->invoke($model);
        $result = $mappings['status'][1](0);
        $this->assertNotNull($result);
    }

    // ───────────── FileSystemSettings ─────────────

    public function test_filesystem_settings_table_name(): void
    {
        $this->assertSame('settings_filesystem', (new FileSystemSettings())->getTable());
    }

    public function test_filesystem_settings_fillable(): void
    {
        $model = new FileSystemSettings();
        $this->assertContains('disk', $model->getFillable());
        $this->assertContains('local_file_storage_path', $model->getFillable());
    }

    public function test_filesystem_settings_get_mappings(): void
    {
        $model = new FileSystemSettings();
        $ref = new \ReflectionMethod($model, 'getMappings');
        $mappings = $ref->invoke($model);
        $this->assertArrayHasKey('disk', $mappings);
        $this->assertArrayHasKey('local_file_storage_path', $mappings);
    }

    // ───────────── Payment_log ─────────────

    public function test_payment_log_table_name(): void
    {
        $this->assertSame('payment_logs', (new Payment_log())->getTable());
    }

    public function test_payment_log_fillable(): void
    {
        $model = new Payment_log();
        $this->assertContains('from', $model->getFillable());
        $this->assertContains('status', $model->getFillable());
        $this->assertContains('amount', $model->getFillable());
    }

    public function test_payment_log_timestamps_enabled(): void
    {
        $this->assertTrue((new Payment_log())->timestamps);
    }

    // ───────────── ReportColumn ─────────────

    public function test_report_column_table_name(): void
    {
        $this->assertSame('report_columns', (new ReportColumn())->getTable());
    }

    public function test_report_column_fillable(): void
    {
        $model = new ReportColumn();
        $this->assertContains('key', $model->getFillable());
        $this->assertContains('label', $model->getFillable());
        $this->assertContains('type', $model->getFillable());
        $this->assertContains('default', $model->getFillable());
    }

    public function test_report_column_user_link_reports_relation(): void
    {
        $this->assertInstanceOf(HasMany::class, (new ReportColumn())->userLinkReports());
    }

    // ───────────── ReportSetting ─────────────

    public function test_report_setting_table_name(): void
    {
        $this->assertSame('report_settings', (new ReportSetting())->getTable());
    }

    public function test_report_setting_fillable(): void
    {
        $this->assertContains('records', (new ReportSetting())->getFillable());
    }

    public function test_report_setting_get_mappings(): void
    {
        $model = new ReportSetting();
        $ref = new \ReflectionMethod($model, 'getMappings');
        $mappings = $ref->invoke($model);
        $this->assertArrayHasKey('records', $mappings);
    }

    // ───────────── ThirdPartyApp ─────────────

    public function test_third_party_app_table_name(): void
    {
        $this->assertSame('third_party_apps', (new ThirdPartyApp())->getTable());
    }

    public function test_third_party_app_fillable(): void
    {
        $model = new ThirdPartyApp();
        $this->assertContains('app_name', $model->getFillable());
        $this->assertContains('app_key', $model->getFillable());
        $this->assertContains('app_secret', $model->getFillable());
    }

    public function test_third_party_app_get_mappings(): void
    {
        $model = new ThirdPartyApp();
        $ref = new \ReflectionMethod($model, 'getMappings');
        $mappings = $ref->invoke($model);
        $this->assertArrayHasKey('app_name', $mappings);
        $this->assertArrayHasKey('app_key', $mappings);
        $this->assertArrayHasKey('app_secret', $mappings);
    }

    // ───────────── UserLinkReport ─────────────

    public function test_user_link_report_table_name(): void
    {
        $this->assertSame('users_link_reports', (new UserLinkReport())->getTable());
    }

    public function test_user_link_report_fillable(): void
    {
        $model = new UserLinkReport();
        $this->assertContains('user_id', $model->getFillable());
        $this->assertContains('column_id', $model->getFillable());
        $this->assertContains('type', $model->getFillable());
    }

    public function test_user_link_report_user_relation(): void
    {
        $this->assertInstanceOf(BelongsTo::class, (new UserLinkReport())->user());
    }

    public function test_user_link_report_report_column_relation(): void
    {
        $this->assertInstanceOf(BelongsTo::class, (new UserLinkReport())->reportColumn());
    }

    // ───────────── VerificationAttempt ─────────────

    public function test_verification_attempt_table_name(): void
    {
        $this->assertSame('verification_attempts', (new VerificationAttempt())->getTable());
    }

    public function test_verification_attempt_primary_key(): void
    {
        $this->assertSame('user_id', (new VerificationAttempt())->getKeyName());
    }

    public function test_verification_attempt_fillable(): void
    {
        $model = new VerificationAttempt();
        $this->assertContains('user_id', $model->getFillable());
        $this->assertContains('mobile_attempt', $model->getFillable());
        $this->assertContains('email_attempt', $model->getFillable());
    }

    public function test_verification_attempt_user_relation(): void
    {
        $this->assertInstanceOf(BelongsTo::class, (new VerificationAttempt())->user());
    }

    // ───────────── ApiKey ─────────────

    public function test_api_key_table_name(): void
    {
        $this->assertSame('api_keys', (new ApiKey())->getTable());
    }

    public function test_api_key_fillable_contains_key_fields(): void
    {
        $model = new ApiKey();
        $fillable = $model->getFillable();
        $this->assertContains('rzp_key', $fillable);
        $this->assertContains('stripe_key', $fillable);
        $this->assertContains('pipedrive_api_key', $fillable);
        $this->assertContains('verification_preference', $fillable);
    }

    public function test_api_key_get_log_url_default(): void
    {
        $model = new ApiKey();
        $url = $model->getLogUrl();
        $this->assertStringContainsString('third-party-integration', $url);
    }

    public function test_api_key_get_log_name_default(): void
    {
        $model = new ApiKey();
        $this->assertSame('api_key', $model->getLogName());
    }

    public function test_api_key_get_mappings_callbacks(): void
    {
        $model = new ApiKey();
        $ref = new \ReflectionMethod($model, 'getMappings');
        $mappings = $ref->invoke($model);
        $this->assertArrayHasKey('rzp_key', $mappings);
        $this->assertArrayHasKey('require_pipedrive_user_verification', $mappings);
        $yesResult = $mappings['require_pipedrive_user_verification'][1](1);
        $this->assertNotNull($yesResult);
        $noResult = $mappings['require_pipedrive_user_verification'][1](0);
        $this->assertNotNull($noResult);
    }

    // ───────────── Comment ─────────────

    public function test_comment_table_name(): void
    {
        $this->assertSame('comments', (new Comment())->getTable());
    }

    public function test_comment_fillable(): void
    {
        $model = new Comment();
        $this->assertContains('user_id', $model->getFillable());
        $this->assertContains('updated_by_user_id', $model->getFillable());
        $this->assertContains('description', $model->getFillable());
    }

    public function test_comment_user_relation(): void
    {
        $this->assertInstanceOf(BelongsTo::class, (new Comment())->user());
    }

    // ───────────── ExportDetail ─────────────

    public function test_export_detail_table_name(): void
    {
        $this->assertSame('export_details', (new ExportDetail())->getTable());
    }

    public function test_export_detail_fillable(): void
    {
        $model = new ExportDetail();
        $this->assertContains('user_id', $model->getFillable());
        $this->assertContains('file', $model->getFillable());
        $this->assertContains('file_path', $model->getFillable());
        $this->assertContains('name', $model->getFillable());
    }

    public function test_export_detail_user_relation(): void
    {
        $this->assertInstanceOf(BelongsTo::class, (new ExportDetail())->user());
    }

    public function test_export_detail_get_mappings(): void
    {
        $model = new ExportDetail();
        $ref = new \ReflectionMethod($model, 'getMappings');
        $mappings = $ref->invoke($model);
        $this->assertArrayHasKey('user_id', $mappings);
        $this->assertArrayHasKey('file', $mappings);
    }

    // ───────────── WhatsappIntegrationUser ─────────────

    public function test_whatsapp_integration_user_table_name(): void
    {
        $this->assertSame('whatsapp_integration_user', (new WhatsappIntegrationUser())->getTable());
    }

    public function test_whatsapp_integration_user_fillable(): void
    {
        $model = new WhatsappIntegrationUser();
        $this->assertContains('waba_id', $model->getFillable());
        $this->assertContains('user_id', $model->getFillable());
        $this->assertContains('access_token', $model->getFillable());
    }

    public function test_whatsapp_integration_user_user_relation(): void
    {
        $this->assertInstanceOf(BelongsTo::class, (new WhatsappIntegrationUser())->user());
    }

    public function test_whatsapp_integration_user_get_mappings(): void
    {
        $model = new WhatsappIntegrationUser();
        $ref = new \ReflectionMethod($model, 'getMappings');
        $mappings = $ref->invoke($model);
        $this->assertArrayHasKey('phone_number', $mappings);
        $this->assertArrayHasKey('waba_id', $mappings);
    }

    public function test_whatsapp_integration_user_access_token_attribute_exists(): void
    {
        // accessToken() is a new-style Attribute with set/get
        $this->assertTrue(method_exists(WhatsappIntegrationUser::class, 'accessToken'));
    }

    public function test_whatsapp_integration_user_access_token_decrypts_on_get(): void
    {
        $model = new WhatsappIntegrationUser();
        $model->access_token = 'my-secret';
        $this->assertSame('my-secret', $model->access_token);
    }

    public function test_whatsapp_integration_user_access_token_returns_raw_on_decrypt_failure(): void
    {
        $model = new WhatsappIntegrationUser();
        $model->setRawAttributes(['access_token' => 'not-encrypted']);
        // DecryptException falls back to raw value
        $this->assertSame('not-encrypted', $model->access_token);
    }
}
