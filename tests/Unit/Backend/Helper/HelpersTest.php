<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Helper;

use Exception;
use Tests\DBTestCase;

class HelpersTest extends DBTestCase
{
    // =========================================================================
    // errorResponse()
    // =========================================================================

    public function test_error_response_returns_400_by_default(): void
    {
        $response = errorResponse('Something went wrong');

        $this->assertSame(400, $response->getStatusCode());
    }

    public function test_error_response_contains_success_false(): void
    {
        $response = errorResponse('Oops');
        $json = $response->getData(true);

        $this->assertFalse($json['success']);
    }

    public function test_error_response_contains_message_key(): void
    {
        $response = errorResponse('Something went wrong');
        $json = $response->getData(true);

        $this->assertArrayHasKey('message', $json);
        $this->assertSame('Something went wrong', $json['message']);
    }

    public function test_error_response_accepts_custom_status_code(): void
    {
        $response = errorResponse('Not Found', 404);

        $this->assertSame(404, $response->getStatusCode());
    }

    public function test_error_response_accepts_array_message(): void
    {
        $response = errorResponse(['field' => 'The field is required', 'other' => 'Error']);
        $json = $response->getData(true);

        $this->assertIsArray($json['message']);
        $this->assertArrayHasKey('field', $json['message']);
    }

    public function test_error_response_accepts_nested_field_errors(): void
    {
        $errors = ['email' => ['Email is required', 'Email must be valid'], 'name' => ['Name is required']];
        $response = errorResponse($errors, 422);
        $json = $response->getData(true);

        $this->assertSame(422, $response->getStatusCode());
        $this->assertIsArray($json['message']);
        $this->assertArrayHasKey('email', $json['message']);
        $this->assertCount(2, $json['message']['email']);
    }

    // =========================================================================
    // successResponse()
    // =========================================================================

    public function test_success_response_returns_200_by_default(): void
    {
        $response = successResponse();

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_success_response_contains_success_true(): void
    {
        $response = successResponse();
        $json = $response->getData(true);

        $this->assertTrue($json['success']);
    }

    public function test_success_response_includes_message_when_provided(): void
    {
        $response = successResponse('Created successfully');
        $json = $response->getData(true);

        $this->assertArrayHasKey('message', $json);
        $this->assertSame('Created successfully', $json['message']);
    }

    public function test_success_response_omits_message_key_when_empty(): void
    {
        $response = successResponse();
        $json = $response->getData(true);

        $this->assertArrayNotHasKey('message', $json);
    }

    public function test_success_response_includes_data_key_when_array_provided(): void
    {
        $response = successResponse('', ['id' => 1, 'name' => 'Test']);
        $json = $response->getData(true);

        $this->assertArrayHasKey('data', $json);
        $this->assertSame(1, $json['data']['id']);
    }

    public function test_success_response_omits_data_key_when_empty_string(): void
    {
        $response = successResponse('ok', '');
        $json = $response->getData(true);

        $this->assertArrayNotHasKey('data', $json);
    }

    public function test_success_response_omits_data_key_when_null(): void
    {
        // empty(null) === true in PHP, so null data is not added
        $response = successResponse('ok', null);
        $json = $response->getData(true);

        $this->assertArrayNotHasKey('data', $json);
    }

    public function test_success_response_accepts_nested_array_data(): void
    {
        $data = ['items' => [['id' => 1], ['id' => 2]], 'total' => 2];
        $response = successResponse('', $data);
        $json = $response->getData(true);

        $this->assertSame(2, $json['data']['total']);
        $this->assertCount(2, $json['data']['items']);
    }

    public function test_success_response_accepts_custom_status_code(): void
    {
        $response = successResponse('Created', ['id' => 1], 201);

        $this->assertSame(201, $response->getStatusCode());
    }

    public function test_success_response_with_unicode_message(): void
    {
        $response = successResponse('Créé avec succès');
        $json = $response->getData(true);

        $this->assertSame('Créé avec succès', $json['message']);
    }

    // =========================================================================
    // exceptionResponse()
    // =========================================================================

    public function test_exception_response_returns_500(): void
    {
        $e = new Exception('Boom');
        $response = exceptionResponse($e);

        $this->assertSame(500, $response->getStatusCode());
    }

    public function test_exception_response_has_required_keys(): void
    {
        $e = new Exception('Test error');
        $json = exceptionResponse($e)->getData(true);

        $this->assertFalse($json['success']);
        $this->assertSame('Test error', $json['message']);
        $this->assertArrayHasKey('file', $json);
        $this->assertArrayHasKey('trace', $json);
    }

    // =========================================================================
    // checkArray()
    // =========================================================================

    public function test_check_array_returns_value_when_key_exists(): void
    {
        $this->assertSame('bar', checkArray('foo', ['foo' => 'bar']));
    }

    public function test_check_array_returns_empty_string_when_key_missing(): void
    {
        $this->assertSame('', checkArray('missing', ['foo' => 'bar']));
    }

    public function test_check_array_returns_null_value_correctly(): void
    {
        $this->assertNull(checkArray('key', ['key' => null]));
    }

    // =========================================================================
    // mime()
    // =========================================================================

    public function test_mime_returns_image_for_jpg(): void
    {
        $this->assertSame('image', mime('jpg'));
    }

    public function test_mime_returns_image_for_png(): void
    {
        $this->assertSame('image', mime('png'));
    }

    public function test_mime_returns_image_for_gif(): void
    {
        $this->assertSame('image', mime('gif'));
    }

    public function test_mime_returns_image_for_image_prefix(): void
    {
        $this->assertSame('image', mime('image/jpeg'));
    }

    public function test_mime_returns_null_for_pdf(): void
    {
        $this->assertNull(mime('pdf'));
    }

    public function test_mime_returns_null_for_zip(): void
    {
        $this->assertNull(mime('zip'));
    }

    // =========================================================================
    // isJson()
    // =========================================================================

    public function test_is_json_returns_true_for_valid_object(): void
    {
        $this->assertTrue(isJson('{"key":"value"}'));
    }

    public function test_is_json_returns_true_for_valid_array(): void
    {
        $this->assertTrue(isJson('[1,2,3]'));
    }

    public function test_is_json_returns_true_for_null_literal(): void
    {
        $this->assertTrue(isJson('null'));
    }

    public function test_is_json_returns_false_for_plain_string(): void
    {
        $this->assertFalse(isJson('not-json'));
    }

    public function test_is_json_returns_false_for_unclosed_brace(): void
    {
        $this->assertFalse(isJson('{"key":'));
    }

    // =========================================================================
    // calculateUnitCost()
    // =========================================================================

    public function test_calculate_unit_cost_two_decimal_currency(): void
    {
        // USD: 2 decimal places → cost * 100
        $this->assertSame(4999, calculateUnitCost('USD', 49.99));
    }

    public function test_calculate_unit_cost_zero_decimal_currency(): void
    {
        // JPY: 0 decimal places → round(cost) as int
        $this->assertSame(5000, calculateUnitCost('JPY', 5000.49));
    }

    public function test_calculate_unit_cost_three_decimal_currency(): void
    {
        // KWD: 3 decimal places → cost * 1000
        $this->assertSame(4999, calculateUnitCost('KWD', 4.999));
    }

    public function test_calculate_unit_cost_unknown_currency_defaults_two_decimals(): void
    {
        // Unlisted currency defaults to 2 decimal places
        $this->assertSame(1000, calculateUnitCost('XYZ', 10.0));
    }

    // =========================================================================
    // formatDays()
    // =========================================================================

    public function test_format_days_returns_null_for_zero(): void
    {
        $this->assertNull(formatDays(0));
    }

    public function test_format_days_returns_null_for_negative(): void
    {
        $this->assertNull(formatDays(-5));
    }

    public function test_format_days_returns_days_label_for_less_than_30(): void
    {
        $this->assertSame('7 Days', formatDays(7));
        $this->assertSame('1 Days', formatDays(1));
        $this->assertSame('29 Days', formatDays(29));
    }

    public function test_format_days_returns_month_label(): void
    {
        $this->assertSame('1 Month', formatDays(30));
        $this->assertSame('2 Months', formatDays(60));
        $this->assertSame('12 Months', formatDays(364));
    }

    public function test_format_days_returns_year_label(): void
    {
        $this->assertSame('1 Year', formatDays(365));
        $this->assertSame('2 Years', formatDays(730));
    }

    // =========================================================================
    // isRtlForLang()
    // =========================================================================

    public function test_is_rtl_returns_false_for_english_locale(): void
    {
        app()->setLocale('en');

        $this->assertFalse(isRtlForLang());
    }

    public function test_is_rtl_returns_true_for_arabic_locale(): void
    {
        app()->setLocale('ar');

        $this->assertTrue(isRtlForLang());
        app()->setLocale('en'); // restore
    }

    // =========================================================================
    // authorizeOwnership()
    // =========================================================================

    public function test_authorize_ownership_returns_true_when_ids_match(): void
    {
        $this->getLoggedInUser('user');

        $this->assertTrue(authorizeOwnership($this->user->id));
    }

    public function test_authorize_ownership_returns_false_when_ids_differ(): void
    {
        $this->getLoggedInUser('user');

        $this->assertFalse(authorizeOwnership($this->user->id + 9999));
    }

    public function test_authorize_ownership_allows_admin_when_flag_is_true(): void
    {
        $this->getLoggedInUser('admin');

        $this->assertTrue(authorizeOwnership(99999, allowAdmin: true));
    }

    public function test_authorize_ownership_blocks_admin_when_flag_is_false(): void
    {
        $this->getLoggedInUser('admin');

        $this->assertFalse(authorizeOwnership(99999, allowAdmin: false));
    }

    // =========================================================================
    // formatDuration()
    // =========================================================================

    public function test_format_duration_returns_string(): void
    {
        $result = formatDuration(90);

        $this->assertIsString($result);
        $this->assertNotEmpty($result);
    }

    public function test_format_duration_zero_seconds(): void
    {
        $result = formatDuration(0);

        $this->assertIsString($result);
    }
}
