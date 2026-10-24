<?php

namespace Tests\Unit\Backend\Models\Mailjob;

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
    // isActive / dayCount / dayList — a task's on/off and day settings
    // -------------------------------------------------------------------------

    public function test_is_active_reads_the_tasks_switch(): void
    {
        Condition::where('job', 'expiryMail')->update(['active' => true]);
        Condition::where('job', 'invoice')->update(['active' => false]);

        $this->assertTrue(Condition::isActive('expiryMail'));
        $this->assertFalse(Condition::isActive('invoice'));
        $this->assertFalse(Condition::isActive('no_such_job'));
    }

    public function test_day_count_returns_the_number_or_null(): void
    {
        Condition::where('job', 'invoice')->update(['days' => json_encode(7)]);
        Condition::where('job', 'msg91Reports')->update(['days' => json_encode(0)]);
        Condition::where('job', 'reoon')->update(['days' => null]);

        $this->assertSame(7, Condition::dayCount('invoice'));
        $this->assertSame(0, Condition::dayCount('msg91Reports'));
        $this->assertNull(Condition::dayCount('reoon'));
    }

    public function test_day_list_returns_integers_or_empty(): void
    {
        Condition::where('job', 'expiryMail')->update(['days' => json_encode(['30', '15', 7])]);
        Condition::where('job', 'subsExpirymail')->update(['days' => null]);

        $this->assertSame([30, 15, 7], Condition::dayList('expiryMail'));
        $this->assertSame([], Condition::dayList('subsExpirymail'));
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

    public function test_frequency_matches_per_job_lookup(): void
    {
        Condition::where('job', 'expiryMail')->update(['value' => 'dailyAt,08:30']);

        $task = Condition::where('job', 'expiryMail')->firstOrFail();

        $this->assertSame(['condition' => 'dailyAt', 'at' => '08:30'], $task->frequency());
        $this->assertSame($task->frequency(), $this->condition->getConditionValue('expiryMail'));
        $this->assertSame(['condition' => '', 'at' => ''], $this->condition->getConditionValue('no_such_job'));
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
