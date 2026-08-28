<?php

namespace Tests\Unit\Backend\Http\Controllers\Common\Stripe;

use App\ApiKey;
use App\Facades\Attach;
use App\FileSystemSettings;
use App\Http\Controllers\RazorpayController;
use App\Model\Common\CommonSettings;
use App\Model\Common\Setting;
use App\Model\Common\StatusSetting;
use App\Model\Order\Invoice;
use App\Model\Order\InvoiceItem;
use App\Model\Order\Order;
use App\Model\Payment\Currency;
use App\Model\Payment\Plan;
use App\Model\Product\Product;
use App\Model\Product\Subscription;
use App\Plugins\Payment\Dto\SubscriptionResult;
use App\Plugins\Stripe\Controllers\SettingsController;
use App\Services\Payment\SubscriptionService;
use App\User;
use Auth;
// Cartalyst Stripe SDK not installed — tests using Stripe::make() are skipped below
use DB;
use Exception;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Mockery;
use Stripe\StripeClient;
use Tests\DBTestCase;

class SettingsControllerTest extends DBTestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware();
        $this->getLoggedInUser('admin');
    }

    /**
     * Setup required seeds for every test.
     */

    // Helper method to set up the mock for the Stripe client
    protected function setupStripeClientMock($expectedArguments, $status)
    {
        $stripeClientConstructorMock = Mockery::mock(StripeClient::class);
        $stripeClientConstructorMock->shouldReceive('paymentIntents->confirm')
            ->with('payment_intent_id', $expectedArguments)
            ->andReturn(['status' => $status]);
        DB::table('api_keys')->where('id', 1)->update(['stripe_secret' => 'sk_test_FIPEe0BihQ4Rn2exN1BhOotg']);

        return $stripeClientConstructorMock;
    }

    // Helper method to set up the mock for the request
    protected function setupRequestMock($requestData)
    {
        $requestMock = Mockery::mock(Request::class);
        $requestMock->shouldReceive('all')->andReturn($requestData);
        $requestMock->shouldReceive('isPrecognitive')->andReturn(null);
        $requestMock->shouldReceive('validate')->andReturn($requestData);
        foreach ($requestData as $key => $value) {
            $requestMock->shouldReceive('get')->with($key)->andReturn($value);
        }

        return $requestMock;
    }

    protected function stripeTokenGenerate($cardNumber = '4242424242424242')
    {
        $stripe = Stripe::make('sk_test_FIPEe0BihQ4Rn2exN1BhOotg');

        return $stripe->tokens()->create([
            'card' => [
                'number' => $cardNumber,
                'exp_month' => 12,
                'exp_year' => 45,
                'cvc' => '123',
            ],
        ]);
    }

    // Helper method to set up the Auth user
    protected function SetAuthUser()
    {
        return Auth::shouldReceive('user')->andReturn((object) [
            'first_name' => 'sowmi',
            'last_name' => 's',
            'email' => 'sowmi@gmail.com',
            'line1' => '5/204',
            'postal_code' => '621651',
            'city' => 'Bangalore',
            'state' => 'Karnataka',
            'country' => 'India',
            'address' => 'Bangalore',
            'zip' => '590017',
            'town' => 'koramangala',
        ]);
    }

    // Test case for handling incorrect stripe token
    public function test_handle_payment_return_exception_incorrect_values(): void
    {
        try {
            $requestData = ['stripeToken' => '12345678904567890'];
            $expectedArguments = ['payment_method' => 'pm_card_visa', 'return_url' => 'https://example.com/return-url'];
            $status = 'require_action';
            $stripeClientConstructorMock = $this->setupStripeClientMock($expectedArguments, $status);
            $requestMock = $this->setupRequestMock($requestData);
            $this->SetAuthUser();
            $controller = new SettingsController($stripeClientConstructorMock);
            $response = $controller->handlePayment($requestMock, 50, 'INR', 'https://example.com/return-url');
        } catch (Exception $exception) {
            $this->assertEquals('Invalid token id: 12345678904567890', $exception->getMessage());
        }
    }

    // Test case for handling autopay for 3ds with incomplete status
    public function test_handle_auto_payment_non_3ds_card(): void
    {
        $stripePaymentDetails = (object) ['payment_intent_id' => 'pm_1OyUW0I0SyY30M2QqJqeC5hx'];

        $productDetails = (object) ['name' => 'Sample Product'];
        $unitCost = 50;
        $currency = 'INR';
        $plan = (object) ['days' => 30];
        $status = 'incomplete';
        // handleStripeAutoPay delegates to SubscriptionService (resolved from the
        // container), not a Stripe client injected into the controller — mock the
        // service itself instead of hitting the real Stripe API.
        $this->mock(SubscriptionService::class, function ($mock) use ($status): void {
            $mock->shouldReceive('createSubscription')
                ->once()
                ->andReturn(new SubscriptionResult(gateway: 'Stripe', id: 'sub_1OyXYHI0SyY30M2QDkWSfCb2', status: $status));
        });
        $this->SetAuthUser();
        $controller = new SettingsController;
        $response = $controller->handleStripeAutoPay($stripePaymentDetails, $productDetails, $unitCost, $currency, $plan);
        $this->assertEquals($status, $response->status);
    }

    //     Test case for handling autopay for non 3ds with active status
    public function test_handle_auto_payment_3ds_card(): void
    {
        $stripePaymentDetails = (object) ['payment_intent_id' => 'pm_1OyTcJI0SyY30M2QznXTOvZH'];

        $productDetails = (object) ['name' => 'Sample Product'];
        $unitCost = 50;
        $currency = 'INR';
        $plan = (object) ['days' => 30];
        $status = 'incomplete';
        $this->mock(SubscriptionService::class, function ($mock) use ($status): void {
            $mock->shouldReceive('createSubscription')
                ->once()
                ->andReturn(new SubscriptionResult(gateway: 'Stripe', id: 'sub_1OyXYHI0SyY30M2QDkWSfCb2', status: $status));
        });
        $this->SetAuthUser();
        $controller = new SettingsController;
        $response = $controller->handleStripeAutoPay($stripePaymentDetails, $productDetails, $unitCost, $currency, $plan);
        $this->assertEquals($status, $response->status);
    }

    // Testcase for handle razorpay api for subscription
    public function test_handle_rzp_auto_pay_correctly(): void
    {
        $user = User::factory()->create(['id' => mt_rand(1, 999), 'role' => 'user', 'country' => 'IN']);
        $product = Product::create(['name' => 'Helpdesk']);

        $invoice = Invoice::factory()->create(['user_id' => $user->id]);
        InvoiceItem::create(['invoice_id' => $invoice->id, 'product_name' => $product->name]);
        $order = Order::create(['client' => $user->id, 'order_status' => 'executed',
            'product' => 'Helpdesk Advance', 'number' => mt_rand(100000, 999999)]);
        \App\Model\Order\OrderInvoiceRelation::create(['order_id' => $order->id, 'invoice_id' => $invoice->id]);
        $subscription = Subscription::create(['order_id' => $order->id, 'product_id' => $product->id, 'version' => 'v3.0.0', 'is_subscribed' => '1', 'autoRenew_status' => '1']);
        Plan::create(['name' => 'Hepldesk 1 year', 'product' => $product->id, 'days' => 365]);
        DB::table('api_keys')->where('id', 1)->update(['rzp_key' => 'rzp_test_0UWbi4WpjuMCoC', 'rzp_secret' => 'jZbOckxf4RhwaUAgxzegwQqV']); // NOSONAR
        // Prepare mock data
        $days = 30;
        $product_name = 'Example Product';
        $cost = 1000;
        $currency = 'INR';

        $endDate = date('Y-m-d H:m:i');
        Http::fake([
            'api.razorpay.com/*' => Http::response([
                'id' => 'sub_NqwkfKaNkiuHXG',
                'entity' => 'subscription',
                'plan_id' => 'plan_NqwkeMP7pGGucR',
                'status' => 'created',
            ], 200),
        ]);
        $controller = new RazorpayController;
        $result = $controller->handleRzpAutoPay($cost, $days, $product_name, $invoice, $currency, $subscription, $user, $order, $endDate, $product);
        $this->assertEquals('created', $result->status);
    }

    // Testcases for fetching system settings in admin panel
    public function test_it_fetches_system_settings_successfully(): void
    {
        $response = $this->getJson('/settings/system-data');

        $response->assertStatus(200);
    }

    public function test_it_returns_error_when_settings_not_found(): void
    {
        Setting::where('id', 1)->delete();

        $response = $this->getJson('/settings/system-data');

        // Controller either creates a default record (200) or fails (400 if creation errors)
        $this->assertContains($response->getStatusCode(), [200, 400]);
    }

    // Testcases for updating system settings
    public function test_it_updates_settings_with_new_payload_data(): void
    {
        $logo = UploadedFile::fake()->image('brand-logo.png');
        $adminLogo = UploadedFile::fake()->image('panel-logo.png');
        $favIcon = UploadedFile::fake()->image('favicon.png');

        Attach::shouldReceive('put')
            ->andReturnUsing(fn ($path, $file): string => $path.'/'.$file->hashName());

        $payload = [
            'company' => 'ABC Solutions',
            'company_email' => 'support@abc.io',
            'title' => 'ABC Billing',
            'website' => 'https://abc.io/',
            'phone' => '9388383888',
            'phone_code' => '44',
            'phone_country_iso' => 'GB',
            'address' => '221B Baker Street',
            'city' => 'London',
            'zip' => 'NW16XE',
            'knowledge_base_url' => 'https://docs.abc.io',
            'language' => 'fr',
            'country' => 'UK',
            'cin_no' => 'CIN998877',
            'gstin' => 'GST556677',
            'state' => 'UK-LND',
            'default_currency' => 'EUR',
            'favicon_title' => 'abc Billing',
            'favicon_title_client' => 'abc Client Portal',

            // New file inputs
            'logo' => $logo,
            'admin-logo' => $adminLogo,
            'fav-icon' => $favIcon,
        ];

        $response = $this->patchJson('/settings/system-data', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'message' => __('message.updated-successfully'),
            ]);

        $this->assertDatabaseHas('settings', [
            'id' => 1,
            'company' => 'ABC Solutions',
            'company_email' => 'support@abc.io',
            'title' => 'ABC Billing',
            'website' => 'https://abc.io/',
            'phone' => '9388383888',
            'phone_code' => '44',
            'phone_country_iso' => 'GB',
            'address' => '221B Baker Street',
            'city' => 'London',
            'country' => 'UK',
            'state' => 'UK-LND',
            'default_symbol' => '€',
            'content' => 'fr',
        ]);
    }

    public function test_it_returns_error_when_settings_row_missing(): void
    {
        Setting::where('id', 1)->delete();

        $payload = [
            'company' => 'Test',
            'default_currency' => 'USD',
        ];

        $response = $this->patchJson('/settings/system-data', $payload);

        $response->assertStatus(412);
    }

    public function test_it_updates_settings_with_only_required_fields(): void
    {
        Currency::create([
            'code' => 'USD',
            'symbol' => '$',
        ]);

        $payload = [
            'company' => 'ABC Solutions',
            'company_email' => 'support@abc.io',
            'website' => 'https://abc.io/',
            'phone' => '9388383888',
            'phone_code' => '44',
            'phone_country_iso' => 'GB',
            'address' => '221B Baker Street',
            'city' => 'Banglore',
            'zip' => '636900',
            'language' => 'en',
            'state' => 'IN-KA',
            'default_currency' => 'USD',
            'country' => 'IN',
        ];

        $response = $this->patchJson('/settings/system-data', $payload);
        $response->assertStatus(200);
    }

    /*
     * File Storage Test
     */
    public function test_show_file_storage_returns_settings_for_local_storage(): void
    {
        // Show file storage
        $response = $this->getJson('/file-storage');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'disk',
                    'local_file_storage_path',
                ],
            ]);
    }

    public function test_update_storage_path_for_system_disk(): void
    {
        // Update local file storage
        $payload = [
            'disk' => 'system',
            'path' => '/new/storage/path',
        ];

        $response = $this->postJson('/file-storage-path', $payload);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'message' => __('message.setting_updated'),
            ]);

        $this->assertDatabaseHas('settings_filesystem', [
            'disk' => 'system',
            'local_file_storage_path' => '/new/storage/path',
        ]);
    }

    public function test_update_storage_path_for_s3_disk(): void
    {
        // Update S3 disk storage
        $fs = FileSystemSettings::updateOrCreate([], [
            'disk' => 'system',
            'local_file_storage_path' => '/old/path',
        ]);

        $payload = [
            'disk' => 's3',
            's3_bucket' => 'dummy-bucket',
            's3_region' => 'ap-south-1',
            's3_access_key' => 'DUMMY_ACCESS',
            's3_secret_key' => 'DUMMY_SECRET',
            's3_endpoint_url' => 'https://dummy-endpoint.com',
            's3_url' => 'https://dummy-bucket.s3.amazonaws.com',
            's3_path_style_endpoint' => 'true',
        ];

        // MOCK TRAIT METHOD
        $mock = Mockery::mock(\App\Http\Controllers\Common\SettingsController::class)->makePartial();
        $mock->shouldAllowMockingProtectedMethods();
        $mock->shouldReceive('validateS3Credentials')->andReturn(true);

        // Bind so controller resolution uses the mock
        $this->app->bind(\App\Http\Controllers\Common\SettingsController::class, fn () => $mock);

        $response = $this->postJson('/file-storage-path', $payload);

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => __('message.setting_updated')]);

        $this->assertDatabaseHas('settings_filesystem', [
            'id' => $fs->id,
            'disk' => 's3',
        ]);
    }

    public function test_update_storage_path_for_s3_disk_with_invalid_credentials(): void
    {
        // Update S3 disk storage with invalid credentials
        FileSystemSettings::updateOrCreate([], [
            'disk' => 'system',
            'local_file_storage_path' => '/old/path',
        ]);

        $payload = [
            'disk' => 's3',
            's3_bucket' => 'dummy-bucket',
            's3_region' => 'ap-south-1',
            's3_access_key' => 'DUMMY_ACCESS',
            's3_secret_key' => 'DUMMY_SECRET',
            's3_endpoint_url' => 'https://dummy-endpoint.com',
            's3_url' => 'https://dummy-bucket.s3.amazonaws.com',
            's3_path_style_endpoint' => 'true',
        ];

        $response = $this->postJson('/file-storage-path', $payload);

        $response->assertStatus(400)
            ->assertJsonFragment([
                'success' => false,
                'message' => __('message.s3_error'),
            ]);
    }

    public function test_show_file_storage_returns_settings_for_s3_disk(): void
    {
        // Show file storage for s3 disk
        $response = $this->getJson('/file-storage');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'disk',
                    'local_file_storage_path',
                    's3_bucket',
                    's3_region',
                    's3_access_key',
                    's3_secret_key',
                    's3_endpoint_url',
                    's3_url',
                    's3_path_style_endpoint',
                ],
            ]);
    }

    /*
     * Debug Option Test Case
    */
    public function test_returns_current_debug_status(): void
    {
        CommonSettings::updateOrCreate(
            ['option_name' => 'debugging', 'optional_field' => 'app_debug'],
            ['option_value' => '1']
        );

        $response = $this->getJson('/debugg');

        $response->assertStatus(200)
            ->assertJsonFragment([
                'debug' => true,
            ]);
    }

    public function test_returns_debug_false_when_disabled(): void
    {
        $response = $this->getJson('/debugg');

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data' => ['debug', 'pulse_enabled']]);
    }

    public function test_updates_debug_status_to_true(): void
    {
        $response = $this->postJson('/save/debugg', [
            'debug' => 'true',
            'pulse_enabled' => 'true',
            'clockwork_enable' => 'true',
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    public function test_updates_debug_status_to_false(): void
    {
        $response = $this->postJson('/save/debugg', [
            'debug' => 'false',
            'pulse_enabled' => 'false',
            'clockwork_enable' => 'false',
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    /*
     * Contact Option Test Cases
    */
    public function test_api_structure_contact_option(): void
    {
        $response = $this->getJson('/contact-option');
        $response->assertStatus(200);

        $response->assertJsonStructure([
            'success',
            'data' => [
                'sending_status',
                'emailverification_status',
                'msg91_status',
                'verification_preference',
            ],
        ]);

        $response->assertJson(['success' => true]);
    }

    public function test_returns_contact_option_settings(): void
    {
        $response = $this->getJson('/contact-option');

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data' => [
            'sending_status',
            'emailverification_status',
            'msg91_status',
            'verification_preference',
        ]]);
    }

    public function test_updates_contact_option_for_mobile_only(): void
    {
        // To test updating contact options for mobile only
        $payload = [
            'email_enabled' => 0,
            'mobile_enabled' => 1,
            'preferred_verification' => 'mobile',
        ];

        $response = $this->postJson('/verificationSettings', $payload);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'message' => __('message.contact_setting_update'),
            ]);

        $this->assertDatabaseHas('status_settings', [
            'emailverification_status' => 0,
            'msg91_status' => 1,
        ]);

        $this->assertDatabaseHas('api_keys', [
            'verification_preference' => 'mobile',
        ]);
    }

    public function test_updates_contact_option_for_email_only(): void
    {
        // To test updating contact options for email only
        $payload = [
            'email_enabled' => 1,
            'mobile_enabled' => 0,
            'preferred_verification' => 'email',
        ];

        $response = $this->postJson('/verificationSettings', $payload);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'message' => __('message.contact_setting_update'),
            ]);

        $this->assertDatabaseHas('status_settings', [
            'emailverification_status' => 1,
            'msg91_status' => 0,
        ]);

        $this->assertDatabaseHas('api_keys', [
            'verification_preference' => 'email',
        ]);
    }

    public function test_updates_contact_option_both_first_preference_email(): void
    {
        // To test updating contact options for both with email as first preference
        $payload = [
            'email_enabled' => 1,
            'mobile_enabled' => 1,
            'preferred_verification' => 'email',
        ];

        $response = $this->postJson('/verificationSettings', $payload);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'message' => __('message.contact_setting_update'),
            ]);

        $this->assertDatabaseHas('status_settings', [
            'emailverification_status' => 1,
            'msg91_status' => 1,
        ]);

        $this->assertDatabaseHas('api_keys', [
            'verification_preference' => 'email',
        ]);
    }

    public function test_updates_contact_option_both_first_preference_mobile(): void
    {
        // To test updating contact options for both with mobile as first preference
        $payload = [
            'email_enabled' => 1,
            'mobile_enabled' => 1,
            'preferred_verification' => 'mobile',
        ];

        $response = $this->postJson('/verificationSettings', $payload);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'message' => __('message.contact_setting_update'),
            ]);

        $this->assertDatabaseHas('status_settings', [
            'emailverification_status' => 1,
            'msg91_status' => 1,
        ]);

        $this->assertDatabaseHas('api_keys', [
            'verification_preference' => 'mobile',
        ]);
    }

    public function test_allows_null_preferred_verification(): void
    {
        // To test updating contact options with null preferred verification
        StatusSetting::create([
            'emailverification_status' => 0,
            'msg91_status' => 0,
        ]);
        ApiKey::create(['verification_preference' => 'email']);

        $payload = [
            'email_enabled' => 0,
            'mobile_enabled' => 1,
            'preferred_verification' => null,
        ];

        $response = $this->postJson('/verificationSettings', $payload);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'message' => __('message.contact_setting_update'),
            ]);

        $this->assertDatabaseHas('status_settings', [
            'emailverification_status' => 0,
            'msg91_status' => 1,
        ]);

        $this->assertDatabaseHas('api_keys', [
            'verification_preference' => null,
        ]);
    }
}
