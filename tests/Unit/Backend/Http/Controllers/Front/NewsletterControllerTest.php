<?php

namespace Tests\Unit\Backend\Http\Controllers\Front;

use App\Contracts\NewsletterProvider;
use App\Services\NewsletterManager;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery;
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
        $response = $this->postJson('/newsletter/subscribe', [
            'newsletterEmail' => 'test@example.com',
        ]);
        $response->assertStatus(400);
        $response->assertJson(['success' => false]);
    }

    public function test_subscribe_with_enabled_provider_returns_200(): void
    {
        // Covers lines 26-28: provider is enabled → subscribeAll called → success
        $provider = Mockery::mock(NewsletterProvider::class);
        $provider->shouldReceive('isEnabled')->andReturn(true);
        $provider->shouldReceive('subscribeEmail')->once();

        $manager = new NewsletterManager();
        $manager->register($provider);
        $this->app->instance(NewsletterManager::class, $manager);

        $response = $this->postJson('/newsletter/subscribe', [
            'newsletterEmail' => 'user@example.com',
        ]);

        Mockery::close();

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }
}
