<?php

namespace Tests\Unit\Backend\Models;

use App\Auto_renewal;
use App\Comment;
use App\Payment_log;
use App\ThirdPartyApp;
use App\UserBackupCodes;
use App\VerificationAttempt;
use App\User;
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
            'app_name'   => 'faveo_app_key',
            'app_key'    => 'key_'.uniqid(),
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
        $user   = User::factory()->create();
        $backup = UserBackupCodes::create([
            'user_id'      => $user->id,
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
        $user    = User::factory()->create();
        $attempt = VerificationAttempt::create([
            'user_id'        => $user->id,
            'mobile_attempt' => 0,
            'email_attempt'  => 0,
        ]);
        $this->assertInstanceOf(VerificationAttempt::class, $attempt);
        $this->assertDatabaseHas('verification_attempts', ['user_id' => $user->id]);
    }

    public function test_verification_attempt_email_can_be_set(): void
    {
        $user    = User::factory()->create();
        VerificationAttempt::create([
            'user_id'        => $user->id,
            'mobile_attempt' => 0,
            'email_attempt'  => 0,
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
        $user    = User::factory()->create();
        $renewal = Auto_renewal::create([
            'user_id'     => $user->id,
            'customer_id' => 'cus_test_'.uniqid(),
        ]);
        $this->assertInstanceOf(Auto_renewal::class, $renewal);
    }

    public function test_auto_renewal_user_relation_returns_user(): void
    {
        $user    = User::factory()->create();
        $renewal = Auto_renewal::create([
            'user_id'     => $user->id,
            'customer_id' => 'cus_'.uniqid(),
        ]);
        $this->assertEquals($user->id, $renewal->user->id);
    }

    // =========================================================================
    // Comment
    // =========================================================================

    public function test_comment_can_be_created(): void
    {
        $user    = User::factory()->create();
        $comment = Comment::create([
            'user_id'             => $user->id,
            'updated_by_user_id'  => $user->id,
            'description'         => 'Test comment',
        ]);
        $this->assertInstanceOf(Comment::class, $comment);
    }

    public function test_comment_user_relation_returns_user(): void
    {
        $user    = User::factory()->create();
        $comment = Comment::create([
            'user_id'             => $user->id,
            'updated_by_user_id'  => $user->id,
            'description'         => 'Relation test',
        ]);
        $this->assertEquals($user->id, $comment->user->id);
    }

    // =========================================================================
    // Payment_log
    // =========================================================================

    public function test_payment_log_can_be_created(): void
    {
        $log = Payment_log::create([
            'date'    => now(),
            'subject' => 'Payment received',
            'body'    => 'Invoice #1001 paid',
        ]);
        $this->assertInstanceOf(Payment_log::class, $log);
        $this->assertDatabaseHas('payment_logs', ['subject' => 'Payment received']);
    }
}
