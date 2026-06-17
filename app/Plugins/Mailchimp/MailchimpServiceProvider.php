<?php

namespace App\Plugins\Mailchimp;

use App\Events\OrderPlacedEvent;
use App\Events\UserRegisteredEvent;
use App\Model\Common\Mailchimp\MailchimpSetting;
use App\Plugins\Mailchimp\Http\Client\MailchimpClient;
use App\Plugins\Mailchimp\Listeners\SubscribeUserOnRegister;
use App\Plugins\Mailchimp\Listeners\UnsubscribeOnUserDeleted;
use App\Plugins\Mailchimp\Listeners\UpdateSubscriberOnPurchase;
use App\Plugins\Mailchimp\Providers\MailchimpNewsletterProvider;
use App\Plugins\Mailchimp\Services\ContactBuilder;
use App\Plugins\Mailchimp\Services\MailchimpService;
use App\Services\NewsletterManager;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Override;

class MailchimpServiceProvider extends ServiceProvider
{
    #[Override]
    public function register(): void
    {
        // Bind MailchimpClient — resolved fresh from the stored API key each time
        $this->app->bind(function (): MailchimpClient {
            $apiKey = MailchimpSetting::value('api_key') ?? '';

            return new MailchimpClient($apiKey);
        });

        // ContactBuilder has no dependencies, bind as singleton
        $this->app->singleton(ContactBuilder::class);

        // MailchimpService — singleton so it's created once per request
        $this->app->singleton(fn ($app): MailchimpService => new MailchimpService(
            $app->make(MailchimpClient::class),
            $app->make(ContactBuilder::class),
            MailchimpSetting::firstOrNew(),
        ));

        // Listener singletons
        $this->app->singleton(SubscribeUserOnRegister::class);
        $this->app->singleton(UpdateSubscriberOnPurchase::class);
        $this->app->singleton(UnsubscribeOnUserDeleted::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');
        $this->loadRoutesFrom(__DIR__.'/routes/routes.php');

        Event::listen(UserRegisteredEvent::class, SubscribeUserOnRegister::class);
        Event::listen(OrderPlacedEvent::class, UpdateSubscriberOnPurchase::class);

        resolve(NewsletterManager::class)->register(
            new MailchimpNewsletterProvider(resolve(MailchimpService::class))
        );
    }
}
