<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Models\Common;

use App\Model\Common\ChatScript;
use App\Model\Common\Country;
use App\Model\Common\EmailMobileValidationProviders;
use App\Model\Common\FaveoCloud;
use App\Model\Common\Language;
use App\Model\Common\ManagerSetting;
use App\Model\Common\MsgDeliveryReports;
use App\Model\Common\PipedriveField;
use App\Model\Common\PipedriveFieldOption;
use App\Model\Common\PipedriveLocalFields;
use App\Model\Common\PricingTemplate;
use App\Model\Common\Setting;
use App\Model\Common\SocialMedia;
use App\Model\Common\StatusSetting;
use App\Model\Common\TemplateType;
use App\Model\Common\Website;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Tests\TestCase;

class CommonModelsTest extends TestCase
{
    // ───────────── ChatScript ─────────────

    public function test_chat_script_table_name(): void
    {
        $this->assertSame('chat_scripts', (new ChatScript())->getTable());
    }

    public function test_chat_script_fillable(): void
    {
        $model = new ChatScript();
        $this->assertContains('name', $model->getFillable());
        $this->assertContains('script', $model->getFillable());
        $this->assertContains('on_registration', $model->getFillable());
        $this->assertContains('google_analytics', $model->getFillable());
    }

    public function test_chat_script_get_mappings(): void
    {
        $model = new ChatScript();
        $ref = new \ReflectionMethod($model, 'getMappings');
        $mappings = $ref->invoke($model);
        $this->assertArrayHasKey('name', $mappings);
        $this->assertArrayHasKey('on_registration', $mappings);
        $this->assertArrayHasKey('google_analytics', $mappings);
    }

    public function test_chat_script_mapping_active_inactive_callbacks(): void
    {
        $model = new ChatScript();
        $ref = new \ReflectionMethod($model, 'getMappings');
        $mappings = $ref->invoke($model);
        $active = $mappings['on_registration'][1](1);
        $inactive = $mappings['on_registration'][1](0);
        $this->assertNotNull($active);
        $this->assertNotNull($inactive);
    }

    // ───────────── EmailMobileValidationProviders ─────────────

    public function test_email_mobile_validation_providers_table_name(): void
    {
        $this->assertSame('email_mobile_validation_providers', (new EmailMobileValidationProviders())->getTable());
    }

    public function test_email_mobile_validation_providers_fillable(): void
    {
        $model = new EmailMobileValidationProviders();
        $this->assertContains('provider', $model->getFillable());
        $this->assertContains('api_key', $model->getFillable());
        $this->assertContains('api_secret', $model->getFillable());
        $this->assertContains('mode', $model->getFillable());
        $this->assertContains('accepted_output', $model->getFillable());
    }

    public function test_email_mobile_validation_providers_get_mappings(): void
    {
        $model = new EmailMobileValidationProviders();
        $ref = new \ReflectionMethod($model, 'getMappings');
        $mappings = $ref->invoke($model);
        $this->assertArrayHasKey('provider', $mappings);
        $this->assertArrayHasKey('api_key', $mappings);
    }

    // ───────────── FaveoCloud ─────────────

    public function test_faveo_cloud_table_name(): void
    {
        $this->assertSame('faveo_cloud', (new FaveoCloud())->getTable());
    }

    public function test_faveo_cloud_fillable(): void
    {
        $model = new FaveoCloud();
        $this->assertContains('cloud_central_domain', $model->getFillable());
        $this->assertContains('cloud_cname', $model->getFillable());
    }

    public function test_faveo_cloud_get_mappings(): void
    {
        $model = new FaveoCloud();
        $ref = new \ReflectionMethod($model, 'getMappings');
        $mappings = $ref->invoke($model);
        $this->assertArrayHasKey('cloud_central_domain', $mappings);
        $this->assertArrayHasKey('cloud_cname', $mappings);
    }

    // ───────────── Language ─────────────

    public function test_language_table_name(): void
    {
        $this->assertSame('languages', (new Language())->getTable());
    }

    public function test_language_fillable(): void
    {
        $model = new Language();
        $this->assertContains('name', $model->getFillable());
        $this->assertContains('translation', $model->getFillable());
        $this->assertContains('locale', $model->getFillable());
        $this->assertContains('status', $model->getFillable());
    }

    public function test_language_get_mappings(): void
    {
        $model = new Language();
        $ref = new \ReflectionMethod($model, 'getMappings');
        $mappings = $ref->invoke($model);
        $this->assertArrayHasKey('name', $mappings);
        $this->assertArrayHasKey('status', $mappings);
    }

    public function test_language_mapping_status_callbacks(): void
    {
        $model = new Language();
        $ref = new \ReflectionMethod($model, 'getMappings');
        $mappings = $ref->invoke($model);
        $enabled = $mappings['status'][1](1);
        $disabled = $mappings['status'][1](0);
        $this->assertNotNull($enabled);
        $this->assertNotNull($disabled);
    }

    // ───────────── ManagerSetting ─────────────

    public function test_manager_setting_fillable(): void
    {
        $model = new ManagerSetting();
        $this->assertContains('manager_role', $model->getFillable());
        $this->assertContains('auto_assign', $model->getFillable());
    }

    public function test_manager_setting_get_mappings(): void
    {
        $model = new ManagerSetting();
        $ref = new \ReflectionMethod($model, 'getMappings');
        $mappings = $ref->invoke($model);
        $this->assertArrayHasKey('manager_role', $mappings);
        $this->assertArrayHasKey('auto_assign', $mappings);
    }

    // ───────────── PipedriveField ─────────────

    public function test_pipedrive_field_table_name(): void
    {
        $this->assertSame('pipedrive_fields', (new PipedriveField())->getTable());
    }

    public function test_pipedrive_field_fillable(): void
    {
        $model = new PipedriveField();
        $this->assertContains('field_name', $model->getFillable());
        $this->assertContains('field_key', $model->getFillable());
        $this->assertContains('field_type', $model->getFillable());
    }

    public function test_pipedrive_field_local_field_relation(): void
    {
        $this->assertInstanceOf(BelongsTo::class, (new PipedriveField())->localField());
    }

    public function test_pipedrive_field_pipedrive_groups_relation(): void
    {
        $this->assertInstanceOf(BelongsTo::class, (new PipedriveField())->pipedriveGroups());
    }

    public function test_pipedrive_field_pipedrive_options_relation(): void
    {
        $this->assertInstanceOf(HasMany::class, (new PipedriveField())->pipedriveOptions());
    }

    public function test_pipedrive_field_get_mappings(): void
    {
        $model = new PipedriveField();
        $ref = new \ReflectionMethod($model, 'getMappings');
        $mappings = $ref->invoke($model);
        $this->assertArrayHasKey('field_name', $mappings);
        $this->assertArrayHasKey('field_type', $mappings);
    }

    // ───────────── PipedriveFieldOption ─────────────

    public function test_pipedrive_field_option_guarded_is_empty(): void
    {
        $this->assertSame([], (new PipedriveFieldOption())->getGuarded());
    }

    public function test_pipedrive_field_option_pipedrive_field_relation(): void
    {
        $this->assertInstanceOf(BelongsTo::class, (new PipedriveFieldOption())->pipedriveField());
    }

    // ───────────── PipedriveLocalFields ─────────────

    public function test_pipedrive_local_fields_table_name(): void
    {
        $this->assertSame('pipedrive_local_fields', (new PipedriveLocalFields())->getTable());
    }

    public function test_pipedrive_local_fields_fillable(): void
    {
        $model = new PipedriveLocalFields();
        $this->assertContains('field_name', $model->getFillable());
        $this->assertContains('field_key', $model->getFillable());
    }

    public function test_pipedrive_local_fields_pipedrive_relation(): void
    {
        $this->assertInstanceOf(BelongsTo::class, (new PipedriveLocalFields())->pipedrive());
    }

    // ───────────── PricingTemplate ─────────────

    public function test_pricing_template_fillable(): void
    {
        $model = new PricingTemplate();
        $this->assertContains('data', $model->getFillable());
        $this->assertContains('image', $model->getFillable());
        $this->assertContains('name', $model->getFillable());
    }

    public function test_pricing_template_product_groups_relation(): void
    {
        $this->assertInstanceOf(HasMany::class, (new PricingTemplate())->productGroups());
    }

    // ───────────── SocialMedia ─────────────

    public function test_social_media_table_name(): void
    {
        $this->assertSame('social_media', (new SocialMedia())->getTable());
    }

    public function test_social_media_fillable(): void
    {
        $model = new SocialMedia();
        $this->assertContains('class', $model->getFillable());
        $this->assertContains('fa_class', $model->getFillable());
        $this->assertContains('name', $model->getFillable());
        $this->assertContains('link', $model->getFillable());
    }

    public function test_social_media_get_mappings(): void
    {
        $model = new SocialMedia();
        $ref = new \ReflectionMethod($model, 'getMappings');
        $mappings = $ref->invoke($model);
        $this->assertArrayHasKey('name', $mappings);
        $this->assertArrayHasKey('link', $mappings);
    }

    // ───────────── Website ─────────────

    public function test_website_is_base_model_subclass(): void
    {
        $this->assertInstanceOf(\App\BaseModel::class, new Website());
    }

    // ───────────── StatusSetting ─────────────

    public function test_status_setting_table_name(): void
    {
        $this->assertSame('status_settings', (new StatusSetting())->getTable());
    }

    public function test_status_setting_fillable(): void
    {
        $model = new StatusSetting();
        $fillable = $model->getFillable();
        $this->assertContains('expiry_mail', $fillable);
        $this->assertContains('github_status', $fillable);
        $this->assertContains('whatsapp_status', $fillable);
    }

    public function test_status_setting_get_log_url_default(): void
    {
        $model = new StatusSetting();
        $url = $model->getLogUrl();
        $this->assertStringContainsString('third-party-integration', $url);
    }

    public function test_status_setting_get_log_name_default(): void
    {
        $model = new StatusSetting();
        $this->assertSame('api_key', $model->getLogName());
    }

    public function test_status_setting_get_mappings(): void
    {
        $model = new StatusSetting();
        $ref = new \ReflectionMethod($model, 'getMappings');
        $mappings = $ref->invoke($model);
        $this->assertArrayHasKey('expiry_mail', $mappings);
        $this->assertArrayHasKey('github_status', $mappings);
        // Test enable/disable callbacks
        $enabled = $mappings['expiry_mail'][1](1);
        $this->assertNotNull($enabled);
        $disabled = $mappings['expiry_mail'][1](0);
        $this->assertNotNull($disabled);
    }

    // ───────────── Setting ─────────────

    public function test_setting_table_name(): void
    {
        $this->assertSame('settings', (new Setting())->getTable());
    }

    public function test_setting_fillable(): void
    {
        $model = new Setting();
        $fillable = $model->getFillable();
        $this->assertContains('company', $fillable);
        $this->assertContains('website', $fillable);
        $this->assertContains('email', $fillable);
    }

    public function test_setting_get_image_returns_default_when_null(): void
    {
        $model = new Setting();
        $result = $model->getImage(null, 'images', 'default.png');
        $this->assertSame('default.png', $result);
    }

    public function test_setting_get_image_returns_default_when_empty_string(): void
    {
        $model = new Setting();
        $result = $model->getImage('', 'images', 'fallback.png');
        $this->assertSame('fallback.png', $result);
    }

    public function test_setting_password_attribute_encrypts_on_set(): void
    {
        $model = new Setting();
        $model->password = 'my-password';
        $raw = $model->getAttributes()['password'];
        $this->assertNotSame('my-password', $raw);
        $this->assertNotEmpty($raw);
    }

    public function test_setting_password_attribute_decrypts_on_get(): void
    {
        $model = new Setting();
        $model->password = 'my-password';
        $this->assertSame('my-password', $model->password);
    }

    public function test_setting_password_attribute_returns_null_when_null(): void
    {
        $model = new Setting();
        $model->setRawAttributes(['password' => null]);
        $this->assertNull($model->password);
    }

    public function test_setting_logo_attribute_returns_default_when_null(): void
    {
        $model = new Setting();
        $model->setRawAttributes(['logo' => null]);
        $result = $model->logo;
        $this->assertStringContainsString('agora-invoicing', $result);
    }

    public function test_setting_admin_logo_attribute_returns_default_when_null(): void
    {
        $model = new Setting();
        $model->setRawAttributes(['admin_logo' => null]);
        $result = $model->adminLogo;
        $this->assertStringContainsString('agora_admin_logo', $result);
    }

    public function test_setting_fav_icon_attribute_returns_default_when_null(): void
    {
        $model = new Setting();
        $model->setRawAttributes(['fav_icon' => null]);
        $result = $model->favIcon;
        $this->assertStringContainsString('faveo', $result);
    }

    public function test_setting_get_mappings(): void
    {
        $model = new Setting();
        $ref = new \ReflectionMethod($model, 'getMappings');
        $mappings = $ref->invoke($model);
        $this->assertArrayHasKey('company', $mappings);
        $this->assertArrayHasKey('email', $mappings);
    }

    // ───────────── MsgDeliveryReports ─────────────

    public function test_msg_delivery_reports_fillable(): void
    {
        $model = new MsgDeliveryReports();
        $this->assertContains('mobile_number', $model->getFillable());
        $this->assertContains('request_id', $model->getFillable());
        $this->assertContains('status', $model->getFillable());
    }

    public function test_msg_delivery_reports_readable_status_relation(): void
    {
        $this->assertInstanceOf(BelongsTo::class, (new MsgDeliveryReports())->readableStatus());
    }

    public function test_msg_delivery_reports_user_relation(): void
    {
        $this->assertInstanceOf(BelongsTo::class, (new MsgDeliveryReports())->user());
    }

    public function test_msg_delivery_reports_formatted_sender_id_accessor(): void
    {
        $model = new MsgDeliveryReports();
        $model->sender_id = 'hello';
        $this->assertSame('HELLO', $model->formatted_sender_id);
    }

    public function test_msg_delivery_reports_formatted_sender_id_null(): void
    {
        $model = new MsgDeliveryReports();
        $model->sender_id = null;
        $this->assertSame('', $model->formatted_sender_id);
    }

    // ───────────── TemplateType ─────────────

    public function test_template_type_is_base_model_subclass(): void
    {
        $this->assertInstanceOf(\App\BaseModel::class, new TemplateType());
    }

    // ───────────── Country ─────────────

    public function test_country_table_name(): void
    {
        $this->assertSame('countries', (new Country())->getTable());
    }

    public function test_country_primary_key(): void
    {
        $this->assertSame('country_id', (new Country())->getKeyName());
    }

    public function test_country_fillable(): void
    {
        $model = new Country();
        $this->assertContains('country_name', $model->getFillable());
        $this->assertContains('country_code_char2', $model->getFillable());
        $this->assertContains('status', $model->getFillable());
    }

    public function test_country_currency_relation(): void
    {
        $this->assertInstanceOf(BelongsTo::class, (new Country())->currency());
    }

    public function test_country_users_relation(): void
    {
        $this->assertInstanceOf(HasMany::class, (new Country())->users());
    }
}
