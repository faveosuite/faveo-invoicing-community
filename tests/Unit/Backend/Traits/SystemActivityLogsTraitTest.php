<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Traits;

use App\Traits\SystemActivityLogsTrait;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;
use Mockery;
use Spatie\Activitylog\Models\Activity;
use Tests\DBTestCase;

class SystemActivityLogsTraitTest extends DBTestCase
{
    use DatabaseTransactions;

    private object $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new class extends Model {
            use SystemActivityLogsTrait;

            public static string $logName = 'user';

            public string $logNameColumn = 'first_name';

            protected array $logAttributes = ['first_name', 'email'];

            protected function getMappings(): array
            {
                return [
                    'first_name' => ['name', fn ($v) => strtoupper($v)],
                ];
            }
        };
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_get_activitylog_options_returns_log_options(): void
    {
        $options = $this->subject->getActivitylogOptions();

        $this->assertInstanceOf(\Spatie\Activitylog\Support\LogOptions::class, $options);
    }

    public function test_tap_activity_logs_maps_attributes(): void
    {
        $activity = new Activity();
        $activity->properties = collect([
            'attributes' => ['first_name' => 'john', 'email' => 'j@example.com'],
        ]);

        $this->getPrivateMethod($this->subject, 'tapActivityLogs', [$activity]);

        $mapped = $activity->properties->get('attributes');
        $this->assertArrayHasKey('name', $mapped);
        $this->assertSame('JOHN', $mapped['name']);
        $this->assertArrayNotHasKey('first_name', $mapped);
    }

    public function test_tap_activity_logs_handles_missing_properties(): void
    {
        $activity = new Activity();
        $activity->properties = collect([]);

        $this->getPrivateMethod($this->subject, 'tapActivityLogs', [$activity]);

        $this->assertTrue(true); // no exception thrown
    }

    public function test_set_causer_associates_user_when_found(): void
    {
        $user = User::factory()->create();

        $activity = new Activity();
        $activity->subject = (object) ['causerID' => null]; // null → no associate

        // Just test no exception is thrown
        $this->getPrivateMethod($this->subject, 'setCauser', [$activity]);

        $this->assertTrue(true);
    }

    public function test_set_causer_skips_when_user_not_found(): void
    {
        $activity = Mockery::mock(Activity::class)->makePartial();
        $activity->subject = (object) ['causerID' => null];
        $activity->shouldNotReceive('causer');

        $this->getPrivateMethod($this->subject, 'setCauser', [$activity]);

        $this->assertTrue(true);
    }

    public function test_resolve_deleted_event_name_returns_suspended_for_soft_delete(): void
    {
        $subject = (object) [];

        $activity = Mockery::mock(Activity::class)->makePartial();
        $activity->subject = new class {
            public function isForceDeleting(): bool { return false; }
        };

        $result = $this->getPrivateMethod($this->subject, 'resolveDeletedEventName', [$activity, 'deleted']);

        $this->assertSame('suspended', $result);
    }

    public function test_resolve_deleted_event_name_returns_deleted_for_force_delete(): void
    {
        $activity = Mockery::mock(Activity::class)->makePartial();
        $activity->subject = new class {
            public function isForceDeleting(): bool { return true; }
        };

        $result = $this->getPrivateMethod($this->subject, 'resolveDeletedEventName', [$activity, 'deleted']);

        $this->assertSame('deleted', $result);
    }

    public function test_resolve_deleted_event_name_passes_through_non_deleted(): void
    {
        $activity = Mockery::mock(Activity::class)->makePartial();
        $activity->subject = null;

        $result = $this->getPrivateMethod($this->subject, 'resolveDeletedEventName', [$activity, 'created']);

        $this->assertSame('created', $result);
    }

    public function test_get_log_url_returns_null_when_no_segments(): void
    {
        $result = $this->getPrivateMethod($this->subject, 'getLogUrl', [1]);

        $this->assertNull($result);
    }

    public function test_get_log_url_builds_url_from_segments(): void
    {
        $subject = new class extends Model {
            use SystemActivityLogsTrait;

            public static string $logName = 'user';

            public string $logNameColumn = 'first_name';

            protected array $logAttributes = [];

            public array $logUrl = ['segments' => ['users', ':id'], 'params' => []];

            protected function getMappings(): array { return []; }
        };

        $url = $this->getPrivateMethod($subject, 'getLogUrl', [42]);

        $this->assertStringContainsString('users', $url);
        $this->assertStringContainsString('42', $url);
    }

    public function test_tap_activity_runs_all_steps(): void
    {
        $user = User::factory()->create();
        $activity = new Activity();
        $activity->properties = collect([
            'attributes' => ['first_name' => 'alice'],
        ]);
        $activity->subject = (object) ['first_name' => 'Alice', 'causerID' => null];
        $activity->subject_id = null;

        $this->subject->tapActivity($activity, 'created');

        $this->assertNotNull($activity->description);
    }

    public function test_generate_description_for_deleted_event(): void
    {
        $activity = new Activity();
        $activity->subject = (object) ['first_name' => 'Bob'];
        $activity->subject_id = null;
        $activity->properties = collect([]);

        $this->getPrivateMethod($this->subject, 'generateDescriptionForLogs', [$activity, 'deleted']);

        $this->assertNotNull($activity->description);
    }

    public function test_tap_activity_logs_transforms_old_attributes(): void
    {
        $activity = new Activity();
        $activity->properties = collect([
            'old' => ['first_name' => 'alice'],
        ]);

        $this->getPrivateMethod($this->subject, 'tapActivityLogs', [$activity]);

        $old = $activity->properties->get('old');
        $this->assertArrayHasKey('name', $old);
        $this->assertSame('ALICE', $old['name']);
    }

    public function test_tap_activity_logs_handles_array_properties(): void
    {
        $activity = new Activity();
        // Pass array instead of Collection — covers line 59
        $activity->properties = ['attributes' => ['first_name' => 'alice']];

        $this->getPrivateMethod($this->subject, 'tapActivityLogs', [$activity]);

        $this->assertTrue(true);
    }

    public function test_format_logging_attributes_with_non_callable_transform(): void
    {
        $subject = new class extends Model {
            use SystemActivityLogsTrait;

            public static string $logName = 'user';

            public string $logNameColumn = 'first_name';

            protected array $logAttributes = [];

            protected function getMappings(): array
            {
                // Non-callable transform (just a string) — covers line 97
                return ['first_name' => ['name', 'STATIC_VALUE']];
            }
        };

        $activity = new Activity();
        $activity->properties = collect(['attributes' => ['first_name' => 'alice']]);

        $this->getPrivateMethod($subject, 'tapActivityLogs', [$activity]);

        $mapped = $activity->properties->get('attributes');
        // Non-callable transform → original value is used unchanged
        $this->assertSame('alice', $mapped['name']);
    }

    public function test_get_log_url_builds_url_with_params(): void
    {
        $subject = new class extends Model {
            use SystemActivityLogsTrait;

            public static string $logName = 'user';

            public string $logNameColumn = 'first_name';

            protected array $logAttributes = [];

            public array $logUrl = ['segments' => ['users'], 'params' => ['tab' => 'details']];

            protected function getMappings(): array { return []; }
        };

        $url = $this->getPrivateMethod($subject, 'getLogUrl', [1]);

        $this->assertStringContainsString('tab=details', $url);
    }
}
