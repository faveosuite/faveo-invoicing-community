<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Http\Middleware;

use App\Http\Middleware\VerifyThirdPartyApps;
use App\ThirdPartyApp;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Tests\TestCase;

class VerifyThirdPartyAppsTest extends TestCase
{
    use DatabaseTransactions;

    private function next(): \Closure
    {
        return fn ($req) => response('passed');
    }

    public function test_blocks_request_with_old_timestamp(): void
    {
        $request = Request::create('/api/test', 'POST');
        $request->merge(['timestamp' => time() - 1000]); // 1000 seconds old > 900

        $response = (new VerifyThirdPartyApps())->handle($request, $this->next());
        $data = json_decode($response->getContent(), true);

        $this->assertSame('fails', $data['result']['status']);
    }

    public function test_blocks_request_with_invalid_signature(): void
    {
        $app = ThirdPartyApp::create([
            'app_name' => 'test_app_'.uniqid(),
            'app_key' => 'test-key-'.uniqid(),
            'app_secret' => 'test-secret',
        ]);

        $request = Request::create('/api/test', 'POST');
        $request->merge(['timestamp' => time()]);
        $request->headers->set('app-key', $app->app_key);
        $request->headers->set('signature', 'wrong-signature');

        $response = (new VerifyThirdPartyApps())->handle($request, $this->next());
        $data = json_decode($response->getContent(), true);

        $this->assertSame('fails', $data['result']['status']);
    }

    public function test_passes_request_with_valid_signature(): void
    {
        $secret = 'my-secret-'.uniqid();
        $app = ThirdPartyApp::create([
            'app_name' => 'test_app_'.uniqid(),
            'app_key' => 'test-key-'.uniqid(),
            'app_secret' => $secret,
        ]);

        // In tests, php://input is always empty — middleware hashes '' against the secret
        $signature = hash_hmac('sha256', '', $secret);

        $request = Request::create('/api/test', 'POST');
        $request->merge(['timestamp' => time()]);
        $request->headers->set('app-key', $app->app_key);
        $request->headers->set('signature', $signature);

        $response = (new VerifyThirdPartyApps())->handle($request, $this->next());

        $this->assertSame('passed', $response->getContent());
    }
}
