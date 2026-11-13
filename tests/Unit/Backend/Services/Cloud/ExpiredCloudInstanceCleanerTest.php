<?php

namespace Tests\Unit\Backend\Services\Cloud;

use App\License\Models\Installation;
use App\License\Models\License;
use App\Model\Common\FaveoCloud;
use App\Model\Mailjob\Condition;
use App\Model\Order\Order;
use App\Model\Payment\Plan;
use App\Model\Product\CloudProducts;
use App\Model\Product\Product;
use App\Model\Product\Subscription;
use App\Services\Cloud\ExpiredCloudInstanceCleaner;
use App\ThirdPartyApp;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\DBTestCase;

class ExpiredCloudInstanceCleanerTest extends DBTestCase
{
    use DatabaseTransactions;

    private Product $product;

    private Plan $plan;

    private MockHandler $cloud;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = \App\User::factory()->create(); // the cron runs with no one logged in
        Condition::where('job', 'cloud')->update(['active' => true, 'days' => '1']);
        config([
            'custom.google_chat' => null, // skipped, so the queued responses below are only cloud calls
            'custom.cloud_delete_job_url_normal' => 'https://jobs.test.local/delete/normal',
            'custom.cloud_delete_job_url_custom' => 'https://jobs.test.local/delete/custom',
        ]);
        ThirdPartyApp::firstOrCreate(['app_name' => 'faveo_app_key'], ['app_key' => 'test-key', 'app_secret' => 'test-secret']);
        FaveoCloud::updateOrCreate(['id' => 1], ['cloud_central_domain' => 'https://cloud.test.local', 'cloud_cname' => 'test']);
        $this->product = Product::factory()->create();
        $this->plan = Plan::create(['name' => 'Cloud '.uniqid(), 'product' => $this->product->id, 'days' => 30]);
        CloudProducts::create(['cloud_product' => $this->product->id, 'cloud_free_plan' => $this->plan->id]);

        $this->cloud = new MockHandler;
        $this->app->bind(Client::class, fn () => new Client(['handler' => HandlerStack::create($this->cloud)]));
    }

    public function test_does_nothing_when_the_cloud_task_is_off(): void
    {
        Condition::where('job', 'cloud')->update(['active' => false]);
        $notDeleted = Subscription::where('is_deleted', 0)->count();

        (new ExpiredCloudInstanceCleaner)();

        $this->assertSame($notDeleted, Subscription::where('is_deleted', 0)->count());
    }

    public function test_deletes_an_expired_instance(): void
    {
        $sub = $this->expiredOrderOn('live.test.local');
        $this->cloud->append(new Response(200, [], json_encode(['status' => 'success'])), new Response(200));

        (new ExpiredCloudInstanceCleaner)();

        $this->assertSame(1, (int) $sub->fresh()->is_deleted);
    }

    public function test_plan_changed_old_order_never_deletes_the_live_instance(): void
    {
        $old = $this->expiredOrderOn('live.test.local', 'Terminated');
        $this->paidOrderOn('live.test.local');
        $this->cloud->append(new Response(200, [], json_encode(['status' => 'success'])));

        (new ExpiredCloudInstanceCleaner)();

        $this->assertSame(1, $this->cloud->count(), 'cloud DELETE must not be called');
        $this->assertSame(0, (int) $old->fresh()->is_deleted);
    }

    public function test_old_order_cannot_delete_a_domain_a_newer_order_owns(): void
    {
        $old = $this->expiredOrderOn('live.test.local'); // not Terminated: only the last guard stops it
        $this->paidOrderOn('live.test.local');
        $this->cloud->append(new Response(200, [], json_encode(['status' => 'success'])));

        (new ExpiredCloudInstanceCleaner)();

        $this->assertSame(1, $this->cloud->count(), 'cloud DELETE must not be called');
        $this->assertSame(0, (int) $old->fresh()->is_deleted);
    }

    public function test_deletion_is_recorded_even_when_cron_cleanup_fails(): void
    {
        $sub = $this->expiredOrderOn('live.test.local');
        $this->cloud->append(new Response(200, [], json_encode(['status' => 'success']))); // no response left for the cron call

        (new ExpiredCloudInstanceCleaner)();

        $this->assertSame(1, (int) $sub->fresh()->is_deleted);
    }

    private function expiredOrderOn(string $domain, string $status = 'executed'): Subscription
    {
        return $this->orderOn($domain, $status, now()->subDays(10));
    }

    private function paidOrderOn(string $domain): Subscription
    {
        return $this->orderOn($domain, 'executed', now()->addYear());
    }

    private function orderOn(string $domain, string $status, \DateTimeInterface $endsAt): Subscription
    {
        $order = Order::factory()->create(['client' => $this->user->id, 'product' => $this->product->id, 'order_status' => $status]);
        License::create(['product_id' => $this->product->id, 'license_code' => 'X'.$order->number, 'license_order_number' => $order->number]);
        Installation::create(['product_id' => $this->product->id, 'license_code' => 'X'.$order->number, 'installation_domain' => $domain]);

        return Subscription::factory()->create([
            'order_id' => $order->id, 'product_id' => $this->product->id, 'user_id' => $this->user->id,
            'plan_id' => $this->plan->id, 'is_deleted' => 0, 'ends_at' => $endsAt,
        ]);
    }
}
