<?php

namespace Tests\Unit\Client;

use App\Model\Common\EmailMobileValidationProviders;
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
        $response = $this->call('POST', 'auth/register', ['first_name' => $user->first_name,
            'last_name' => $user->last_name,
            // 'email' =>$user->email,
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
            // 'user_name'=>$user->user_name,
            'password' => $user->password,
        ]);
        $errors = session('errors');
        // dd($errors);
        // dd($response);
        $response->assertStatus(302);
        // $this->assertEquals($errors->get('user_name')[0], 'The user name field is required.');
        // $this->assertEquals($errors->get('email')[0], 'The email field is required.');
    }

    // #[Group('postRegister')]
    // public function test_when_user_registers_successfully()
    // {
    //     $this->setUpServerVariable('192.168.12.12', 'someaddress', 'IN');
    //     $user = User::factory()->create(['bussiness'=>'Accounting', 'mobile_code'=>91]);
    //     $response = $this->call('POST', 'auth/register', ['first_name'=> $user->first_name,
    //                 'last_name'                                       => $user->last_name,
    //                 'email'                                           => 'test@gmail.com',
    //                  'company'                                        => $user->company,
    //                  'bussiness'                                      => 'Accounting',
    //                  'company_type'                                   => $user->company_type,
    //                  'company_size'                                   => $user->company_size,
    //                  'country'                                        => 'IN',
    //                  'mobile_code'                                    => '91',
    //                  'mobile'                                         => '9901541237',
    //                  'address'                                        => $user->address,
    //                  'town'                                           => $user->town,
    //                  'state'                                          => $user->state,
    //                  'zip'                                            => $user->zip,
    //                  'user_name'                                      => 'testuser11',
    //                  'ip'                                             => $user->ip,
    //                  'password'                                       => $user->password,
    //                  'password_confirmation'                          => $user->password,
    //                  'terms'                                          => 'on',

    //                  ]);
    // dd($response);
    // $response->assertStatus(302);
    // $this->assertEquals(json_decode($response->content())->type, 'success');
    // $this->assertStringContainsSubstring(json_decode($response->content())->message, 'Your Submission');

    // $this->tearDownServerVariable();
    // }

    // #[Group('postRegister')]
    // public function test_postRegister_whenUserRegistersAndIpIsNotDetected()
    // {
    //     $user = User::factory()->create(['bussiness'=>'Accounting', 'mobile_code'=>91]);
    //     $response = $this->call('POST', 'auth/register', ['first_name'=> $user->first_name,
    //                 'last_name'                                       => $user->last_name,
    //                 'email'                                           => 'test@gmail.com',
    //                  'company'                                        => $user->company,
    //                  'bussiness'                                      => 'Accounting',
    //                  'company_type'                                   => $user->company_type,
    //                  'company_size'                                   => $user->company_size,
    //                  'country'                                        => 'IN',
    //                  'mobile_code'                                    => '91',
    //                  'mobile'                                         => '9901541237',
    //                  'address'                                        => $user->address,
    //                  'town'                                           => $user->town,
    //                  'state'                                          => $user->state,
    //                  'zip'                                            => $user->zip,
    //                  'user_name'                                      => 'testuser11',
    //                  'ip'                                             => $user->ip,
    //                  'password'                                       => $user->password,
    //                  'password_confirmation'                          => $user->password,
    //                  'terms'                                          => 'on',
    //                  ]);
    //     // dd($response);
    //     $response->assertStatus(302);
    //     // $this->assertEquals(json_decode($response->content())[0], 'Undefined index: REMOTE_ADDR');
    // }

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
        // $this->assertEquals($errors->get('password_confirmation')[0], 'The password confirmation and password must match.');
        $this->mock->disable();
        $this->tearDownServerVariable();
    }

    public function test_postRegister_whenEverythingMatches()
    {
        $this->withoutMiddleware();
        $this->setUpServerVariable('192.168.12.12', 'someaddress', 'IN');
        $user = User::factory()->create(['bussiness' => 'Accounting', 'mobile_code' => 91]);
        $status = StatusSetting::where('id', 1)->update(['email_validation_status' => 1, 'mobile_validation_status' => 1]);
        EmailMobileValidationProviders::where('provider', 'reoon')->update(['mode' => 'quick', 'api_key' => 'OUDJUBXL3xLXX39xXB5KcLTl7A1rxjZg', 'accepted_output' => 1]);
        EmailMobileValidationProviders::where('provider', 'abstract')->update(['api_key' => '6a060f0ef12f4ff5a914cc09a03963ab', 'to_use' => 1]);

        $response = $this->call('POST', 'auth/register', ['first_name' => $user->first_name,
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
        ]);

        $response->assertStatus(200);
        $content = $response->original;
        $this->assertEquals(true, $content['success']);
    }
}
