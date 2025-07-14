<?php

namespace Tests\Unit\Common;

use App\ApiKey;
use App\Http\Controllers\Common\MSG91Controller;
use App\Model\Common\MsgDeliveryReports;
use App\ThirdPartyApp;
use App\User;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Mockery;
use Tests\DBTestCase;

class MSG91ControllerTest extends DBTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_validate_third_party_request_returns_false_for_invalid_credentials()
    {
        $controller = new Msg91Controller();

        $request = Request::create('/msg/reports', 'POST', [
            'app_key' => 'invalid_key',
            'app_secret' => 'invalid_secret',
        ]);

        $this->assertFalse($controller->validateThirdPartyRequest($request));
    }

    public function test_validate_third_party_request_returns_true_when_app_and_api_key_exist()
    {
        $app = ThirdPartyApp::create([
            'app_name' => 'testkey',
            'app_key' => 'key123',
            'app_secret' => 'secret123',
        ]);

        ApiKey::first()->update([
            'msg91_third_party_id' => $app->id,
        ]);

        $controller = new Msg91Controller();

        $request = Request::create('/msg/reports', 'POST', [
            'app_key' => 'key123',
            'app_secret' => 'secret123',
        ]);

        $this->assertTrue($controller->validateThirdPartyRequest($request));
    }

    public function test_update_otp_request_creates_or_updates_record()
    {
        User::factory()->create(['id' => 42]);

        $controller = new Msg91Controller();

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

    public function test_handle_reports_skips_if_validation_fails()
    {
        // Spy on DB transaction to ensure it's never called
        DB::shouldReceive('transaction')->never();

        $controller = Mockery::mock(Msg91Controller::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        // Force validateThirdPartyRequest to return false
        $controller->shouldReceive('validateThirdPartyRequest')->andReturn(false);

        $request = Request::create('/msg/reports', 'POST', [
            'data' => json_encode([]),
            'app_key' => 'x',
            'app_secret' => 'y',
        ]);

        $controller->handleReports($request);
    }

    public function test_handle_reports_processes_each_report_and_calls_processIndividualReport()
    {
        $app = ThirdPartyApp::create([
            'app_key' => 'k',
            'app_secret' => 's',
        ]);
        ApiKey::create([
            'msg91_third_party_id' => $app->id,
        ]);

        $controller = Mockery::mock(Msg91Controller::class)
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
        $controller->shouldReceive('processIndividualReport')
            ->once()
            ->with(Mockery::on(function ($arg) {
                return $arg['request_id'] === 'r1'
                    && $arg['number'] === '555'
                    && $arg['status'] === 'DELIVRD'
                    && $arg['date'] === '2025-04-10 10:00:00';
            }));

        $request = Request::create('/msg/reports', 'POST', [
            'app_key' => 'k',
            'app_secret' => 's',
            'data' => json_encode($reportsPayload),
        ]);

        $controller->handleReports($request);
    }

    public function test_msg91_report_query_filters_correctly()
    {
        $user = User::create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
        ]);

        MsgDeliveryReports::create([
            'request_id' => 'foo123',
            'mobile_number' => '999',
            'country_iso' => 'US',
            'failure_reason' => 'none',
            'status' => 1,
            'date' => Carbon::now()->subDay(),
            'user_id' => $user->id,
        ]);

        $controller = new Msg91Controller();

        $request = Request::create('/reports', 'GET', [
            'request_id' => 'foo',
            'mobile_number' => '999',
            'country_iso' => 'US',
            'failure_reason' => 'none',
            'status' => 'Delivered',
            'date_from' => Carbon::now()->subDays(2)->format('m/d/Y'),
            'date_to' => Carbon::now()->format('m/d/Y'),
            'email' => 'john@',
        ]);

        $query = $controller->msg91ReportQuery($request);

        $results = $query->get();

        $this->assertCount(1, $results);
        $this->assertEquals('foo123', $results->first()->request_id);
    }
}
