<?php

namespace App\Services\Cloud;

use App\Model\Common\FaveoCloud;
use App\ThirdPartyApp;
use GuzzleHttp\Client;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * The one way billing talks to the Faveo Cloud central server (FaveoCloud::cloud_central_domain).
 * Adds the app key/token/timestamp and the HMAC signature, asks for JSON, and reads the reply.
 */
class CloudApi
{
    public function __construct(private readonly Client $client, private readonly ?FaveoCloud $cloud = null)
    {
    }

    /**
     * GET with the app key as ?key=.
     *
     * @return array<mixed>
     */
    public function get(string $endpoint): array
    {
        return $this->decode($endpoint, $this->raw('GET', $endpoint, ['query' => ['key' => $this->keys()->app_key]]));
    }

    /**
     * Signed POST/DELETE. A token in $data is kept (createTenant stores it for the cloud's call back).
     *
     * @param  array<mixed>  $data
     * @return array<mixed>
     */
    public function send(string $method, string $endpoint, array $data): array
    {
        $keys = $this->keys();
        $data += ['app_key' => $keys->app_key, 'token' => Str::random(32), 'timestamp' => time()];

        return $this->decode($endpoint, $this->raw($method, $endpoint, [
            'form_params' => $data,
            'headers' => ['signature' => hash_hmac('sha256', http_build_query($data), (string) $keys->app_secret)],
        ]));
    }

    /**
     * The unparsed reply, for endpoints that don't answer in plain JSON (checkDomain).
     *
     * @param  array<string, mixed>  $options
     */
    public function raw(string $method, string $endpoint, array $options): string
    {
        if (! $this->cloud?->cloud_central_domain) {
            throw new RuntimeException('Faveo Cloud central domain is not configured.');
        }
        $options['headers'] = ($options['headers'] ?? []) + ['Accept' => 'application/json'];

        return (string) $this->client->request($method, $this->cloud->cloud_central_domain.$endpoint, $options)->getBody();
    }

    public function keys(): ThirdPartyApp
    {
        $keys = ThirdPartyApp::where('app_name', 'faveo_app_key')->first(['app_key', 'app_secret']);
        if (! $keys || ! $keys->app_key) {
            throw new RuntimeException(__('message.cloud_invalid_message'));
        }

        return $keys;
    }

    /**
     * The cloud can print notices before its JSON, so read from the first brace. (Cutting at the
     * second brace, as some callers used to, broke any reply with a nested object.).
     *
     * @return array<mixed>
     */
    private function decode(string $endpoint, string $body): array
    {
        $result = json_decode(substr($body, (int) strpos($body, '{')), true);
        if (! is_array($result)) {
            throw new RuntimeException(sprintf('Cloud %s returned an unreadable response: %s', $endpoint, Str::limit($body, 500)));
        }

        return $result;
    }
}
