<?php

namespace App\Streams\License;

use App\Streams\RedisAdapterManager;
use App\Streams\RedisStreamProducer;
use Illuminate\Support\Str;
use RuntimeException;

class LicenseStreamHandler
{
    protected RedisStreamProducer $producer;
    private readonly string $responseStream;
    private readonly int $timeout;

    public function __construct(int $timeout = 10)
    {
        $this->producer = new RedisStreamProducer('license_request');
        $this->responseStream = 'license_responses';
        $this->timeout = $timeout;
    }

    public function getPluginInfo(array $licenseCodes): array
    {
        $correlationId = (string) Str::uuid();

        $this->producer->publish('license_plugin_info', [
            'license_codes' => $licenseCodes,
            'reply_to' => $this->responseStream,
            'correlation_id' => $correlationId,
        ]);

        return $this->waitForResponse($correlationId);
    }

    public function reissueDomain(string $installationPath)
    {
        $correlationId = (string) Str::uuid();

        $this->producer->publish('license_domain_reissue', [
            'installation_path' => $installationPath,
            'reply_to' => $this->responseStream,
            'correlation_id' => $correlationId,
        ]);

        return $this->waitForResponse($correlationId);
    }

    /**
     * Poll the response stream for a message matching the correlation ID.
     */
    protected function waitForResponse(string $correlationId): array
    {
        $redis = RedisAdapterManager::create();
        $start = time();
        $lastId = '0-0';

        while ((time() - $start) < $this->timeout) {
            $messages = $redis->xrange($this->responseStream, $lastId, '+', 100);

            foreach ($messages as $id => $message) {
                $lastId = $id;
                $data = json_decode($message['message'] ?? '{}', true);

                if (($data['payload']['correlation_id'] ?? null) === $correlationId) {
                    return $data['payload'] ?? [];
                }
            }

            // Increment lastId to avoid re-reading the same messages
            if ($lastId !== '0-0') {
                $parts = explode('-', $lastId);
                $lastId = $parts[0].'-'.((int) $parts[1] + 1);
            }

            usleep(100_000); // 100ms between polls
        }

        throw new RuntimeException("Timeout waiting for response (correlation_id: {$correlationId})");
    }
}
