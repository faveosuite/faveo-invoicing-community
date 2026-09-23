<?php

namespace Tests\Unit\Backend\Models;

use App\ApiKey;
use App\Auto_renewal;
use App\Comment;
use App\Model\Cart\Cart;
use App\Model\Common\StatusSetting;
use App\Payment_log;
use App\ThirdPartyApp;
use App\User;
use App\UserBackupCodes;
use App\VerificationAttempt;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\DBTestCase;

class AppModelsTest extends DBTestCase
{
    use DatabaseTransactions;

    // =========================================================================
    // ThirdPartyApp
    // =========================================================================

    public function test_third_party_app_can_be_created(): void
    {
        $app = ThirdPartyApp::create([
            'app_name' => 'faveo_app_key',
            'app_key' => 'key_'.uniqid(),
            'app_secret' => 'secret_'.uniqid(),
        ]);
        $this->assertInstanceOf(ThirdPartyApp::class, $app);
        $this->assertDatabaseHas('third_party_apps', ['app_name' => 'faveo_app_key']);
    }

    // =========================================================================
    // UserBackupCodes
    // =========================================================================

    public function test_user_backup_codes_can_be_created(): void
    {
        $user = User::factory()->create();
        $backup = UserBackupCodes::create([
            'user_id' => $user->id,
            'backup_codes' => json_encode(['CODE1', 'CODE2']),
        ]);
        $this->assertInstanceOf(UserBackupCodes::class, $backup);
        $this->assertDatabaseHas('user_backup_codes', ['user_id' => $user->id]);
    }

    // =========================================================================
    // VerificationAttempt
    // =========================================================================

    public function test_verification_attempt_can_be_created(): void
    {
        $user = User::factory()->create();
        $attempt = VerificationAttempt::create([
            'user_id' => $user->id,
            'mobile_attempt' => 0,
            'email_attempt' => 0,
        ]);
        $this->assertInstanceOf(VerificationAttempt::class, $attempt);
        $this->assertDatabaseHas('verification_attempts', ['user_id' => $user->id]);
    }

    public function test_verification_attempt_email_can_be_set(): void
    {
        $user = User::factory()->create();
        VerificationAttempt::create([
            'user_id' => $user->id,
            'mobile_attempt' => 0,
            'email_attempt' => 0,
        ]);
        // Update via DB since PK is user_id and model increment targets PK
        DB::table('verification_attempts')
            ->where('user_id', $user->id)
            ->update(['email_attempt' => 3]);
        $this->assertEquals(3, VerificationAttempt::where('user_id', $user->id)->value('email_attempt'));
    }

    // =========================================================================
    // Auto_renewal
    // =========================================================================

    public function test_auto_renewal_can_be_created(): void
    {
        $user = User::factory()->create();
        $renewal = Auto_renewal::create([
            'user_id' => $user->id,
            'customer_id' => 'cus_test_'.uniqid(),
        ]);
        $this->assertInstanceOf(Auto_renewal::class, $renewal);
    }

    public function test_auto_renewal_user_relation_returns_user(): void
    {
        $user = User::factory()->create();
        $renewal = Auto_renewal::create([
            'user_id' => $user->id,
            'customer_id' => 'cus_'.uniqid(),
        ]);
        $this->assertEquals($user->id, $renewal->user->id);
    }

    // =========================================================================
    // Comment
    // =========================================================================

    public function test_comment_can_be_created(): void
    {
        $user = User::factory()->create();
        $comment = Comment::create([
            'user_id' => $user->id,
            'updated_by_user_id' => $user->id,
            'description' => 'Test comment',
        ]);
        $this->assertInstanceOf(Comment::class, $comment);
    }

    public function test_comment_user_relation_returns_user(): void
    {
        $user = User::factory()->create();
        $comment = Comment::create([
            'user_id' => $user->id,
            'updated_by_user_id' => $user->id,
            'description' => 'Relation test',
        ]);
        $this->assertEquals($user->id, $comment->user->id);
    }

    // =========================================================================
    // Payment_log
    // =========================================================================

    public function test_payment_log_can_be_created(): void
    {
        $log = Payment_log::create([
            'date' => now(),
            'subject' => 'Payment received',
            'body' => 'Invoice #1001 paid',
        ]);
        $this->assertInstanceOf(Payment_log::class, $log);
        $this->assertDatabaseHas('payment_logs', ['subject' => 'Payment received']);
    }

    // =========================================================================
    // Cart model – invoice() relationship
    // =========================================================================

    public function test_cart_invoice_is_belongs_to(): void
    {
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, (new Cart())->invoice());
    }

    public function test_cart_casts_contains_coupon_discount(): void
    {
        $casts = (new Cart())->getCasts();
        $this->assertArrayHasKey('coupon_discount', $casts);
    }

    // =========================================================================
    // ApiKey::getLogUrl() and getLogName() – wasChanged() branches
    // =========================================================================

    public function test_api_key_get_log_url_returns_contact_option_when_verification_preference_changed(): void
    {
        // Load real ApiKey, change verification_preference, save → wasChanged() true
        $apiKey = ApiKey::firstOrCreate(['id' => 1]);
        $original = $apiKey->verification_preference;

        // Simulate a change by using a new value
        $newVal = $original === 'email' ? 'mobile' : 'email';
        $apiKey->verification_preference = $newVal;
        $apiKey->save();

        // After save, wasChanged('verification_preference') = true
        $url = $apiKey->getLogUrl();
        $this->assertStringContainsString('contact-option', $url);

        $name = $apiKey->getLogName();
        $this->assertSame('contact_options', $name);
    }

    // =========================================================================
    // StatusSetting – wasChanged branches for getLogUrl/getLogName
    // =========================================================================

    public function test_status_setting_get_log_url_returns_contact_option_when_verification_changed(): void
    {
        $setting = StatusSetting::firstOrCreate(['id' => 1]);
        $original = $setting->emailverification_status;

        // Change emailverification_status to trigger wasChanged()
        $setting->emailverification_status = $original ? 0 : 1;
        $setting->save();

        $url = $setting->getLogUrl();
        $this->assertStringContainsString('contact-option', $url);

        $name = $setting->getLogName();
        $this->assertSame('contact_options', $name);
    }

    public function test_status_setting_get_log_url_returns_tenant_when_cloud_changed(): void
    {
        $setting = StatusSetting::firstOrCreate(['id' => 1]);
        $original = $setting->cloud_button;

        // Change cloud_button to trigger wasChanged() for cloud check
        $setting->cloud_button = $original === '1' ? '0' : '1';
        $setting->save();

        $url = $setting->getLogUrl();
        // cloud_button was changed → should contain 'view/tenant'
        $this->assertStringContainsString('tenant', $url);

        $name = $setting->getLogName();
        $this->assertSame('cloud', $name);
    }
}
