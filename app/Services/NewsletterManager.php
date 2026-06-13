<?php

namespace App\Services;

use App\Contracts\NewsletterProvider;

class NewsletterManager
{
    /** @var NewsletterProvider[] */
    private array $providers = [];

    public function register(NewsletterProvider $provider): void
    {
        $this->providers[] = $provider;
    }

    /**
     * Fan out to every enabled provider. A single provider failure does not
     * stop the others — errors are logged internally.
     */
    public function subscribeAll(string $email): void
    {
        foreach ($this->providers as $provider) {
            if (! $provider->isEnabled()) {
                continue;
            }

            try {
                $provider->subscribeEmail($email);
            } catch (\Throwable $e) {
                \Logger::exception($e);
            }
        }
    }

    public function hasEnabledProviders(): bool
    {
        foreach ($this->providers as $provider) {
            if ($provider->isEnabled()) {
                return true;
            }
        }

        return false;
    }
}
