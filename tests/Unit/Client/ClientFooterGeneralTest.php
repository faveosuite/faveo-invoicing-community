<?php

namespace Tests\Unit\Client;

use App\Http\Controllers\FreeTrailController;
use App\Http\Controllers\Tenancy\TenantController;
use App\Model\Common\FaveoCloud;
use App\Model\Common\PricingTemplate;
use App\Model\Order\Invoice;
use App\Model\Payment\Plan;
use App\Model\Payment\PlanPrice;
use App\Model\Product\CloudProducts;
use App\Model\Product\Product;
use App\Model\Product\ProductGroup;
use App\User;
use DB;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Mockery;
use PHPUnit\Framework\Attributes\Group;
use Tests\DBTestCase;

class ClientFooterGeneralTest extends DBTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
        $this->tenantController = Mockery::mock(new TenantController(new Client, new FaveoCloud));
    }

    #[Group('demo')]
    public function test_request_demo_required_field_not_given(): void
    {
        User::factory()->create();
        $this->withoutMiddleware();
        $response = $this->call('POST', 'demo-request');
        $response->assertSessionHasErrors('demoname', 'The name field is required');
    }

    #[Group('demo')]
    public function test_request_demo_fields_are_given(): void
    {
        $this->withoutMiddleware();
        $response = $this->call('POST', 'demo-request', ['demoname' => 'test',
            'demoemail' => 'test@gmail.com',
            'country_code' => '91',
            'Mobile' => 4335544354,
            'demomessage' => 'This is a demo message',
            'demo' => [
                'pot_field' => '',     // valid
                'time_field' => encrypt(time() - 10), // valid
            ], ]);
        $response->assertStatus(200);
        $response->assertJson(['message' => 'Your message was sent successfully. Thanks.']);
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
                'pot_field' => 'ghfhkgj',     // valid
                'time_field' => encrypt(time() - 10), // valid
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
                'pot_field' => 'ghfhkgj',     // valid
                'time_field' => encrypt(time() - 10), // valid
            ]]);
        $response->assertRedirect();
        $response->assertSessionHasErrors('demo');
    }

    #[Group('trial')]
    public function test_start_free_trial_domain_is_wrong(): void
    {
        $this->withoutMiddleware();
        $response = $this->call('POST', 'first-login', ['domain' => 'test@123.com']);
        $response->assertSessionHasErrors('domain', 'Special characters are not allowed in domain name');
    }

    #[Group('trial')]
    public function test_start_free_trial_tenant_not_created(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        $product = Product::create(['name' => 'Helpdesk Advance']);
        $plan = Plan::create(['id' => 25, 'name' => 'Hepldesk 1 year', 'product' => $product->id, 'days' => 13]);
        PlanPrice::create(['plan_id' => $plan->id, 'add_price' => '1000', 'currency' => 'USD']);
        CloudProducts::create(['cloud_product' => $product->id, 'cloud_free_plan' => $plan->id, 'cloud_product_key' => $product->name]);
        Invoice::factory()->create(['user_id' => $user->id]);
        $response = $this->call('POST', 'first-login', ['domain' => 'test', 'id' => $user->id, 'product' => $product->name]);
        $content = $response->json();

        $this->assertEquals($content['status'], 'false');
    }

    #[Group('trial')]
    public function test_start_free_trial_tenant_created(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        $product = Product::create(['name' => 'Helpdesk Advance']);
        $plan = Plan::create(['id' => 25, 'name' => 'Hepldesk 1 year', 'product' => $product->id, 'days' => 15]);
        PlanPrice::create(['plan_id' => $plan->id, 'add_price' => '1000', 'currency' => 'USD']);
        CloudProducts::create(['cloud_product' => $product->id, 'cloud_free_plan' => $plan->id, 'cloud_product_key' => $product->name]);
        Invoice::factory()->create(['user_id' => $user->id]);
        $tenantControllerMock = Mockery::mock(TenantController::class);
        $requestMock = Mockery::mock(Request::class);
        $requestMock->domain = 'example.com';

        $request = new Request([
            'domain' => 'test',
            'id' => $user->id,
            'product' => $product->name,
        ]);
        $tenantControllerMock->shouldReceive('createTenant')
            ->withAnyArgs()
            ->andReturn(['status' => true, 'message' => trans('message.cloud_created_successfully')]);

        $controller = new FreeTrailController($tenantControllerMock);

        $result = $controller->firstLoginAttempt($request);
        $this->assertTrue($result['status']);
        $this->assertEquals(' You will receive the login credentials on your registered email', $result['message']);
    }

    #[Group('trial')]
    public function test_free_trial_attempt_more_then_one(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        $product = Product::create(['name' => 'Helpdesk Advance']);
        $plan = Plan::create(['id' => 25, 'name' => 'Hepldesk 1 year', 'product' => $product->id, 'days' => 15]);
        CloudProducts::create(['cloud_product' => $product->id, 'cloud_free_plan' => $plan->id, 'cloud_product_key' => $product->name]);
        Invoice::factory()->create(['user_id' => $user->id]);
        DB::table('free_trial_allowed')->insert([
            'id' => 1,
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        DB::table('free_trial_allowed')->insert([
            'id' => 2,
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        $response = $this->call('POST', 'first-login', ['domain' => 'test', 'id' => $user->id, 'product' => $product->name]);
        $content = $response->json();
        $this->assertEquals($content['success'], actual: false);
        $this->assertEquals($content['message'], 'It has come to our notice that you have crossed the free trial limit, please delete your existing instances to proceed further.');
    }

    #[Group('group')]
    public function test_master_group_display(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        $template = PricingTemplate::create(['data' => 'good']);
        $group1 = ProductGroup::create(['name' => 'Helpdesk Advance group', 'pricing_templates_id' => $template->id, 'hidden' => 0]);
        ProductGroup::create(['name' => 'Service Advance group', 'pricing_templates_id' => $template->id, 'hidden' => 0]);
        $response = $this->call('POST', 'available-groups');
        $this->assertEquals($response['message'], 'Success');
        $data = $response['data'][$group1->id];
        $this->assertEquals($data['name'], 'Helpdesk Advance group');
    }
}
