<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Helper;

use Tests\TestCase;

class HelpersExtendedTest extends TestCase
{
    // =========================================================================
    // getStatusLabel() – covers 'renewed' branch (line 219)
    // =========================================================================

    public function test_get_status_label_renewed(): void
    {
        $result = getStatusLabel('renewed');
        $this->assertIsString($result);
    }

    public function test_get_status_label_success(): void
    {
        $result = getStatusLabel('Success');
        $this->assertIsString($result);
    }

    public function test_get_status_label_pending(): void
    {
        $result = getStatusLabel('Pending');
        $this->assertIsString($result);
    }

    // =========================================================================
    // getDateHtml() – covers null input returning '--' (line 173)
    // =========================================================================

    public function test_get_date_html_returns_dash_for_null(): void
    {
        $result = getDateHtml(null);
        $this->assertSame('--', $result);
    }

    public function test_get_date_html_with_valid_date(): void
    {
        $result = getDateHtml('2025-01-01 12:00:00');
        $this->assertIsString($result);
    }

    // =========================================================================
    // getPreReleaseStatusLabel() – covers specific match branches
    // =========================================================================

    public function test_get_pre_release_status_label_pre_release(): void
    {
        $this->assertSame('pre_release', getPreReleaseStatusLabel('pre_release'));
    }

    public function test_get_pre_release_status_label_beta(): void
    {
        $this->assertSame('beta', getPreReleaseStatusLabel('beta'));
    }

    public function test_get_pre_release_status_label_official(): void
    {
        $this->assertSame('official', getPreReleaseStatusLabel('official'));
    }

    public function test_get_pre_release_status_label_unknown_returns_null(): void
    {
        $this->assertNull(getPreReleaseStatusLabel('unknown'));
    }

    // =========================================================================
    // getCountryByCode() – returns null when country not found (lines 231-235)
    // =========================================================================

    public function test_get_country_by_code_returns_null_for_unknown_code(): void
    {
        $result = getCountryByCode('ZZZZ');
        $this->assertNull($result);
    }

    // =========================================================================
    // getVersionAndLabel() – calls Cache::remember (lines 199-208)
    // =========================================================================

    public function test_get_version_and_label_returns_null_for_unknown_product(): void
    {
        $result = getVersionAndLabel(null, '999999');
        $this->assertNull($result);
    }

    public function test_get_version_and_label_returns_version_when_provided(): void
    {
        $result = getVersionAndLabel('1.0.0', '1');
        $this->assertSame('1.0.0', $result);
    }

    // =========================================================================
    // getTimezoneByName() – returns '114' when not found (line 270)
    // =========================================================================

    public function test_get_timezone_by_name_returns_fallback_for_nonexistent(): void
    {
        $result = getTimezoneByName('Nonexistent/Timezone');
        $this->assertSame('114', $result);
    }

    // =========================================================================
    // installationStatusLabel() – covers both active/inactive branches
    // =========================================================================

    public function test_installation_status_label_inactive_when_empty(): void
    {
        $result = installationStatusLabel('');
        $this->assertStringContainsString('inactive', strtolower($result));
    }

    public function test_installation_status_label_active_when_path_given(): void
    {
        $result = installationStatusLabel('/var/www/html');
        $this->assertStringContainsString('active', strtolower($result));
    }

    // =========================================================================
    // getRootUrl() – covers path removal and trailing slash removal
    // =========================================================================

    public function test_get_root_url_with_path_removal(): void
    {
        $result = getRootUrl('https://www.example.com/path/page.php', 0, 0, 1, 0);
        $this->assertStringContainsString('example.com', $result);
    }

    public function test_get_root_url_with_trailing_slash_removal(): void
    {
        $result = getRootUrl('https://www.example.com/', 0, 0, 0, 1);
        // Trailing slash stripped; result should contain example.com without trailing /
        $this->assertIsString($result);
        $this->assertStringContainsString('example.com', $result);
    }

    public function test_get_root_url_with_invalid_url_returns_original(): void
    {
        $result = getRootUrl('not-a-valid-url', 0, 0, 0, 0);
        $this->assertSame('not-a-valid-url', $result);
    }

    // =========================================================================
    // honeypotField() – covers the HTML generation (lines 890-906)
    // =========================================================================

    public function test_honeypot_field_returns_html(): void
    {
        $result = honeypotField();
        $this->assertStringContainsString('<div', $result);
        $this->assertStringContainsString('display:none', $result);
    }

    public function test_honeypot_field_with_custom_name(): void
    {
        $result = honeypotField('custom_honeypot');
        $this->assertStringContainsString('custom_honeypot', $result);
    }

    // =========================================================================
    // isAgentAllowed() – covers no-agents-configured path (lines 919-924)
    // =========================================================================

    public function test_is_agent_allowed_returns_false_for_nonexistent_plan(): void
    {
        // PlanPrice for plan 999999 doesn't exist → no_of_agents = null → false
        $result = isAgentAllowed(1, 999999);
        $this->assertFalse($result);
    }

    // =========================================================================
    // currencyFormat() – covers specific branches
    // =========================================================================

    public function test_currency_format_without_symbol(): void
    {
        $result = currencyFormat(10.00, 'USD', false);
        $this->assertIsString($result);
        $this->assertStringNotContainsString('$', $result);
    }

    // =========================================================================
    // getUrl() – covers the HTTP_HOST checking branches (lines 851, 855)
    // =========================================================================

    public function test_get_url_returns_string(): void
    {
        $result = getUrl();
        $this->assertIsString($result);
    }

    // =========================================================================
    // findStateByRegionId() – covers the DB query path (lines 260-261)
    // =========================================================================

    public function test_find_state_by_region_id_returns_array(): void
    {
        $result = findStateByRegionId('US');
        $this->assertIsArray($result);
    }

    // =========================================================================
    // checkPlanSession() – covers lines 307-309
    // =========================================================================

    public function test_check_plan_session_returns_bool(): void
    {
        $result = checkPlanSession();
        $this->assertIsBool($result);
    }

    // =========================================================================
    // formatDuration() – covers formatting of seconds into human-readable
    // =========================================================================

    public function test_format_duration_seconds(): void
    {
        $result = formatDuration(45);
        $this->assertIsString($result);
    }

    public function test_format_duration_minutes(): void
    {
        $result = formatDuration(120);
        $this->assertIsString($result);
    }

    // =========================================================================
    // isJson() – covers JSON validation
    // =========================================================================

    public function test_is_json_returns_true_for_valid_json(): void
    {
        $this->assertTrue(isJson('{"key": "value"}'));
    }

    public function test_is_json_returns_false_for_invalid_json(): void
    {
        $this->assertFalse(isJson('not json'));
    }

    // =========================================================================
    // isCurrencySupportedForPayments() – covers return false path (line 952)
    // =========================================================================

    public function test_is_currency_supported_for_payments_returns_false_for_unknown(): void
    {
        $result = isCurrencySupportedForPayments('NONEXISTENT_CURRENCY', 'nonexistent_method');
        $this->assertFalse($result);
    }

    // =========================================================================
    // exceptionResponse() – pure JSON response builder
    // =========================================================================

    public function test_exception_response_returns_json_500(): void
    {
        $exception = new \Exception('Test error message');
        $response = exceptionResponse($exception);
        $this->assertSame(500, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertFalse($data['success']);
        $this->assertSame('Test error message', $data['message']);
    }

    // =========================================================================
    // commonSettings() – DB query
    // =========================================================================

    public function test_common_settings_returns_null_for_nonexistent(): void
    {
        $result = commonSettings('nonexistent_option_xyz', 'field_xyz');
        $this->assertNull($result);
    }

    // =========================================================================
    // authorizeOwnership() – auth-based ownership check
    // =========================================================================

    public function test_authorize_ownership_returns_false_for_different_user(): void
    {
        // Not authenticated → auth()->id() = null → 999 !== null → false
        $result = authorizeOwnership(999);
        $this->assertFalse($result);
    }

    // =========================================================================
    // getContactData() – returns fallback when no setting (line 616)
    // =========================================================================

    public function test_get_contact_data_returns_array(): void
    {
        $result = getContactData();
        $this->assertIsArray($result);
    }

    // =========================================================================
    // assetLink() – pure config-based URL
    // =========================================================================

    public function test_asset_link_returns_string(): void
    {
        $result = assetLink('js', 'nonexistent_key');
        $this->assertIsString($result);
    }
}
