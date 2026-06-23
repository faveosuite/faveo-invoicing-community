<?php

namespace Tests\Unit\Backend\Http\Controllers\Common\Sms;

use App\ApiKey;
use App\Http\Controllers\Common\Sms\MSG91Controller;
use App\Model\Common\Msg91Status;
use App\Model\Common\MsgDeliveryReports;
use App\ThirdPartyApp;
use App\User;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Str;
use Mockery;
use Tests\DBTestCase;

class MSG91ControllerTest extends DBTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
    }

    public function test_validate_third_party_request_returns_false_for_invalid_credentials(): void
    {
        $controller = new MSG91Controller;

        $this->assertFalse($controller->validateThirdPartyRequest('invalid', 'invalid'));
    }

    public function test_validate_third_party_request_returns_true_when_app_and_api_key_exist(): void
    {
        $app = ThirdPartyApp::create([
            'app_name' => 'testkey',
            'app_key' => 'key123',
            'app_secret' => 'secret123',
        ]);

        ApiKey::first()->update([
            'msg91_third_party_id' => $app->id,
        ]);

        $controller = new MSG91Controller;

        $this->assertTrue($controller->validateThirdPartyRequest('key123', 'secret123'));
    }

    public function test_update_otp_request_creates_or_updates_record(): void
    {
        User::factory()->create(['id' => 42]);

        $controller = new MSG91Controller;

        // First call: create
        $controller->updateOtpRequest(
            'req1',
            200,
            'US',
            '5551234',
            '+1',
            42
        );

        $this->assertDatabaseHas('msg_delivery_reports', [
            'request_id' => 'req1',
            'status' => 200,
            'country_iso' => 'US',
            'mobile_number' => '5551234',
            'mobile_code' => '+1',
            'user_id' => 42,
        ]);

        // Second call: update
        $controller->updateOtpRequest(
            'req1',
            500,
            'US',
            '5551234',
            '+1',
            42
        );

        $this->assertDatabaseHas('msg_delivery_reports', [
            'request_id' => 'req1',
            'status' => 500,
        ]);
    }

    public function test_handle_reports_skips_if_validation_fails(): void
    {
        // Spy on DB transaction to ensure it's never called
        DB::shouldReceive('transaction')->never();

        $controller = Mockery::mock(MSG91Controller::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        // Force validateThirdPartyRequest to return false
        $controller->shouldReceive('validateThirdPartyRequest')->andReturn(false);

        $request = Request::create('/msg/reports/x/y', 'POST', [
            'data' => json_encode([]),
        ]);

        $controller->handleReports($request, 'x', 'y');
    }

    public function test_handle_reports_processes_each_report_and_calls_process_individual_report(): void
    {
        $app = ThirdPartyApp::create([
            'app_key' => 'k',
            'app_secret' => 's',
        ]);
        ApiKey::create([
            'msg91_third_party_id' => $app->id,
        ]);

        $controller = Mockery::mock(MSG91Controller::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $controller->shouldReceive('validateThirdPartyRequest')->andReturn(true);

        $reportsPayload = [
            [
                'requestId' => 'r1',
                'report' => [
                    [
                        'number' => '555',
                        'status' => 'DELIVRD',
                        'date' => '2025-04-10 10:00:00',
                        'failedReason' => null,
                    ],
                ],
            ],
        ];

        $controller->shouldAllowMockingProtectedMethods();
        $expectedUtcDate = Date::parse('2025-04-10 10:00:00', 'Asia/Kolkata')
            ->timezone('UTC')
            ->toDateTimeString();
        $controller->shouldReceive('processIndividualReport')
            ->once()
            ->with(Mockery::on(fn ($arg): bool => $arg['request_id'] === 'r1'
                && $arg['number'] === '555'
                && $arg['status'] === 'DELIVRD'
                && $arg['date'] === $expectedUtcDate));

        $request = Request::create('/msg/reports/k/s', 'POST', [
            'data' => json_encode($reportsPayload),
        ]);

        $controller->handleReports($request, 'k', 's');
    }

    //    public function test_msg91_report_query_filters_correctly()
    //    {
    //        $user = User::create([
    //            'first_name' => 'John',
    //            'last_name' => 'Doe',
    //            'email' => 'john@example.com',
    //        ]);
    //
    //        MsgDeliveryReports::create([
    //            'request_id' => 'foo123',
    //            'mobile_number' => '999',
    //            'country_iso' => 'US',
    //            'failure_reason' => 'none',
    //            'status' => 1,
    //            'date' => Carbon::now()->subDay(),
    //            'user_id' => $user->id,
    //        ]);
    //
    //        $controller = new Msg91Controller();
    //
    //        $request = Request::create('/reports', 'GET', [
    //            'request_id' => 'foo',
    //            'mobile_number' => '999',
    //            'country_iso' => 'US',
    //            'failure_reason' => 'none',
    //            'status' => 'Delivered',
    //            'date_from' => Carbon::now()->subDays(2)->format('m/d/Y'),
    //            'date_to' => Carbon::now()->format('m/d/Y'),
    //            'email' => 'john@',
    //        ]);
    //
    //        $query = $controller->msg91ReportQuery($request);
    //
    //        $results = $query->get();
    //
    //        $this->assertCount(1, $results);
    //        $this->assertEquals('foo123', $results->first()->request_id);
    //    }

    // New test cases can be added here
    protected function createMsg91Log(array $overrides = [])
    {
        $user = User::factory()->create();

        // Ensure status exists (no duplicate violation)
        $status = Msg91Status::firstOrCreate(
            ['status_code' => 'DEL'],
            ['status_label' => 'Delivered']
        );

        $defaults = [
            'mobile_number' => '9876543210',
            'request_id' => Str::uuid(),
            'status' => $status->status_code,
            'date' => now(),
            'sender_id' => 'SENDER',
            'failure_reason' => null,
            'user_id' => $user->id,
            'country_iso' => 'IN',
            'mobile_code' => '+91',
        ];

        return MsgDeliveryReports::create(array_merge($defaults, $overrides));
    }

    public function test_get_msg91_logs_returns_data(): void
    {
        $this->createMsg91Log();
        $this->createMsg91Log();

        $response = $this->getJson('/getMsgReports');

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => __('message.msg91_reports_fetched')])
            ->assertJsonCount(2, 'data.data');
    }

    public function test_get_msg91_logs_search_filter(): void
    {
        $user = User::factory()->create(['first_name' => 'Test', 'last_name' => 'User']);
        $this->createMsg91Log([
            'request_id' => 'REQ-123',
            'user_id' => $user->id,
        ]);

        // Search by request_id
        $response1 = $this->getJson('/getMsgReports?search-query=REQ-123');
        $response1->assertStatus(200)
            ->assertJsonCount(1, 'data.data');

        // Search by user full name
        $response2 = $this->getJson('/getMsgReports?search-query=Test User');
        $response2->assertStatus(200)
            ->assertJsonCount(1, 'data.data');

        // Search by email
        $response3 = $this->getJson('/getMsgReports?search-query='.$user->email);
        $response3->assertStatus(200)
            ->assertJsonCount(1, 'data.data');

        // Search by status
        $response4 = $this->getJson('/getMsgReports?search-query=pending');
        $response4->assertStatus(200)
            ->assertJsonCount(1, 'data.data');
    }

    public function test_msg91_filter_by_request_id(): void
    {
        // filter by request_id
        $this->createMsg91Log(['request_id' => 'REQ12345']);
        $this->createMsg91Log();

        $response = $this->getJson('/getMsgReports?request_id=REQ12345');

        $response->assertStatus(200)
            ->assertJsonFragment(['request_id' => 'REQ12345'])
            ->assertJsonCount(1, 'data.data');
    }

    public function test_msg91_filter_by_full_name(): void
    {
        // filter by full_name
        $user = User::factory()->create(['first_name' => 'John', 'last_name' => 'Doe']);

        $this->createMsg91Log(['user_id' => $user->id]);
        $this->createMsg91Log();

        $response = $this->getJson('/getMsgReports?full_name=John Doe');

        $response->assertStatus(200)
            ->assertJsonFragment(['user_fullname' => 'John Doe'])
            ->assertJsonCount(1, 'data.data');
    }

    public function test_msg91_filter_by_email(): void
    {
        // filter by email
        $user = User::factory()->create(['email' => 'john@example.com']);
        $this->createMsg91Log(['user_id' => $user->id]);

        $response = $this->getJson('/getMsgReports?email=john@example.com');

        $response->assertStatus(200)
            ->assertJsonFragment(['user_email' => 'john@example.com']);
    }

    public function test_msg91_filter_by_mobile_and_country(): void
    {
        // filter by mobile_number and country_iso
        $this->createMsg91Log(['mobile_number' => '7894561230', 'country_iso' => 'US']);
        $this->createMsg91Log(['mobile_number' => '9999999999', 'country_iso' => 'IN']); // excluded

        $response = $this->getJson('/getMsgReports?mobile_number=789456&country_iso=US');

        $response->assertStatus(200)
            ->assertJsonFragment(['mobile_number' => '7894561230'])
            ->assertJsonCount(1, 'data.data');
    }

    public function test_msg91_filter_by_failure_reason(): void
    {
        // filter by failure_reason
        $this->createMsg91Log(['failure_reason' => 'Route not found']);
        $this->createMsg91Log();

        $response = $this->getJson('/getMsgReports?failure_reason=Route');

        $response->assertStatus(200)
            ->assertJsonFragment(['failure_reason' => 'Route not found']);
    }

    public function test_msg91_filter_by_single_date_range(): void
    {
        // filter by single date range (from and till are same)
        $this->createMsg91Log(['created_at' => now()->subDay()]);
        $this->createMsg91Log(['created_at' => now()->subDays(10)]);
        $log1 = $this->createMsg91Log();
        $log1->forceFill([
            'created_at' => Date::create(2025, 7, 12)->startOfDay(),
        ])->saveQuietly();
        $response = $this->getJson('/getMsgReports?date_from=2025-07-12&date_to=2025-07-12');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.data');
    }

    public function test_msg91_filter_by_multiple_date_range(): void
    {
        // filter by multiple date range (from and till are different)
        $log1 = $this->createMsg91Log();
        $log1->forceFill([
            'created_at' => Date::create(2025, 7, 12)->startOfDay(),
        ])->saveQuietly();
        $log2 = $this->createMsg91Log();
        $log2->forceFill([
            'created_at' => Date::create(2025, 9, 12)->startOfDay(),
        ])->saveQuietly();
        $log3 = $this->createMsg91Log();
        $log3->forceFill([
            'created_at' => Date::create(2025, 10, 12)->startOfDay(),
        ])->saveQuietly();

        $response = $this->getJson('/getMsgReports?date_from=2025-07-12&date_to=2025-10-12');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data.data');
    }

    public function test_msg91_filter_by_without_give_till_date(): void
    {
        // filter by date range without giving till date
        $this->createMsg91Log();
        $this->createMsg91Log();
        $log1 = $this->createMsg91Log();

        $log1->forceFill([
            'created_at' => Date::create(2025, 7, 12)->startOfDay(),
        ])->saveQuietly();

        $response = $this->getJson('/getMsgReports?date_from=2025-07-12');
        $response->assertStatus(200)
            ->assertJsonCount(3, 'data.data');
    }

    public function test_msg91_filters_all_conditions_together(): void
    {
        // filter by all conditions together
        $user = User::factory()->create(['first_name' => 'John', 'last_name' => 'Doe']);

        $this->createMsg91Log([
            'user_id' => $user->id,
            'mobile_number' => '9876540000',
            'status' => 'DEL',
            'failure_reason' => 'None',
            'created_at' => now()->subDay(),
        ]);

        $this->createMsg91Log([
            'user_id' => $user->id,
            'mobile_number' => '9876540000',
            'status' => 'DEL',
            'failure_reason' => 'None',
            'created_at' => now()->subDay(),
        ]);

        $this->createMsg91Log();

        $qs = http_build_query([
            'email' => $user->email,
            'mobile_number' => '987654',
            'status' => 'Pending',
            'date_from' => now()->subDays(2)->toDateString(),
            'date_to' => now()->toDateString(),
        ]);

        $response = $this->getJson('/getMsgReports?'.$qs);

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data.data');
    }
}
