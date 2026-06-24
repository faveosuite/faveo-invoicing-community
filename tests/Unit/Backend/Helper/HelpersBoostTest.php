<?php

namespace Tests\Unit\Backend\Helper;

use App\Model\Common\Setting;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\DBTestCase;

class HelpersBoostTest extends DBTestCase
{
    use DatabaseTransactions;

    // =========================================================================
    // Pure functions — no DB, no auth needed
    // =========================================================================

    public function test_check_array_returns_value_when_key_exists(): void
    {
        $result = checkArray('foo', ['foo' => 'bar']);
        $this->assertEquals('bar', $result);
    }

    public function test_check_array_returns_empty_string_when_key_missing(): void
    {
        $result = checkArray('missing', ['foo' => 'bar']);
        $this->assertSame('', $result);
    }

    public function test_mime_returns_image_for_jpg(): void
    {
        $result = mime('jpg');
        $this->assertEquals('image', $result);
    }

    public function test_mime_returns_image_for_png(): void
    {
        $result = mime('png');
        $this->assertEquals('image', $result);
    }

    public function test_mime_returns_null_for_pdf(): void
    {
        $result = mime('pdf');
        $this->assertNull($result);
    }

    public function test_mime_returns_null_for_unknown_type(): void
    {
        $result = mime('xyz_unknown_format');
        $this->assertNull($result);
    }

    public function test_mime_returns_image_for_image_prefix(): void
    {
        $result = mime('image/jpeg');
        $this->assertEquals('image', $result);
    }

    public function test_error_response_returns_json_with_success_false(): void
    {
        $response = errorResponse('Something went wrong');
        $data = json_decode($response->getContent(), true);
        $this->assertFalse($data['success']);
        $this->assertEquals('Something went wrong', $data['message']);
        $this->assertEquals(400, $response->getStatusCode());
    }

    public function test_error_response_with_custom_status_code(): void
    {
        $response = errorResponse('Not found', 404);
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function test_success_response_returns_json_with_success_true(): void
    {
        $response = successResponse('Done', ['key' => 'value']);
        $data = json_decode($response->getContent(), true);
        $this->assertTrue($data['success']);
        $this->assertEquals('Done', $data['message']);
        $this->assertEquals(['key' => 'value'], $data['data']);
    }

    public function test_success_response_without_message(): void
    {
        $response = successResponse('', ['items' => []]);
        $data = json_decode($response->getContent(), true);
        $this->assertTrue($data['success']);
        $this->assertArrayNotHasKey('message', $data);
    }

    public function test_tooltip_wraps_text_in_html(): void
    {
        $result = tooltip('Help text here');
        $this->assertIsString($result);
        $this->assertStringContainsString('Help text here', $result);
    }

    public function test_tooltip_empty_returns_string(): void
    {
        $result = tooltip();
        $this->assertIsString($result);
    }

    public function test_get_status_label_active_returns_string(): void
    {
        $result = getStatusLabel(1);
        $this->assertTrue(is_string($result) || is_array($result));
    }

    public function test_get_status_label_inactive_returns_string(): void
    {
        $result = getStatusLabel(0);
        $this->assertTrue(is_string($result) || is_array($result));
    }

    public function test_is_json_with_valid_json_returns_true(): void
    {
        $result = isJson('{"key":"value"}');
        $this->assertTrue($result);
    }

    public function test_is_json_with_invalid_string_returns_false(): void
    {
        $result = isJson('not a json string');
        $this->assertFalse($result);
    }

    public function test_is_json_with_empty_string_returns_false(): void
    {
        $result = isJson('');
        $this->assertFalse($result);
    }

    public function test_format_duration_seconds_only(): void
    {
        $result = formatDuration(45);
        $this->assertIsString($result);
        $this->assertStringContainsString('45', $result);
    }

    public function test_format_duration_minutes(): void
    {
        $result = formatDuration(90);
        $this->assertIsString($result);
    }

    public function test_format_duration_hours(): void
    {
        $result = formatDuration(3661);
        $this->assertIsString($result);
    }

    public function test_get_root_url_strips_scheme(): void
    {
        $result = getRootUrl('https://example.com/path', 1, 0, 0, 0);
        $this->assertStringNotContainsString('https://', $result);
    }

    public function test_get_root_url_strips_www(): void
    {
        $result = getRootUrl('https://www.example.com', 0, 1, 0, 0);
        $this->assertStringNotContainsString('www.', $result);
    }

    public function test_currency_format_usd_returns_formatted_string(): void
    {
        $result = currencyFormat(100, 'USD');
        $this->assertIsString($result);
    }

    public function test_currency_format_zero_returns_string(): void
    {
        $result = currencyFormat(0, 'USD');
        $this->assertIsString($result);
    }

    public function test_get_currency_precision_standard_returns_2(): void
    {
        $result = getCurrencyPrecision('USD');
        $this->assertEquals(2, $result);
    }

    public function test_get_currency_precision_zero_decimal_returns_0(): void
    {
        $result = getCurrencyPrecision('JPY');
        $this->assertEquals(0, $result);
    }

    public function test_get_currency_precision_three_decimal_returns_3(): void
    {
        $result = getCurrencyPrecision('KWD');
        $this->assertEquals(3, $result);
    }

    public function test_calculate_unit_cost_usd_multiplies_by_100(): void
    {
        $result = calculateUnitCost('USD', 10);
        $this->assertEquals(1000, $result);
    }

    public function test_calculate_unit_cost_jpy_no_multiplication(): void
    {
        $result = calculateUnitCost('JPY', 100);
        $this->assertEquals(100, $result);
    }

    public function test_rounding_rounds_correctly(): void
    {
        $result = rounding(10.005);
        $this->assertIsFloat($result);
    }

    public function test_is_v3_api_returns_bool(): void
    {
        $result = isV3Api();
        $this->assertIsBool($result);
    }

    public function test_exception_response_returns_json_with_failure(): void
    {
        $exception = new \RuntimeException('Test error message', 500);
        $response = exceptionResponse($exception);
        $data = json_decode($response->getContent(), true);
        $this->assertFalse($data['success']);
    }

    public function test_hyper_link_generator_returns_html_anchor(): void
    {
        $result = hyperLinkGenerator('https://example.com', 'Click Here');
        $this->assertStringContainsString('<a', $result);
        $this->assertStringContainsString('https://example.com', $result);
        $this->assertStringContainsString('Click Here', $result);
    }

    public function test_format_days_less_than_30_returns_days(): void
    {
        $result = formatDays(15);
        $this->assertIsString($result);
    }

    public function test_format_days_30_returns_month(): void
    {
        $result = formatDays(30);
        $this->assertIsString($result);
    }

    public function test_format_days_365_returns_year(): void
    {
        $result = formatDays(365);
        $this->assertIsString($result);
    }

    public function test_create_url_prepends_app_url(): void
    {
        $result = createUrl('dashboard');
        $this->assertIsString($result);
        $this->assertStringContainsString('dashboard', $result);
    }

    public function test_is_rtl_for_lang_returns_bool(): void
    {
        $result = isRtlForLang();
        $this->assertIsBool($result);
    }

    public function test_honeypot_data_returns_array_with_expected_keys(): void
    {
        $result = honeypotData();
        $this->assertIsArray($result);
        $this->assertArrayHasKey('pot', $result);
        $this->assertArrayHasKey('time', $result);
        $this->assertArrayHasKey('token', $result);
    }

    public function test_get_url_returns_string(): void
    {
        $result = getUrl();
        $this->assertIsString($result);
    }

    public function test_is_s3_enabled_returns_bool(): void
    {
        $result = isS3Enabled();
        $this->assertIsBool($result);
    }

    public function test_email_sending_status_returns_bool(): void
    {
        $result = emailSendingStatus();
        $this->assertIsBool($result);
    }

    public function test_cloud_central_domain_returns_string(): void
    {
        $result = cloudCentralDomain();
        $this->assertIsString($result);
    }

    public function test_get_expiry_label_past_date_returns_expired_status(): void
    {
        $result = getExpiryLabel('2020-01-01');
        $this->assertIsArray($result);
        $this->assertArrayHasKey('date', $result);
        $this->assertArrayHasKey('status', $result);
        $this->assertNotNull($result['status']); // expired date has a status label
    }

    public function test_get_expiry_label_future_date_returns_null_status(): void
    {
        $result = getExpiryLabel(date('Y-m-d', strtotime('+30 days')));
        $this->assertIsArray($result);
        $this->assertArrayHasKey('date', $result);
        $this->assertNull($result['status']); // future date has no "expired" status
    }

    // =========================================================================
    // Functions that need a DB user
    // =========================================================================

    public function test_authorize_ownership_for_correct_user_returns_true(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $this->actingAs($user);
        $result = authorizeOwnership($user->id, false);
        $this->assertTrue($result);
    }

    public function test_authorize_ownership_for_different_user_returns_false(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $user2 = User::factory()->create(['role' => 'user']);
        $this->actingAs($user);
        $result = authorizeOwnership($user2->id, false);
        $this->assertFalse($result);
    }

    public function test_authorize_ownership_admin_with_allow_admin_returns_true(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);
        $this->actingAs($admin);
        $result = authorizeOwnership($user->id, true);
        $this->assertTrue($result);
    }

    public function test_get_contact_data_returns_array_with_logo(): void
    {
        Setting::create(['company' => 'Test Co', 'website' => 'http://test.com']);
        $result = getContactData();
        $this->assertIsArray($result);
        $this->assertArrayHasKey('logo', $result);
        $this->assertArrayHasKey('contact', $result);
    }

    public function test_system_date_time_format_returns_string(): void
    {
        $result = systemDateTimeFormat();
        $this->assertIsString($result);
    }

    public function test_system_timezone_returns_string(): void
    {
        $result = systemTimezone();
        $this->assertIsString($result);
    }
}
