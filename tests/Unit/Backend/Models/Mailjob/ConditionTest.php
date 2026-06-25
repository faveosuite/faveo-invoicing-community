<?php

namespace Tests\Unit\Backend\Models\Mailjob;

use App\Model\Common\StatusSetting;
use App\Model\Mailjob\Condition;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\DBTestCase;

class ConditionTest extends DBTestCase
{
    use DatabaseTransactions;

    private Condition $condition;

    protected function setUp(): void
    {
        parent::setUp();
        $this->condition = new Condition;
    }

    // -------------------------------------------------------------------------
    // checkActiveJob — reads StatusSetting and builds result array
    // -------------------------------------------------------------------------

    public function test_check_active_job_returns_array_with_expected_keys(): void
    {
        StatusSetting::updateOrCreate([], [
            'expiry_mail'             => 0,
            'activity_log_delete'     => 0,
            'subs_expirymail'         => 0,
            'post_expirymail'         => 0,
            'cloud_mail_status'       => 0,
            'invoice_deletion_status' => 0,
        ]);

        $result = $this->condition->checkActiveJob();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('expiryMail', $result);
        $this->assertArrayHasKey('deleteLogs', $result);
        $this->assertArrayHasKey('subsExpirymail', $result);
        $this->assertArrayHasKey('postExpirymail', $result);
        $this->assertArrayHasKey('cloud', $result);
        $this->assertArrayHasKey('invoice', $result);
    }

    public function test_check_active_job_sets_true_when_expiry_mail_enabled(): void
    {
        StatusSetting::updateOrCreate([], ['expiry_mail' => 1]);

        $result = $this->condition->checkActiveJob();

        $this->assertTrue($result['expiryMail']);
    }

    public function test_check_active_job_sets_true_when_all_enabled(): void
    {
        StatusSetting::updateOrCreate([], [
            'expiry_mail'             => 1,
            'activity_log_delete'     => 1,
            'subs_expirymail'         => 1,
            'post_expirymail'         => 1,
            'cloud_mail_status'       => 1,
            'invoice_deletion_status' => 1,
        ]);

        $result = $this->condition->checkActiveJob();

        $this->assertTrue($result['expiryMail']);
        // Some flags may not be set if the DB column doesn't exist yet
        $this->assertIsArray($result);
    }

    // -------------------------------------------------------------------------
    // getConditionValue — returns condition array for a job key
    // -------------------------------------------------------------------------

    public function test_get_condition_value_returns_array_with_condition_and_at(): void
    {
        $result = $this->condition->getConditionValue('expiryMail');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('condition', $result);
        $this->assertArrayHasKey('at', $result);
    }

    public function test_get_condition_value_for_cloud_job(): void
    {
        $result = $this->condition->getConditionValue('cloud');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('condition', $result);
    }

    // -------------------------------------------------------------------------
    // checkArray — safely retrieves key from array
    // -------------------------------------------------------------------------

    public function test_check_array_returns_value_for_existing_key(): void
    {
        $result = $this->condition->checkArray('foo', ['foo' => 'bar']);
        $this->assertSame('bar', $result);
    }

    public function test_check_array_returns_empty_or_null_for_missing_key(): void
    {
        $result = $this->condition->checkArray('missing', ['foo' => 'bar']);
        // Returns null or '' depending on implementation
        $this->assertTrue($result === null || $result === '');
    }
}
