<?php

namespace App\Providers;

use App\Events\UserRegisteredEvent;
use App\Listeners\SyncUserToPipedrive;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        \App\Events\Event::class => [
            'App\Listeners\EventListener',
        ],
        \Illuminate\Auth\Events\Login::class => [
            \App\Listeners\MergeGuestCartOnLogin::class,
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
    public function boot()
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents()
    {
        return false;
    }
}
