<?php

use GuzzleHttp\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Vemcogroup\SparkPostDriver\Transport\SparkPostTransport;

if (!function_exists('sparkpost_delete_supression')) {
    function sparkpost_delete_supression($email): JsonResponse
    {
        Validator::make(['email' => $email], [
            'email' => [
                'required', 'email'
            ]
        ])->validate();

        $config = config('services.sparkpost', []);
        $sparkpostOptions = $config['options'] ?? [];
        $guzzleOptions = $config['guzzle'] ?? [];
        $client = app()->make(Client::class, $guzzleOptions);

        return (new SparkPostTransport($client, $config['secret'], $sparkpostOptions))->deleteSupression($email);
    }
}

if (!function_exists('sparkpost_check_email')) {
    function sparkpost_check_email($email): JsonResponse
    {
        Validator::make(['email' => $email], [
            'email' => [
                'required', 'email'
            ]
        ])->validate();

        $config = config('services.sparkpost', []);
        $sparkpostOptions = $config['options'] ?? [];
        $guzzleOptions = $config['guzzle'] ?? [];
        $client = app()->make(Client::class, $guzzleOptions);

        return (new SparkPostTransport($client, $config['secret'], $sparkpostOptions))->validateSingleRecipient($email);
    }
}
