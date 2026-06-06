<?php

namespace App\Plugins\Mailchimp\Http\Client;

use App\Plugins\Mailchimp\Exceptions\MailchimpApiException;
use App\Plugins\Mailchimp\Exceptions\MailchimpRateLimitException;
use Illuminate\Support\Facades\Http;

class MailchimpClient
{
    private string $baseUrl;

    public function __construct(private readonly string $apiKey)
    {
        $dc = ($pos = strrpos($apiKey, '-')) !== false
            ? substr($apiKey, $pos + 1)
            : 'us1';

        $this->baseUrl = "https://{$dc}.api.mailchimp.com/3.0";
    }

    /**
     * Validate the API key by hitting the root endpoint.
     * No credits consumed, no SMS sent — pure auth check.
     */
    public function ping(): bool
    {
        try {
            $data = $this->get('/');

            return isset($data['account_id']);
        } catch (\Throwable) {
            return false;
        }
    }

    public function get(string $endpoint, array $params = []): array
    {
        return $this->request('GET', $endpoint, $params);
    }

    public function post(string $endpoint, array $body = []): array
    {
        return $this->request('POST', $endpoint, $body);
    }

    public function patch(string $endpoint, array $body = []): array
    {
        return $this->request('PATCH', $endpoint, $body);
    }

    public function put(string $endpoint, array $body = []): array
    {
        return $this->request('PUT', $endpoint, $body);
    }

    private function request(string $method, string $endpoint, array $data = []): array
    {
        $url = $this->baseUrl.'/'.ltrim($endpoint, '/');

        $pending = Http::withBasicAuth('anystring', $this->apiKey)
                       ->acceptJson()
                       ->contentType('application/json');

        $response = match (strtoupper($method)) {
            'GET' => $pending->get($url, $data),
            'POST' => $pending->post($url, $data),
            'PATCH' => $pending->patch($url, $data),
            'PUT' => $pending->put($url, $data),
            default => throw new \InvalidArgumentException("Unsupported HTTP method: {$method}"),
        };

        if ($response->status() === 429) {
            throw new MailchimpRateLimitException();
        }

        if ($response->failed()) {
            $body = $response->json() ?? [];
            $message = $body['detail'] ?? $body['title'] ?? "Mailchimp API error ({$response->status()})";
            throw new MailchimpApiException($message, $response->status());
        }

        return $response->json() ?? [];
    }
}
