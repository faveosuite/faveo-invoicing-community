<?php

namespace Tests\Unit\Client;

use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Testing\Fluent\AssertableJson;
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

    /** @group postRegister */
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
            'mobile' => $user->mobile,
            'address' => $user->address,
            'town' => $user->town,
            'state' => $user->state,
            'zip' => $user->zip,
            'password' => $user->password,
            'password_confirmation' => $user->password,
        ]);
        $response->assertSessionHasErrors('email',"The email field is required.");
    }

    /** @group postRegister */
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
            'mobile' => '9901541237',
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
        $this->mock->disable();
        $this->tearDownServerVariable();
    }

    /** @group postRegister */
    public function test_when_confirm_password_does_not_match(){
        $user = User::factory()->create();
        $this->withoutMiddleware();
        $response = $this->call('POST', 'auth/register', ['first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email'=>'santhanu@gmail.com',
            'company' => $user->company,
            'bussiness' => $user->bussiness,
            'company_type' => $user->company_type,
            'company_size' => $user->company_size,
            'country' => $user->country,
            'mobile_code' => '91',
            'mobile' => $user->mobile,
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
    public function test_registration_success_message(){
        $user = User::factory()->create();
        $this->withoutMiddleware();
        $response = $this->call('POST', 'auth/register', ['first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email'=>'santhanu@gmail.com',
            'company' => $user->company,
            'bussiness' => $user->bussiness,
            'company_type' => $user->company_type,
            'company_size' => $user->company_size,
            'country' => $user->country,
            'mobile_code' => '91',
            'mobile' => $user->mobile,
            'address' => $user->address,
            'town' => $user->town,
            'zip' => $user->zip,
            'password' => $user->password,
            'password_confirmation' => $user->password,
        ]);
        $response->assertJsonStructure([
                    'success','message','data'
        ]);
    }

    /** @group postRegister */
    public function test_when_mobile_code_is_not_sent(){
        $user = User::factory()->create();
        $this->withoutMiddleware();
        $response = $this->call('POST', 'auth/register', ['first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email'=>'santhanu@gmail.com',
            'company' => $user->company,
            'bussiness' => $user->bussiness,
            'company_type' => $user->company_type,
            'company_size' => $user->company_size,
            'country' => $user->country,
            'mobile' => $user->mobile,
            'address' => $user->address,
            'town' => $user->town,
            'zip' => $user->zip,
            'password' => $user->password,
            'password_confirmation' => $user->password,
        ]);
        $response->assertSessionHasErrors('mobile_code');
    }

    /** @group postRegister */
    public function test_when_user_registered_present_in_database(){
        $user = User::factory()->create(['bussiness'=>'--','company_type'=>'--']);
        $this->withoutMiddleware();
        $response = $this->call('POST', 'auth/register', ['first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email'=>'santhanu@gmail.com',
            'company' => $user->company,
            'bussiness' => '--',
            'company_type' => '--',
            'company_size' => $user->company_size,
            'country' => $user->country,
            'state' => 'IN-TN',
            'mobile_code' => '91',
            'mobile' => $user->mobile,
            'address' => $user->address,
            'town' => $user->town,
            'zip' => $user->zip,
            'password' => $user->password,
            'password_confirmation' => $user->password,
        ]);
        $this->assertDatabaseHas('users', $user->toArray());
    }
}
