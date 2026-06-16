<?php

namespace App\Plugins\Zoho\Providers;

use App\Events\UserRegisteredEvent;
use App\Plugins\Zoho\Controllers\Api\{
    ZohoAccessToken,
    ZohoAccountsApi,
};
use App\Plugins\Zoho\Integrations\Campaigns\Controllers\Api\ZohoCampaignsApi;
use App\Plugins\Zoho\Integrations\Campaigns\Controllers\Campaigns;
use App\Plugins\Zoho\Integrations\Campaigns\Providers\ZohoCampaignsNewsletterProvider;
use App\Plugins\Zoho\Integrations\Crm\Controllers\Api\ZohoCrmApi;
use App\Plugins\Zoho\Integrations\Crm\Controllers\Crm;
use App\Plugins\Zoho\Listeners\SyncUserToZohoCampaigns;
use App\Plugins\Zoho\Listeners\SyncUserToZohoCrm;
use App\Plugins\Zoho\Models\ZohoIntegration;
use App\Services\NewsletterManager;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Override;

class ZohoServiceProvider extends ServiceProvider
{
    /**
     * Cache integrations per request.
     */
    protected array $integrations = [];

    /**
     * Register all services.
     */
    #[Override]
    public function register(): void
    {
        $this->mergeConfigFrom(
            app_path('Plugins/Zoho/config/zoho.php'),
            'zoho'
        );

        $this->mergeConfigFrom(
            app_path('Plugins/Zoho/Integrations/Campaigns/config/zoho_campaigns.php'),
            'zoho_campaigns'
        );

        $this->mergeConfigFrom(
            app_path('Plugins/Zoho/Integrations/Crm/config/zoho_crm.php'),
            'zoho_crm'
        );

        /*
        |--------------------------------------------------------------------------
        | Zoho Accounts APIs
        |--------------------------------------------------------------------------
        */

        $this->app->singleton('zoho.accounts.campaigns', fn () => $this->makeAccountsApi('campaigns'));

        $this->app->singleton('zoho.accounts.crm', fn () => $this->makeAccountsApi('crm'));

        /*
        |--------------------------------------------------------------------------
        | Zoho Access Token (integration-aware)
        |--------------------------------------------------------------------------
        */

        $this->app->singleton(fn ($app): ZohoAccessToken => new ZohoAccessToken());

        /*
        |--------------------------------------------------------------------------
        | Zoho APIs
        |--------------------------------------------------------------------------
        */

        $this->app->singleton(function ($app): ZohoCampaignsApi {
            $integration = $this->getIntegration('campaigns');

            return new ZohoCampaignsApi(
                getZohoRegion($integration->client->region),
                $app->make(ZohoAccessToken::class),
                $integration->id
            );
        });

        $this->app->singleton(function ($app): ZohoCrmApi {
            $integration = $this->getIntegration('crm');

            return new ZohoCrmApi(
                getZohoRegion($integration->client->region),
                $app->make(ZohoAccessToken::class),
                $integration->id
            );
        });

        /*
        |--------------------------------------------------------------------------
        | Service layer (Facade targets)
        |--------------------------------------------------------------------------
        */

        $this->app->singleton('zoho.campaigns', fn ($app) => new Campaigns());

        $this->app->singleton('zoho.crm', fn ($app) => new Crm());
    }

    /**
     * Boot plugin resources.
     */
    public function boot(): void
    {
        require_once app_path('Plugins/Zoho/Helpers/Helpers.php');

        $this->loadMigrationsFrom(
            app_path('Plugins/Zoho/database/migrations')
        );

        $this->loadRoutesFrom(
            app_path('Plugins/Zoho/routes/routes.php')
        );

        $this->loadViewsFrom(
            app_path('Plugins/Zoho/resources/views'),
            'zoho'
        );

        Event::listen(UserRegisteredEvent::class, SyncUserToZohoCrm::class);
        Event::listen(UserRegisteredEvent::class, SyncUserToZohoCampaigns::class);

        resolve(NewsletterManager::class)->register(new ZohoCampaignsNewsletterProvider());
    }

    /**
     * Resolve active integration (cached).
     */
    protected function getIntegration(string $platform): ZohoIntegration
    {
        return $this->integrations[$platform]
            ??= ZohoIntegration::with(['client'])
            ->where('platform', $platform)
            ->where('is_active', true)
            ->firstOrFail();
    }

    /**
     * Build Zoho Accounts API.
     */
    protected function makeAccountsApi(string $platform): ZohoAccountsApi
    {
        $integration = $this->getIntegration($platform);

        return new ZohoAccountsApi(
            $integration->client->client_id,
            $integration->client->client_secret,
            getZohoRegion($integration->client->region)
        );
    }
}
