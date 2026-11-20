<?php

declare(strict_types=1);

namespace App\Services\Cloud;

use App\License\Models\License;
use App\Model\Cloud\CloudEvent;
use App\Model\Common\FaveoCloud;
use App\Services\EntitlementService;
use GuzzleHttp\Client;
use RuntimeException;
use Throwable;

/**
 * Billing → Faveo Cloud events, the Standard Webhooks way: every event goes into the outbox
 * (cloud_events) first, `cloud:events` sends what is due to ONE endpoint on cloud central, and
 * retries with growing gaps for 3 days. Each request carries
 *
 *   webhook-id         the event id (the cloud skips ids it has seen)
 *   webhook-timestamp  unix seconds (the cloud refuses anything more than 5 minutes off)
 *   webhook-signature  "v1," + base64(HMAC-SHA256("id.timestamp.body")) with the secret of the
 *                      faveo_app_key third-party app — the key billing already uses for the cloud
 *
 * A license.updated event carries the license record signed with billing's Ed25519 key as
 * well, so the cloud's license client checks it on its own (License::accept()). Its data is
 * built when sent, so a late retry still delivers the newest record.
 */
class CloudEvents
{
    public const string LICENSE_UPDATED = 'license.updated';

    public const string LICENSE_SUSPENDED = 'license.suspended';

    public const string LICENSE_UNSUSPENDED = 'license.unsuspended';

    public const string PATH = '/billing/events';

    /** Seconds to wait after each failed try; the last gap repeats. */
    private const array BACKOFF = [60, 300, 1800, 7200, 18000, 36000];

    /** An event not accepted within this many hours is marked failed (an admin can resend it). */
    private const int GIVE_UP_HOURS = 72;

    public function __construct(private readonly ?Client $client = null) {}

    /**
     * A cloud license's rights changed. One pending license.updated per license is enough: its
     * data is built when sent.
     */
    public function licenseChanged(License $license): void
    {
        $tenant = $this->tenantOf($license);
        if ($tenant === null) {
            return;
        }

        $pending = CloudEvent::where('type', self::LICENSE_UPDATED)->whereNull('sent_at')->whereNull('failed_at')
            ->where('payload->license_id', $license->id)->exists();
        if (! $pending) {
            $this->record(self::LICENSE_UPDATED, $tenant, ['license_id' => $license->id]);
        }
    }

    /** A cloud license was suspended (status not 1) or made active again. */
    public function statusChanged(License $license): void
    {
        $tenant = $this->tenantOf($license);
        if ($tenant !== null) {
            $type = (int) $license->license_status === 1 ? self::LICENSE_UNSUSPENDED : self::LICENSE_SUSPENDED;
            $this->record($type, $tenant, ['license_code' => $license->license_code]);
        }
    }

    /** @param  array<string, mixed>  $payload */
    public function record(string $type, ?string $tenant, array $payload): CloudEvent
    {
        return CloudEvent::create(['type' => $type, 'tenant' => $tenant, 'payload' => $payload]);
    }

    /** Sends every event that is due, oldest first. Returns how many the cloud accepted. */
    public function sendDue(int $limit = 100): int
    {
        $accepted = 0;
        CloudEvent::whereNull('sent_at')->whereNull('failed_at')
            ->where(fn ($q) => $q->whereNull('next_attempt_at')->orWhere('next_attempt_at', '<=', now()))
            ->orderBy('created_at')->limit($limit)->get()
            ->each(function (CloudEvent $event) use (&$accepted): void {
                if ($this->send($event)) {
                    $accepted++;
                }
            });

        return $accepted;
    }

    /** One try. Accepted = the cloud answered 2xx; anything else schedules the next try. */
    public function send(CloudEvent $event): bool
    {
        try {
            $api = new CloudApi($this->client ?? new Client(['timeout' => 10]), FaveoCloud::first());
            $secret = (string) $api->keys()->app_secret;
            if ($secret === '') {
                throw new RuntimeException('The faveo_app_key third-party app has no secret.');
            }

            $body = json_encode([
                'id' => $event->id, 'type' => $event->type, 'tenant' => $event->tenant,
                'data' => $this->data($event), 'sent_at' => now()->toAtomString(),
            ], JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
            $timestamp = (string) time();

            $api->raw('POST', self::PATH, ['body' => $body, 'headers' => [
                'Content-Type' => 'application/json',
                'webhook-id' => $event->id,
                'webhook-timestamp' => $timestamp,
                'webhook-signature' => self::signature($event->id, $timestamp, $body, $secret),
            ]]);
        } catch (Throwable $e) {
            $this->failedTry($event, $e->getMessage());

            return false;
        }

        $event->update(['sent_at' => now(), 'attempts' => $event->attempts + 1, 'next_attempt_at' => null, 'last_error' => null]);

        return true;
    }

    /** Standard Webhooks signature: "v1," + base64(HMAC-SHA256 of "id.timestamp.body"). */
    public static function signature(string $id, string $timestamp, string $body, string $secret): string
    {
        return 'v1,'.base64_encode(hash_hmac('sha256', "{$id}.{$timestamp}.{$body}", $secret, binary: true));
    }

    /** A failed event goes back in the queue (admin "Resend"). */
    public function resend(CloudEvent $event): void
    {
        $event->update(['failed_at' => null, 'sent_at' => null, 'next_attempt_at' => now()]);
    }

    /** Daily safety net: a fresh license.updated for every active cloud license. Returns how many. */
    public function reconcile(): int
    {
        $count = 0;
        License::whereIn('product_id', cloudPopupProducts())->where('license_status', 1)
            ->whereNotNull('license_domain')->where('license_domain', '!=', '')
            ->chunkById(500, function ($licenses) use (&$count): void {
                foreach ($licenses as $license) {
                    $this->licenseChanged($license);
                    $count++;
                }
            });

        return $count;
    }

    /** @return array<string, mixed> */
    private function data(CloudEvent $event): array
    {
        if ($event->type !== self::LICENSE_UPDATED) {
            return $event->payload;
        }

        $license = License::find((int) ($event->payload['license_id'] ?? 0));
        if (! $license instanceof License) {
            throw new RuntimeException('The license is gone.');
        }
        $rootUrl = 'https://'.$event->tenant;

        return [
            'license_code' => $license->license_code,
            'product_id' => $license->product_id,
            'root_url' => $rootUrl,
            ...resolve(EntitlementService::class)->signedRecord($license, (string) $license->product_id, $license->license_code, $rootUrl),
        ];
    }

    private function failedTry(CloudEvent $event, string $error): void
    {
        $attempts = $event->attempts + 1;
        $giveUp = $event->created_at !== null && $event->created_at->copy()->addHours(self::GIVE_UP_HOURS)->isPast();

        $event->update([
            'attempts' => $attempts,
            'last_error' => mb_substr($error, 0, 2000),
            'failed_at' => $giveUp ? now() : null,
            'next_attempt_at' => $giveUp ? null : now()->addSeconds(self::BACKOFF[min($attempts, count(self::BACKOFF)) - 1]),
        ]);
    }

    /** The cloud domain of a cloud product's license, or null (not cloud, or no tenant yet). */
    private function tenantOf(License $license): ?string
    {
        if (! in_array($license->product_id, array_map(intval(...), cloudPopupProducts()), strict: true)) {
            return null;
        }
        $domain = trim(explode(',', (string) $license->license_domain)[0]);
        $domain = (string) (parse_url(str_contains($domain, '://') ? $domain : 'https://'.$domain, PHP_URL_HOST) ?? '');

        return $domain !== '' ? $domain : null;
    }
}
