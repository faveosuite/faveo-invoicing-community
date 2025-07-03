<?php

namespace Tests\Unit\Client;

use App\Model\Common\StatusSetting;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use phpmock\MockBuilder;
use Tests\DBTestCase;

class RegistrationTest extends DBTestCase
{
    use DatabaseTransactions;

    private $address;

    private $mock;

    private function setUpServerVariable($ip, $address, $content)
    {
        global $_SERVER;
        $this->address = $_SERVER;
        $_SERVER['HTTP_CLIENT_IP'] = $ip;
        $_SERVER['REMOTE_ADDR'] = $address;

        $builder = new MockBuilder();
        $builder->setNamespace('Illuminate\Foundation\Auth')
                ->setName('file_get_contents')
                ->setFunction(function () use ($content) {
                    return $content;
                });

        $this->mock = $builder->build();
        $this->mock->disable();
    }

    private function tearDownServerVariable()
    {
        global $_SERVER;
        $_SERVER = $this->address;
        $this->mock->disable();
    }

    #[Group('postRegister')]
    public function test_when_user_registers_emailAndUsername_not_given()
    {
        $user = User::factory()->create();
        $this->withoutMiddleware();
        $response = $this->call('POST', 'auth/register', ['first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'company' => $user->company,
            'bussiness' => $user->bussiness,
            'company_type' => $user->company_type,
            'company_size' => $user->company_size,
            'country' => $user->country,
            'mobile_code' => '91',
            'mobile' => '7418934527',
            'address' => $user->address,
            'town' => $user->town,
            'state' => $user->state,
            'zip' => $user->zip,
            'password' => $user->password,
            'password_confirmation' => $user->password,
        ]);
        $response->assertSessionHasErrors('email', 'The email field is required.');
    }

    #[Group('postRegister')]
    public function test_postRegister_whenPasswordDoesNotMatch()
    {
        $this->setUpServerVariable('192.168.12.12', 'someaddress', 'IN');
        $user = User::factory()->create(['bussiness' => 'Accounting', 'mobile_code' => 91]);
        $response = $this->call('POST', 'auth/register', ['first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => 'test@gmail.com',
            'company' => $user->company,
            'bussiness' => 'Accounting',
            'company_type' => $user->company_type,
            'company_size' => $user->company_size,
            'country' => 'IN',
            'mobile_code' => '91',
            'mobile' => '9901541239',
            'address' => $user->address,
            'town' => $user->town,
            'state' => $user->state,
            'zip' => $user->zip,
            'user_name' => 'testuser11',
            'ip' => $user->ip,
            'password' => $user->password,
            'password_confirmation' => 'adsadsd',
            'terms' => 'on',
        ]);
        $errors = session('errors');
        $response->assertStatus(302);
        // $this->assertEquals($errors->get('password_confirmation')[0], 'The password confirmation and password must match.');
        $this->mock->disable();
        $this->tearDownServerVariable();
    }

    /** @group postRegister */
    public function test_when_confirm_password_does_not_match()
    {
        $user = User::factory()->create();
        $this->withoutMiddleware();
        $response = $this->call('POST', 'auth/register', ['first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => 'santhanu@gmail.com',
            'company' => $user->company,
            'bussiness' => $user->bussiness,
            'company_type' => $user->company_type,
            'company_size' => $user->company_size,
            'country' => $user->country,
            'mobile_code' => '91',
            'mobile' => '7418934526',
            'address' => $user->address,
            'town' => $user->town,
            'state' => $user->state,
            'zip' => $user->zip,
            'password' => $user->password,
            'password_confirmation' => 'santhanu',
        ]);
        $response->assertSessionHasErrors('password_confirmation');
    }

    /** @group postRegister */
    public function test_registration_success_message()
    {
        $user = User::factory()->create();
        $this->withoutMiddleware();
        $response = $this->call('POST', 'auth/register', ['first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => 'santhanu@gmail.com',
            'company' => $user->company,
            'bussiness' => $user->bussiness,
            'company_type' => $user->company_type,
            'company_size' => $user->company_size,
            'country' => $user->country,
            'mobile_code' => '91',
            'mobile' => '7418934525',
            'address' => $user->address,
            'town' => $user->town,
            'zip' => $user->zip,
            'password' => $user->password,
            'password_confirmation' => $user->password,
            'registerForm' => [
                'pot_field' => '',     // valid
                'time_field' => encrypt(time() - 10), // valid
            ],
        ]);
        $response->assertContent('{"success":true,"message":"You\u2019re all set! Registration complete.","data":{"need_verify":0}}');
        $response->assertJsonStructure([
            'success', 'message', 'data',
        ]);
    }

    /** @group postRegister */
    public function test_when_mobile_number_is_not_sent()
    {
        $user = User::factory()->create();
        $this->withoutMiddleware();
        $response = $this->call('POST', 'auth/register', ['first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => 'santhanu@gmail.com',
            'company' => $user->company,
            'bussiness' => $user->bussiness,
            'company_type' => $user->company_type,
            'company_size' => $user->company_size,
            'country' => $user->country,
            'address' => $user->address,
            'town' => $user->town,
            'zip' => $user->zip,
            'password' => $user->password,
            'password_confirmation' => $user->password,
        ]);
        $response->assertSessionHasErrors('mobile');
    }

    /** @group postRegister */
    public function test_when_user_registered_present_in_database()
    {
        $user = User::factory()->create(['bussiness' => '--', 'company_type' => '--']);
        $this->withoutMiddleware();
        $response = $this->call('POST', 'auth/register', ['first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => 'santhanu@gmail.com',
            'company' => $user->company,
            'bussiness' => '--',
            'company_type' => '--',
            'company_size' => $user->company_size,
            'country' => $user->country,
            'state' => 'IN-TN',
            'mobile_code' => '91',
            'mobile' => '7418934528',
            'address' => $user->address,
            'town' => $user->town,
            'zip' => $user->zip,
            'password' => $user->password,
            'password_confirmation' => $user->password,
            'registerForm' => [
                'pot_field' => '',     // valid
                'time_field' => encrypt(time() - 10), // valid
            ],
        ]);
        $response->assertContent('{"success":true,"message":"You\u2019re all set! Registration complete.","data":{"need_verify":0}}');
        $response->assertJsonStructure([
            'success', 'message', 'data',
        ]);
    }

    public function test_postRegister_whenEverythingMatches()
    {
        $this->withoutMiddleware();
        $this->setUpServerVariable('192.168.12.12', 'someaddress', 'IN');
        $user = User::factory()->create(['bussiness' => 'Accounting', 'mobile_code' => 91]);
        $status = StatusSetting::where('id', 1)->update(['email_validation_status' => 0, 'mobile_validation_status' => 0]);

        $response = $this->call('POST', 'auth/register', [
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => 'santhanuchakrapani@gmail.com',
            'company' => $user->company,
            'bussiness' => 'Accounting',
            'company_type' => $user->company_type,
            'company_size' => $user->company_size,
            'country' => 'IN',
            'mobile_code' => '91',
            'mobile' => '9901541237',
            'address' => $user->address,
            'town' => $user->town,
            'state' => $user->state,
            'zip' => $user->zip,
            'user_name' => 'testuser11',
            'ip' => $user->ip,
            'password' => 'Santhanu@12',
            'password_confirmation' => 'Santhanu@12',
            'terms' => 'on',
            'registerForm' => [
                'pot_field' => '',     // valid
                'time_field' => encrypt(time() - 10), // valid
            ],
        ]);

        $response->assertStatus(200);
        $content = $response->original;
        $this->assertEquals(true, $content['success']);
    }
}
