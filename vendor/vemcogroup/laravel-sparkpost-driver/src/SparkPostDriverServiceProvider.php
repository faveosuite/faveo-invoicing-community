<?php

namespace Vemcogroup\SparkPostDriver;

use GuzzleHttp\Client;
use Illuminate\Mail\MailManager;
use Illuminate\Support\ServiceProvider;
use Vemcogroup\SparkPostDriver\Transport\SparkPostTransport;

class SparkPostDriverServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->extend('mail.manager', function(MailManager $manager) {
            $manager->extend('sparkpost', function() {
                $config = config('services.sparkpost', []);
                $sparkpostOptions = $config['options'] ?? [];
                $guzzleOptions = $config['guzzle'] ?? [];
                $client = $this->app->make(Client::class, ['config' => $guzzleOptions]);

                return new SparkPostTransport($client, $config['secret'], $sparkpostOptions);
            });

            return $manager;
        });
    }
}
