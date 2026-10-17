<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Services;

use App\Contracts\NewsletterProvider;
use App\Services\NewsletterManager;
use Mockery;
use Tests\TestCase;

class NewsletterManagerTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function manager(): NewsletterManager
    {
        return new NewsletterManager();
    }

    public function test_has_enabled_providers_returns_false_when_empty(): void
    {
        $this->assertFalse($this->manager()->hasEnabledProviders());
    }

    public function test_has_enabled_providers_returns_true_when_one_enabled(): void
    {
        $manager = $this->manager();
        $provider = Mockery::mock(NewsletterProvider::class);
        $provider->shouldReceive('isEnabled')->andReturn(true);

        $manager->register($provider);

        $this->assertTrue($manager->hasEnabledProviders());
    }

    public function test_has_enabled_providers_returns_false_when_all_disabled(): void
    {
        $manager = $this->manager();
        $provider = Mockery::mock(NewsletterProvider::class);
        $provider->shouldReceive('isEnabled')->andReturn(false);

        $manager->register($provider);

        $this->assertFalse($manager->hasEnabledProviders());
    }

    public function test_subscribe_all_calls_enabled_providers(): void
    {
        $manager = $this->manager();

        $enabled = Mockery::mock(NewsletterProvider::class);
        $enabled->shouldReceive('isEnabled')->andReturn(true);
        $enabled->shouldReceive('subscribeEmail')->once()->with('test@example.com');

        $disabled = Mockery::mock(NewsletterProvider::class);
        $disabled->shouldReceive('isEnabled')->andReturn(false);
        $disabled->shouldNotReceive('subscribeEmail');

        $manager->register($enabled);
        $manager->register($disabled);
        $manager->subscribeAll('test@example.com');

        $this->assertTrue(true);
    }

    public function test_subscribe_all_continues_after_provider_exception(): void
    {
        $manager = $this->manager();

        $failing = Mockery::mock(NewsletterProvider::class);
        $failing->shouldReceive('isEnabled')->andReturn(true);
        $failing->shouldReceive('subscribeEmail')->andThrow(new \RuntimeException('API error'));

        $working = Mockery::mock(NewsletterProvider::class);
        $working->shouldReceive('isEnabled')->andReturn(true);
        $working->shouldReceive('subscribeEmail')->once();

        $manager->register($failing);
        $manager->register($working);

        \Logger::shouldReceive('exception')->once();

        $manager->subscribeAll('test@example.com');

        $this->assertTrue(true);
    }

    public function test_subscribe_all_skips_disabled_providers(): void
    {
        $manager = $this->manager();

        $provider = Mockery::mock(NewsletterProvider::class);
        $provider->shouldReceive('isEnabled')->andReturn(false);
        $provider->shouldNotReceive('subscribeEmail');

        $manager->register($provider);
        $manager->subscribeAll('test@example.com');

        $this->assertTrue(true);
    }
}
