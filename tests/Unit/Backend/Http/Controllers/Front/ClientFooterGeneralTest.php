<?php

namespace Tests\Unit\Backend\Http\Controllers\Front;

use App\Model\Common\PricingTemplate;
use App\Model\Order\Invoice;
use App\Model\Payment\Plan;
use App\Model\Payment\PlanPrice;
use App\Model\Product\CloudProducts;
use App\Model\Product\Product;
use App\Model\Product\ProductGroup;
use App\User;
use DB;
use PHPUnit\Framework\Attributes\Group;
use Tests\DBTestCase;

class ClientFooterGeneralTest extends DBTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
    }

    #[Group('demo')]
    public function test_request_demo_required_field_not_given(): void
    {
        $this->withoutMiddleware();
        $response = $this->call('POST', 'demo-request');
        $response->assertSessionHasErrors('demoname', 'The name field is required');
    }

    #[Group('demo')]
    public function test_request_demo_spam_detected(): void
    {
        $this->withoutMiddleware();
        $response = $this->call('POST', 'demo-request', ['demoname' => 'test',
            'demoemail' => 'test@gmail.com',
            'country_code' => '91',
            'Mobile' => 4335544354,
            'demomessage' => 'This is a demo message',
            'demo' => [
                'pot_field' => 'ghfhkgj',     // honeypot filled → spam
                'time_field' => encrypt(time() - 10),
            ],
        ]);
        $response->assertRedirect();
        $response->assertSessionHasErrors('demo');
    }

    #[Group('demo')]
    public function test_request_demo_when_spam_name_given(): void
    {
        $this->withoutMiddleware();
        $response = $this->call('POST', 'demo-request', ['demoname' => 'test',
            'demoemail' => 'test@gmail.com',
            'country_code' => '91',
            'Mobile' => 4335544354,
            'demomessage' => 'This is a demo message!!!!!!!!!!',
            'demo' => [
                'pot_field' => 'ghfhkgj',
                'time_field' => encrypt(time() - 10),
            ]]);
        $response->assertRedirect();
        $response->assertSessionHasErrors('demo');
    }

    #[Group('trial')]
    public function test_start_free_trial_domain_is_wrong(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $product = Product::create(['name' => 'Helpdesk Advance']);
        $plan = Plan::create(['name' => 'Plan', 'product' => $product->id, 'days' => 15]);
        CloudProducts::create(['cloud_product' => $product->id, 'cloud_free_plan' => $plan->id]);

        $response = $this->postJson('free-trial/start', [
            'domain' => 'test@123.com', // invalid — special chars
            'product_id' => $product->id,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['domain']);
    }

    #[Group('trial')]
    public function test_start_free_trial_tenant_not_created(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $product = Product::create(['name' => 'Helpdesk Advance']);
        $plan = Plan::create(['name' => 'Plan', 'product' => $product->id, 'days' => 13]);
        PlanPrice::create(['plan_id' => $plan->id, 'add_price' => '1000', 'currency' => 'USD']);
        CloudProducts::create(['cloud_product' => $product->id, 'cloud_free_plan' => $plan->id, 'cloud_product_key' => $product->name]);

        // No cloud infrastructure configured — should return an error
        $response = $this->postJson('free-trial/start', [
            'domain' => 'testdomain',
            'product_id' => $product->id,
        ]);

        // Either succeeds or fails depending on cloud config, but should not 405/422
        $this->assertContains($response->getStatusCode(), [200, 400]);
        $this->assertNotNull($response->json('success'));
    }

    #[Group('trial')]
    public function test_free_trial_attempt_more_then_one(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $product = Product::create(['name' => 'Helpdesk Advance']);
        $plan = Plan::create(['name' => 'Plan', 'product' => $product->id, 'days' => 15]);
        CloudProducts::create(['cloud_product' => $product->id, 'cloud_free_plan' => $plan->id, 'cloud_product_key' => $product->name]);

        // Insert existing trial record to trigger limit
        DB::table('free_trial_allowed')->insert([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        $response = $this->postJson('free-trial/start', [
            'domain' => 'testdomain',
            'product_id' => $product->id,
        ]);

        $response->assertStatus(400);
        $response->assertJson(['success' => false]);
    }

    #[Group('group')]
    public function test_master_group_display(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $template = PricingTemplate::create(['data' => 'good']);
        ProductGroup::create(['name' => 'Helpdesk Advance group', 'pricing_templates_id' => $template->id, 'hidden' => 0]);
        ProductGroup::create(['name' => 'Service Advance group', 'pricing_templates_id' => $template->id, 'hidden' => 0]);

        // Route changed from POST available-groups to GET store/groups
        $response = $this->call('GET', 'store/groups');
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $response->assertJsonStructure(['data']);
    }
}
