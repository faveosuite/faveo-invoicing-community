<?php

namespace Tests\Unit\Admin\User;

use App\Http\Controllers\User\ClientController;
use App\Model\Order\Invoice;
use App\Model\Order\InvoiceItem;
use App\Model\Order\Order;
use App\Model\Order\Payment;
use App\Model\Product\Product;
use App\ReportColumn;
use App\User;
use App\UserLinkReport;
use Illuminate\Http\Request;
use Tests\DBTestCase;

class ClientControllerTest extends DBTestCase
{
    private $classObject;

    protected function setUp(): void
    {
        parent::setUp();
        $this->classObject = new ClientController();
    }

    #[Group('User')]
    public function test_getBaseQueryForUserSearch_whenCountryIsPresentInTheRequest_filtersResultByThatCountry()
    {
        $request = new Request(['country' => 'US']);
        User::factory()->create(['country' => 'US']);
        User::factory()->create(['country' => 'IN']);
        $methodResponse = $this->getPrivateMethod($this->classObject, 'getBaseQueryForUserSearch', [$request]);
        $users = $methodResponse->get();
        $this->assertEquals(1, $users->count());
        $this->assertEquals('UNITED STATES', $users->first()->country);
    }

    #[Group('User')]
    public function test_getBaseQueryForUserSearch_whenIndustryIsPresentInTheRequest_filtersResultByThatIndustry()
    {
        $request = new Request(['industry' => 'testOne']);
        $userOne = User::factory()->create(['bussiness' => 'testOne']);
        User::factory()->create(['bussiness' => 'testTwo']);
        $methodResponse = $this->getPrivateMethod($this->classObject, 'getBaseQueryForUserSearch', [$request]);
        $users = $methodResponse->get();
        $this->assertEquals(1, $users->count());
        $this->assertEquals($userOne->id, $users->first()->id);
    }

    #[Group('User')]
    public function test_getBaseQueryForUserSearch_whenRoleIsPresentInTheRequest_filtersResultByThatRole()
    {
        $request = new Request(['role' => 'user']);
        $userOne = User::factory()->create(['role' => 'user']);
        User::factory()->create(['role' => 'admin']);
        $methodResponse = $this->getPrivateMethod($this->classObject, 'getBaseQueryForUserSearch', [$request]);
        $users = $methodResponse->get();
        $this->assertEquals(1, $users->count());
        $this->assertEquals($userOne->id, $users->first()->id);
    }

    #[Group('User')]
    public function test_getBaseQueryForUserSearch_whenPositionIsPresentInTheRequest_filtersResultByThatPosition()
    {
        $request = new Request(['position' => 'positionOne']);
        $userOne = User::factory()->create(['position' => 'positionOne']);
        User::factory()->create(['position' => 'positionTwo']);
        $methodResponse = $this->getPrivateMethod($this->classObject, 'getBaseQueryForUserSearch', [$request]);
        $users = $methodResponse->get();
        $this->assertEquals(1, $users->count());
        $this->assertEquals($userOne->id, $users->first()->id);
    }

    #[Group('User')]
    public function test_getBaseQueryForUserSearch_whenAccountManagerIsPresentInTheRequest_filtersResultByThatAccountManager()
    {
        $managerOneId = User::factory()->create()->id;
        $request = new Request(['actmanager' => $managerOneId]);
        $managerTwoId = User::factory()->create()->id;
        $userOne = User::factory()->create(['account_manager' => $managerOneId]);
        User::factory()->create(['account_manager' => $managerTwoId]);
        $methodResponse = $this->getPrivateMethod($this->classObject, 'getBaseQueryForUserSearch', [$request]);
        $users = $methodResponse->get();
        $this->assertEquals(1, $users->count());
        $this->assertEquals($userOne->id, $users->first()->id);
    }

    #[Group('User')]
    public function test_getBaseQueryForUserSearch_whenSalesManagerIsPresentInTheRequest_filtersResultByThatSalesManager()
    {
        $managerOneId = User::factory()->create()->id;
        $request = new Request(['salesmanager' => $managerOneId]);
        $managerTwoId = User::factory()->create()->id;
        $userOne = User::factory()->create(['manager' => $managerOneId]);
        User::factory()->create(['manager' => $managerTwoId]);
        $methodResponse = $this->getPrivateMethod($this->classObject, 'getBaseQueryForUserSearch', [$request]);
        $users = $methodResponse->get();
        $this->assertEquals(1, $users->count());
        $this->assertEquals($userOne->id, $users->first()->id);
    }

    #[Group('User')]
    public function test_getBaseQueryForUserSearch_GivesPhoneNumberFormattedWithCountryCode()
    {
        $request = new Request(['country' => 'US']);
        User::factory()->create(['country' => 'US', 'mobile_code' => '1', 'mobile' => '9087654321']);
        $methodResponse = $this->getPrivateMethod($this->classObject, 'getBaseQueryForUserSearch', [$request]);
        $users = $methodResponse->get();
        $this->assertEquals('+1 9087654321', $users->first()->mobile);
    }

    #[Group('User')]
    public function test_getBaseQueryForUserSearch_whenMobilestatusIsPresentInTheRequest_filtersResultByThatMobilestatus()
    {
        $request = new Request(['mobile_verified' => 1]);
        $methodResponse = $this->getPrivateMethod($this->classObject, 'getBaseQueryForUserSearch', [$request]);
        $users = $methodResponse->get();
        $this->assertEquals(1, $users->count());
        $firstUser = $users->first();
        $this->assertTrue($firstUser->mobile_verified == 1);
    }

    public function test_getBaseQueryForUserSearch_whenEmailstatusIsPresentInTheRequest_filtersResultByThatEmailstatus()
    {
        $request = new Request(['active' => 1]);
        $methodResponse = $this->getPrivateMethod($this->classObject, 'getBaseQueryForUserSearch', [$request]);
        $users = $methodResponse->get();
        $this->assertEquals(1, $users->count());
        $firstUser = $users->first();
        $this->assertTrue($firstUser->active == 1);
    }

    #[Group('User')]
    public function test_getBaseQueryForUserSearch_when2FAstatusIsPresentInTheRequest_filtersResultByThat2FAstatus()
    {
        $request = new Request(['is_2fa_enabled' => 0]);
        $methodResponse = $this->getPrivateMethod($this->classObject, 'getBaseQueryForUserSearch', [$request]);
        $users = $methodResponse->get();
        $this->assertEquals(1, $users->count());
        $firstUser = $users->first();
        $this->assertTrue($firstUser->is_2fa_enabled == 0);
    }

    #[Group('User')]
    public function test_Admin_error_when_address_is_not_present()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);
        $this->withoutMiddleware();
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $response = $this->call('POST', url('clients'), [
            'first_name' => 'Abc',
            'user_name' => 'demopass',
            'active' => '1',
            'mobile_verified' => 1,
            'last_name' => 'Xyz',
            'company' => 'demo',
            'country' => 'IN',
            'email' => 'test@test.com',
            'state' => 'karnataka',
            'mobile' => '9898789887',
            'mobile_code' => '91',
            'role' => 'user',
            'bussiness' => 'abcd',
            'company_type' => 'public_company',
            'company_size' => '2-50',
            'timezone_id' => 79,
            'currency' => 'INR',
            'town' => 'trichy',
            'zip' => '621651',
        ]);
        $response->assertRedirect();
        $response->assertStatus(302);
        $response->assertJsonValidationErrors('The address field is required');
    }

    #[Group('User')]
    public function test_when_admin_user_creation_successFull()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);
        $this->withoutMiddleware();
        $response = $this->call('POST', url('clients'), [
            'first_name' => 'Abc',
            'user_name' => 'demopass',
            'active' => '1',
            'mobile_verified' => 1,
            'last_name' => 'Xyz',
            'company' => 'demo',
            'country' => 'IN',
            'email' => 'test@test.com',
            'state' => 'karnataka',
            'address' => 'Home',
            'mobile' => '9898789887',
            'mobile_code' => '91',
            'role' => 'user',
            'bussiness' => 'abcd',
            'company_type' => 'public_company',
            'company_size' => '2-50',
            'timezone_id' => 79,
            'currency' => 'INR',
            'town' => 'trichy',
            'zip' => '621651',
        ]);
        $response->assertRedirect();
        $response->assertSessionHas('success', 'Saved Successfully');
    }

    #[Group('User')]
    public function test_admin_when_zip_is_given_wrong()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);
        $this->withoutMiddleware();
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $response = $this->call('POST', url('clients'), [
            'first_name' => 'Abc',
            'user_name' => 'demopass',
            'active' => '1',
            'mobile_verified' => 1,
            'last_name' => 'Xyz',
            'company' => 'demo',
            'country' => 'IN',
            'email' => 'test@test.com',
            'state' => 'karnataka',
            'address' => 'Home',
            'mobile' => '9898789887',
            'mobile_code' => '91',
            'role' => 'user',
            'bussiness' => 'abcd',
            'company_type' => 'public_company',
            'company_size' => '2-50',
            'timezone_id' => 79,
            'currency' => 'INR',
            'town' => 'trichy',
            'zip' => '621@#$#651',
        ]);
        $response->assertRedirect();
        $response->assertStatus(302);
        $response->assertJsonValidationErrors('he zip/postal code in invalid');
    }

    #[Group('User')]
    public function test_add_columns_to_db_successfully()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);
        $response = UserLinkReport::create([
            'user_id' => $admin->id,
            'column_id' => '1',
            'type' => 'users',
        ]);
        $this->assertDatabaseHas('users_link_reports', ['user_id' => $admin->id]);
    }

    #[Group('User')]
    public function test_get_columns_with_user_columns()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);
        $reportColumn = ReportColumn::create(['type' => 'orders', 'key' => 'order_id']);

        $userlink = UserLinkReport::create([
            'user_id' => $admin->id,
            'column_id' => '1',
            'type' => 'orders',
        ]);

        $response = $this->call('GET', 'get-columns');
        $response->assertStatus(302);
    }

    #[Group('User')]
    public function test_when_editing_user()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);
        $this->withoutMiddleware();
        $user = User::factory()->create();
        $response = $this->call('PATCH', url('clients/'.$user->id), [
            'first_name' => $user->first_name,
            'user_name' => $user->user_name,
            'active' => $user->active,
            'mobile_verified' => $user->mobile_verified,
            'last_name' => $user->last_name,
            'company' => $user->company,
            'country' => $user->country,
            'email' => 'test@test.com',
            'state' => $user->state,
            'address' => $user->address,
            'mobile' => $user->mobile,
            'mobile_code' => $user->mobile_code,
            'role' => $user->role,
            'bussiness' => $user->bussiness,
            'company_type' => $user->company_type,
            'company_size' => $user->company_size,
            'timezone_id' => $user->timezone_id,
            'currency' => $user->currency,
            'town' => $user->town,
            'zip' => $user->zip,
        ]);
        $updatedUser = \DB::table('users')->where('id', $user->id)->first();
        $response->assertRedirect();
        $response->assertSessionHas('success', 'Updated Successfully');
        $this->assertTrue($updatedUser->email === 'test@test.com');
    }

    #[Group('User')]
    public function test_get_active_inactive_label()
    {
        $mobileActive = 1;
        $emailActive = 0;
        $twoFaActive = 1;
        $response = $this->getPrivateMethod($this->classObject, 'getActiveLabel', [$mobileActive, $emailActive, $twoFaActive]);

        $this->assertEquals("<i class='fas fa-envelope'  style='color:red'  <label data-toggle='tooltip' style='font-weight:500;' data-placement='top' title='Unverified email'> </label></i>&nbsp;&nbsp;<i class='fas fa-phone'  style='color:green'  <label data-toggle='tooltip' style='font-weight:500;' data-placement='top' title='Mobile verified'></label></i>&nbsp;&nbsp;<i class='fas fa-qrcode'  style='color:green'  <label data-toggle='tooltip' style='font-weight:500;' data-placement='top' title='2FA Enabled'> </label></i>", $response);
    }

    #[Group('User')]
    public function test_show_individual_user()
    {
        $user = User::factory()->create(['role' => 'admin']);
        $this->actingAs($user);
        $this->withoutMiddleware();
        $product = Product::factory()->create();
        $invoice = Invoice::factory()->create(['user_id' => $user->id]);
        $invoiceItem = InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'product_name' => 'Helpdesk Advance',
            'regular_price' => 10000,
            'quantity' => 1,
            'tax_name' => 'CGST+SGST',
            'tax_percentage' => 18,
            'subtotal' => 11800,
            'domain' => 'faveo.com',
            'plan_id' => 1,
        ]);
        $order = Order::factory()->create(['invoice_id' => $invoice->id,
            'invoice_item_id' => $invoiceItem->id, 'client' => $user->id, 'product' => $product->id]);
        Payment::create(['invoice_id' => $invoice->id, 'user_id' => $user->id, 'amount' => '50000']);

        $response = $this->call('GET', url('clients/'.$user->id));
        $data = $response->original->gatherData();
        $response->assertViewIs('themes.default1.user.client.show');
        $response->assertStatus(200);
        $response->assertViewHas('client');
        $this->assertEquals(10000, $data['invoiceSum']);
        $this->assertEquals(50000, $data['amountReceived']);
    }

    #[Group('User')]
    public function test_get_user_in_table()
    {
        $user = User::factory()->create(['role' => 'admin']);
        $this->actingAs($user);
        $this->withoutMiddleware();
        User::factory(10)->create();
        $response = $this->call('GET', 'get-clients');
        $response->assertStatus(200);
        $response->assertJsonStructure(['draw', 'recordsTotal', 'recordsFiltered', 'data' => ['*' => ['id',
            'first_name',
            'last_name',
            'email',
            'mobile',
            'name',
            'country',
            'created_at',
            'mobile_verified',
            'email_verified',
            'is_2fa_enabled',
            'role',
            'position',
            'checkbox',
            'company',
            'action'],
        ],
        ]
        );
    }
}
