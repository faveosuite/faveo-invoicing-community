<?php

namespace Tests\Unit\AutoUpdate;

use App\ApiKey;
use App\Http\Controllers\AutoUpdate\AutoUpdateController;
use Illuminate\Support\Facades\Cache;
use Tests\DBTestCase;

class AutoUpdateControllerTest extends DBTestCase
{
    public function test_add_new_product_to_aus_returns_the_generated_product_key(): void
    {
        // Empty license config -> LicenseService::getUrl()/getApiKeySecret() are ''.
        // Pre-seed a live, non-expiring cached token under that same '' config so
        // getValidToken() short-circuits and no real HTTP call to a license
        // manager is ever made by this test.
        ApiKey::create([]);
        Cache::put('license_response_'.md5(''), [
            'access_token' => 'fake-token',
            'stored_at' => now()->timestamp,
            'expires_in' => 3600,
        ]);

        $key = (new AutoUpdateController())->addNewProductToAUS(1, 'Test Product', 'TEST-SKU');

        // This is the actual bug being fixed: the method used to fall off the
        // end of its try block and implicitly return null, so the key it just
        // sent to the License Manager was never available to persist locally.
        $this->assertNotEmpty($key);
        $this->assertSame(16, strlen($key));
    }
}
