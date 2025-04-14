<?php

namespace Tests\Unit;

use App\Http\Controllers\Common\BaseMailChimpController;
use App\User;
use Tests\TestCase;
use Tests\DBTestCase;
use Illuminate\Http\Request;

class MailchimpControllerTest extends DBTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->classObject = new BaseMailChimpController();
    }

    public function test_post_mailchimp_settings_success()
    {
        $payload = [
            'list_id' => '1',
            'subscribe_status' => 1,
        ];
        $user = User::factory()->create(['role' => 'admin']);
        $this->actingAs($user);
        $request = Request::create('/dummy-url', 'POST', $payload);
        $methodResponse = $this->getPrivateMethod($this->classObject, 'postMailChimpSettings', [$request]);
        $this->assertNotEmpty($methodResponse);
    }
}
