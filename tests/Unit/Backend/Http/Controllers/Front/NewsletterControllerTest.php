<?php

namespace Tests\Unit\Backend\Http\Controllers\Front;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\DBTestCase;

class NewsletterControllerTest extends DBTestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
    }

    public function test_subscribe_missing_email_returns_422(): void
    {
        $response = $this->postJson('/newsletter/subscribe', []);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['newsletterEmail']);
    }

    public function test_subscribe_invalid_email_format_returns_422(): void
    {
        $response = $this->postJson('/newsletter/subscribe', [
            'newsletterEmail' => 'not-an-email',
        ]);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['newsletterEmail']);
    }

    public function test_subscribe_with_no_providers_returns_400(): void
    {
        // No newsletter providers configured in test env → returns 400
        $response = $this->postJson('/newsletter/subscribe', [
            'newsletterEmail' => 'test@example.com',
        ]);
        $response->assertStatus(400);
        $response->assertJson(['success' => false]);
    }
}
