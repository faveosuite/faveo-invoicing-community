<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Http\Middleware;

use App\Http\Middleware\CheckPulseEnabled;
use Illuminate\Http\Request;
use Tests\TestCase;

class CheckPulseEnabledTest extends TestCase
{
    private function next(): \Closure
    {
        return fn ($req) => response('passed');
    }

    public function test_passes_through_when_pulse_enabled(): void
    {
        config(['pulse.enabled' => true]);
        $request = Request::create('/pulse', 'GET');

        $response = (new CheckPulseEnabled())->handle($request, $this->next());

        $this->assertSame('passed', $response->getContent());
    }

    public function test_redirects_when_pulse_disabled(): void
    {
        config(['pulse.enabled' => false]);
        $request = Request::create('/pulse', 'GET');

        $response = (new CheckPulseEnabled())->handle($request, $this->next());

        $this->assertSame(302, $response->getStatusCode());
    }
}
