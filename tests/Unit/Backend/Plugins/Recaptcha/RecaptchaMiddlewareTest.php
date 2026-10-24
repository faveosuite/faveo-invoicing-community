<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Plugins\Recaptcha;

use App\Model\Common\StatusSetting;
use App\Plugins\Recaptcha\Middleware\RecaptchaMiddleware;
use App\Plugins\Recaptcha\Model\RecaptchaSetting;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;
use Tests\DBTestCase;

class RecaptchaMiddlewareTest extends DBTestCase
{
    use DatabaseTransactions;

    private const string HOST = 'billing.test';

    protected function setUp(): void
    {
        parent::setUp();

        StatusSetting::query()->firstOrFail()->update(['recaptcha_status' => 1]);
        RecaptchaSetting::query()->updateOrCreate([], [
            'v2_site_key' => 'v2-site', 'v2_secret_key' => 'v2-secret',
            'v3_site_key' => 'v3-site', 'v3_secret_key' => 'v3-secret',
            'captcha_version' => 'v3_invisible', 'failover_action' => 'v2_checkbox', 'score_threshold' => 0.5,
        ]);
    }

    /**
     * Fake siteverify, answering per secret so v2 and v3 checks can differ.
     *
     * @param  array<string, array<string, mixed>>  $bySecret
     */
    private function fakeGoogle(array $bySecret): void
    {
        Http::fake(fn ($request) => Http::response($bySecret[$request['secret']] ?? ['success' => false]));
    }

    private function check(string $action, ?string $token = 'tok'): Response
    {
        $request = Request::create('https://'.self::HOST.'/x', 'POST', ['g-recaptcha-response' => $token]);
        $this->app->instance('request', $request);

        return new RecaptchaMiddleware()->handle($request, fn () => response('passed'), $action);
    }

    public function test_v3_token_with_matching_action_passes(): void
    {
        $this->fakeGoogle(['v3-secret' => ['success' => true, 'action' => 'newsletter', 'hostname' => self::HOST, 'score' => 0.9]]);

        $this->assertSame('passed', $this->check('newsletter')->getContent());
    }

    public function test_v3_token_for_another_action_is_rejected(): void
    {
        $this->fakeGoogle(['v3-secret' => ['success' => true, 'action' => 'mailChimp', 'hostname' => self::HOST, 'score' => 0.9]]);

        $this->assertSame(422, $this->check('newsletter')->getStatusCode());
    }

    public function test_low_score_switches_to_v2_and_one_solved_checkbox_clears_it(): void
    {
        $this->fakeGoogle([
            'v3-secret' => ['success' => true, 'action' => 'login', 'hostname' => self::HOST, 'score' => 0.1],
            'v2-secret' => ['success' => true],
        ]);

        $low = $this->check('login');
        $this->assertSame(422, $low->getStatusCode());
        $this->assertTrue($low->getData(true)['data']['show_v2_recaptcha']);

        $this->assertSame('passed', $this->check('login')->getContent());
        $this->assertFalse(Session::has('recaptcha_v2_fallback_login'));
    }

    public function test_failed_token_while_in_v2_mode_asks_for_the_checkbox_again(): void
    {
        // e.g. the user reloaded, so the form went back to v3 and sent a v3 token.
        Session::put('recaptcha_v2_fallback_login', true);
        $this->fakeGoogle(['v2-secret' => ['success' => false]]);

        $response = $this->check('login');

        $this->assertSame(422, $response->getStatusCode());
        $this->assertTrue($response->getData(true)['data']['show_v2_recaptcha']);
    }

    public function test_google_unreachable_gives_the_captcha_error_not_a_500(): void
    {
        Http::fake(fn () => throw new ConnectionException('timeout'));

        $this->assertSame(422, $this->check('login')->getStatusCode());
    }

    public function test_non_json_reply_from_google_gives_the_captcha_error_not_a_500(): void
    {
        Http::fake(fn () => Http::response('<html>bad gateway</html>', 502));
        RecaptchaSetting::query()->update(['captcha_version' => 'v2_checkbox']);

        $this->assertSame(422, $this->check('login')->getStatusCode());
    }
}
