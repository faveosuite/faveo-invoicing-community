<?php

namespace Tests\Unit\Backend\Services;

use App\License\Models\License;
use App\License\Services\Ed25519SigningService;
use App\Model\Cloud\CloudEvent;
use App\Model\Common\FaveoCloud;
use App\Model\Payment\Plan;
use App\Model\Product\CloudProducts;
use App\Model\Product\Entitlement;
use App\Model\Product\Product;
use App\Services\Cloud\CloudEvents;
use App\Services\EntitlementService;
use App\ThirdPartyApp;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Carbon;
use Tests\DBTestCase;

class CloudEventsTest extends DBTestCase
{
    private Product $product;

    /** @var list<array<string, mixed>> */
    private array $history = [];

    protected function setUp(): void
    {
        parent::setUp();
        ThirdPartyApp::firstOrCreate(['app_name' => 'faveo_app_key'], ['app_key' => 'cloud-key', 'app_secret' => 'cloud-secret']);
        FaveoCloud::firstOrCreate([], ['cloud_central_domain' => 'https://central.test', 'cloud_cname' => 'test']);
        $this->product = Product::factory()->create();
        CloudProducts::create(['cloud_product' => $this->product->id, 'cloud_free_plan' => Plan::factory()->create(['product' => $this->product->id])->id]);
    }

    /** @param  list<Response>  $responses */
    private function events(array $responses): CloudEvents
    {
        $stack = HandlerStack::create(new MockHandler($responses));
        $stack->push(Middleware::history($this->history));

        return new CloudEvents(new Client(['handler' => $stack]));
    }

    private function cloudLicense(string $domain = 'acme.faveocloud.test'): License
    {
        return License::create([
            'product_id' => $this->product->id, 'license_code' => 'CLOUD'.strtoupper(uniqid()).'0005',
            'license_domain' => $domain, 'license_limit' => 1, 'license_status' => 1,
        ]);
    }

    public function test_a_cloud_license_change_queues_one_license_updated_and_other_products_none(): void
    {
        $chat = Entitlement::create(['code' => 'chat', 'label' => 'Chat', 'type' => 'boolean', 'default_value' => '0']);
        $license = $this->cloudLicense(); // its first date grants already queue one
        $service = resolve(EntitlementService::class);

        $service->setOverride($license, $chat, '1', null);
        $service->setOverride($license, $chat, '0', null); // still pending: no more events
        $this->assertSame(1, CloudEvent::where('type', CloudEvents::LICENSE_UPDATED)->whereNull('sent_at')->where('payload->license_id', $license->id)->count());
        $this->assertSame('acme.faveocloud.test', CloudEvent::where('payload->license_id', $license->id)->value('tenant'));

        $selfHosted = License::create(['product_id' => Product::factory()->create()->id, 'license_code' => 'SELF'.uniqid(), 'license_domain' => 'x.test', 'license_limit' => 1, 'license_status' => 1]);
        $service->setOverride($selfHosted, $chat, '1', null);
        $this->assertSame(0, CloudEvent::where('payload->license_id', $selfHosted->id)->count());
    }

    public function test_an_event_is_sent_signed_and_carries_the_ed25519_signed_record(): void
    {
        $license = $this->cloudLicense();
        $event = resolve(CloudEvents::class)->record(CloudEvents::LICENSE_UPDATED, 'acme.faveocloud.test', ['license_id' => $license->id]);

        $this->assertTrue($this->events([new Response(200, [], '{"status":"success"}')])->send($event));

        $request = $this->history[0]['request'];
        $body = (string) $request->getBody();
        $secret = (string) ThirdPartyApp::where('app_name', 'faveo_app_key')->value('app_secret');
        $this->assertStringEndsWith('/billing/events', (string) $request->getUri());
        $this->assertSame($event->id, $request->getHeaderLine('webhook-id'));
        $this->assertSame(
            CloudEvents::signature($event->id, $request->getHeaderLine('webhook-timestamp'), $body, $secret),
            $request->getHeaderLine('webhook-signature'),
        );

        $data = json_decode($body, true)['data'];
        $this->assertSame('https://acme.faveocloud.test', $data['root_url']);
        $this->assertTrue(resolve(Ed25519SigningService::class)->verify(
            $license->product_id.'|'.$license->license_code.'|'.$data['root_url'].'|'.$data['entitlements'],
            $data['entitlements_signature'],
        ));
        $this->assertNotNull($event->fresh()->sent_at);
    }

    public function test_a_refused_event_is_retried_with_growing_gaps_then_failed_after_3_days(): void
    {
        Carbon::setTestNow('2026-11-20 10:00:00');
        $event = resolve(CloudEvents::class)->record(CloudEvents::LICENSE_SUSPENDED, 'acme.faveocloud.test', ['license_code' => 'X']);

        $this->assertFalse($this->events([new Response(500)])->send($event));
        $this->assertSame('2026-11-20 10:01:00', $event->fresh()->next_attempt_at?->toDateTimeString());

        $this->assertFalse($this->events([new Response(401)])->send($event->fresh()));
        $this->assertSame('2026-11-20 10:05:00', $event->fresh()->next_attempt_at?->toDateTimeString());

        Carbon::setTestNow('2026-11-23 10:00:01');
        $this->assertFalse($this->events([new Response(500)])->send($event->fresh()));
        $this->assertNotNull($event->fresh()->failed_at);
        $this->assertSame(0, $this->events([])->sendDue()); // failed: not sent again

        resolve(CloudEvents::class)->resend($event->fresh());
        $this->assertSame(1, $this->events([new Response(200)])->sendDue());
        Carbon::setTestNow();
    }

    public function test_suspending_and_reactivating_a_cloud_license_queues_events(): void
    {
        $license = $this->cloudLicense();

        $license->update(['license_status' => 2]);
        $license->update(['license_status' => 1]);

        $types = CloudEvent::whereIn('type', [CloudEvents::LICENSE_SUSPENDED, CloudEvents::LICENSE_UNSUSPENDED])
            ->where('payload->license_code', $license->license_code)->orderBy('created_at')->pluck('type')->all();
        $this->assertSame([CloudEvents::LICENSE_SUSPENDED, CloudEvents::LICENSE_UNSUSPENDED], $types);
    }

    public function test_the_daily_reconcile_queues_every_active_cloud_license(): void
    {
        $active = $this->cloudLicense('one.faveocloud.test');
        $this->cloudLicense('')->update(['license_domain' => null]); // no tenant yet

        resolve(CloudEvents::class)->reconcile();

        $this->assertSame(1, CloudEvent::where('type', CloudEvents::LICENSE_UPDATED)->where('payload->license_id', $active->id)->count());
    }
}
