<?php

namespace Tests\Unit\Backend\Plugins\Recaptcha;

use App\Plugins\Recaptcha\Requests\UpdateSettingsRequest;
use App\Plugins\Recaptcha\Services\RecaptchaVerifier;
use Illuminate\Support\Facades\Validator;
use Mockery;
use Tests\TestCase;

/**
 * UpdateSettingsRequest::withValidator(): the keys being saved must pass a live reCAPTCHA
 * check. The verifier is mocked, so nothing calls Google.
 */
class UpdateSettingsRequestTest extends TestCase
{
    /** @var list<array{0: string, 1: string, 2: string}> verify() calls: [response, secret, type] */
    private array $calls = [];

    /**
     * Runs only the after-validation hook for $data, with verify() answering $answers[type].
     *
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $answers
     * @return array<string, list<string>>
     */
    private function afterErrors(array $data, array $answers = []): array
    {
        $verifier = Mockery::mock(RecaptchaVerifier::class);
        $verifier->shouldReceive('verify')->andReturnUsing(function ($response, $secret, $type) use ($answers) {
            $this->calls[] = [$response, $secret, $type];

            return $answers[$type] ?? true;
        });
        $this->app->instance(RecaptchaVerifier::class, $verifier);

        $request = UpdateSettingsRequest::create('/recaptcha', 'POST', $data);
        $validator = Validator::make($data, []);
        $request->withValidator($validator);
        $validator->passes();

        return $validator->errors()->toArray();
    }

    public function test_v3_needs_a_completed_challenge(): void
    {
        $this->assertSame(['v3_site_key' => [__('recaptcha::recaptcha.complete_recaptcha_v3')]], $this->afterErrors(['captcha_version' => 'v3_invisible', 'v3_secret_key' => 's']));
        $this->assertSame([], $this->calls);
    }

    public function test_v3_keys_are_verified_and_a_failure_is_reported_on_the_secret(): void
    {
        $data = ['captcha_version' => 'v3_invisible', 'failover_action' => 'none', 'v3_secret_key' => 'S3', 'v3_g_recaptcha_response' => 'R3'];

        $this->assertSame(['v3_secret_key' => ['bad v3']], $this->afterErrors($data, ['v3' => 'bad v3']));
        $this->assertSame([['R3', 'S3', 'v3']], $this->calls);
    }

    public function test_v3_without_a_secret_is_not_verified(): void
    {
        $this->assertSame([], $this->afterErrors(['captcha_version' => 'v3_invisible', 'v3_g_recaptcha_response' => 'R3']));
        $this->assertSame([], $this->calls);
    }

    public function test_v2_versions_need_a_completed_challenge_and_valid_keys(): void
    {
        foreach (['v2_checkbox', 'v2_invisible'] as $version) {
            $this->calls = [];
            $this->assertSame(['v2_site_key' => [__('recaptcha::recaptcha.complete_recaptcha_v2')]], $this->afterErrors(['captcha_version' => $version]), $version);

            $ok = ['captcha_version' => $version, 'v2_secret_key' => 'S2', 'v2_g_recaptcha_response' => 'R2'];
            $this->assertSame([], $this->afterErrors($ok), $version);
            $this->assertSame(['v2_secret_key' => ['bad v2']], $this->afterErrors($ok, ['v2' => 'bad v2']), $version);
            $this->assertSame([['R2', 'S2', 'v2'], ['R2', 'S2', 'v2']], $this->calls, $version);
        }
    }

    public function test_v3_with_v2_failover_checks_both_v3_first(): void
    {
        $data = [
            'captcha_version' => 'v3_invisible', 'failover_action' => 'v2_checkbox',
            'v3_secret_key' => 'S3', 'v3_g_recaptcha_response' => 'R3',
            'v2_secret_key' => 'S2', 'v2_g_recaptcha_response' => 'R2',
        ];

        $this->assertSame(['v3_secret_key' => ['bad v3'], 'v2_secret_key' => ['bad v2']], $this->afterErrors($data, ['v3' => 'bad v3', 'v2' => 'bad v2']));
        $this->assertSame([['R3', 'S3', 'v3'], ['R2', 'S2', 'v2']], $this->calls);
    }

    public function test_v3_with_v2_failover_reports_both_missing_challenges(): void
    {
        $this->assertSame([
            'v3_site_key' => [__('recaptcha::recaptcha.complete_recaptcha_v3')],
            'v2_site_key' => [__('recaptcha::recaptcha.complete_recaptcha_v2')],
        ], $this->afterErrors(['captcha_version' => 'v3_invisible', 'failover_action' => 'v2_checkbox']));
    }

    public function test_an_unknown_version_runs_no_checks(): void
    {
        $this->assertSame([], $this->afterErrors(['captcha_version' => 'v9']));
        $this->assertSame([], $this->calls);
    }

    public function test_rules_require_the_keys_of_the_versions_in_use(): void
    {
        $required = function (array $data): array {
            $rules = UpdateSettingsRequest::create('/recaptcha', 'POST', $data)->rules();

            return array_values(array_filter(
                ['v3_site_key', 'v3_secret_key', 'score_threshold', 'v2_site_key', 'v2_secret_key'],
                fn ($field) => str_starts_with($rules[$field], 'required'),
            ));
        };

        $this->assertSame(['v3_site_key', 'v3_secret_key', 'score_threshold'], $required(['captcha_version' => 'v3_invisible', 'failover_action' => 'none']));
        $this->assertSame(['v3_site_key', 'v3_secret_key', 'score_threshold', 'v2_site_key', 'v2_secret_key'], $required(['captcha_version' => 'v3_invisible', 'failover_action' => 'v2_checkbox']));
        $this->assertSame(['v2_site_key', 'v2_secret_key'], $required(['captcha_version' => 'v2_checkbox', 'failover_action' => 'v2_checkbox']));
        $this->assertSame(['v2_site_key', 'v2_secret_key'], $required(['captcha_version' => 'v2_invisible']));
        $this->assertSame([], $required(['captcha_version' => 'other']));
    }
}
