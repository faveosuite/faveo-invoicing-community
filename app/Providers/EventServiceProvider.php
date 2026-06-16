<?php

namespace App\Providers;

use App\Events\Event;
use App\Events\UserRegisteredEvent;
use App\Listeners\MergeGuestCartOnLogin;
use App\Listeners\SyncUserToPipedrive;
use Illuminate\Auth\Events\Login;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Override;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Event::class => [
            'App\Listeners\EventListener',
        ],
        Login::class => [
            MergeGuestCartOnLogin::class,
        ],
        UserRegisteredEvent::class => [
            SyncUserToPipedrive::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    #[Override]
    public function boot()
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    #[Override]
    public function shouldDiscoverEvents()
    {
        return false;
    }
}
