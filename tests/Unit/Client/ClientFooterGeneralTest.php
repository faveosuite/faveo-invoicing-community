<?php

namespace Tests\Unit\Client;

use App\Http\Controllers\FreeTrailController;
use App\Http\Controllers\Tenancy\TenantController;
use App\Model\Common\FaveoCloud;
use App\Model\Order\Invoice;
use App\Model\Payment\Plan;
use App\Model\Product\CloudProducts;
use App\Model\Product\Product;
use App\User;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Mockery;
use Tests\DBTestCase;

class ClientFooterGeneralTest extends DBTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
        $this->tenantController = Mockery::mock(new TenantController(new Client, new FaveoCloud()));
    }

    /** @group demo */
    public function test_request_demo_required_field_not_given()
    {
        $user = User::factory()->create();
        $this->withoutMiddleware();
        $response = $this->call('POST', 'demo-request');
        $response->assertSessionHasErrors('demoname', 'The name field is required');
    }

    /** @group demo */
    public function test_request_demo_fields_are_given()
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

    /** @group demo */
    public function test_request_demo_spam_detected()
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

    /** @group demo */
    public function test_request_demo_when_spam_name_given()
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

    /** @group trial */
    public function test_start_free_trial_domain_is_wrong()
    {
        $this->withoutMiddleware();
        $response = $this->call('POST', 'first-login', ['domain' => 'test@123.com']);
        $response->assertSessionHasErrors('domain', 'Special characters are not allowed in domain name');
    }

    /** @group trial */
    public function test_start_free_trial_tenant_not_created()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        $product = Product::create(['name' => 'Helpdesk Advance']);
        $plan = Plan::create(['id' => 25, 'name' => 'Hepldesk 1 year', 'product' => $product->id, 'days' => 15]);
        $cloudProduct = CloudProducts::create(['cloud_product' => $product->id, 'cloud_free_plan' => $plan->id, 'cloud_product_key' => $product->name]);
        $invoice = Invoice::factory()->create(['user_id' => $user->id]);
        $response = $this->call('POST', 'first-login', ['domain' => 'test', 'id' => $user->id, 'product' => $product->name]);
        $content = $response->json();
        $this->assertEquals($content['status'], 'false');
    }

    /** @group trial */
    public function test_start_free_trial_tenant_created()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        $product = Product::create(['name' => 'Helpdesk Advance']);
        $plan = Plan::create(['id' => 25, 'name' => 'Hepldesk 1 year', 'product' => $product->id, 'days' => 15]);
        $cloudProduct = CloudProducts::create(['cloud_product' => $product->id, 'cloud_free_plan' => $plan->id, 'cloud_product_key' => $product->name]);
        $invoice = Invoice::factory()->create(['user_id' => $user->id]);

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

    /** @group trial */
    public function test_free_trial_attempt_more_then_one()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        $product = Product::create(['name' => 'Helpdesk Advance']);
        $plan = Plan::create(['id' => 25, 'name' => 'Hepldesk 1 year', 'product' => $product->id, 'days' => 15]);
        $cloudProduct = CloudProducts::create(['cloud_product' => $product->id, 'cloud_free_plan' => $plan->id, 'cloud_product_key' => $product->name]);
        $invoice = Invoice::factory()->create(['user_id' => $user->id]);
        \DB::table('free_trial_allowed')->insert([
            'id' => 1,
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        \DB::table('free_trial_allowed')->insert([
            'id' => 2,
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        $response = $this->call('POST', 'first-login', ['domain' => 'test', 'id' => $user->id, 'product' => $product->name]);
        $content = $response->json();
        $this->assertEquals($content['status'], 'false');
        $this->assertEquals($content['message'], 'It has come to our notice that you have crossed the free trial limit, please delete your existing instances to proceed further.');
    }
}
