<?php

namespace Tests\Browser\Auth;

use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\Browser\Helpers\DuskHelper;
use Tests\DuskTestCase;

class RegisterTest extends DuskTestCase
{
    use DuskHelper;

    protected $user;

    protected $data;

    protected function setUp(): void
    {
        parent::setUp();

        $this->runDbSetup();

        $this->user = User::factory()->create([
            'password' => bcrypt('Demopass@1234'),
        ]);

        $this->data['v3'] = [
            'nocaptcha_sitekey' => '6LeNWgUqAAAAADExaPRd4hdOIOVMI9dJSJxESp16',
            'captcha_secretCheck' => '6LeNWgUqAAAAAJLZtAoo1WfwIPHBDXIpovmwEXdB',
        ];

        $this->data['v2'] = [
            'nocaptcha_sitekey' => '6LdAqqMqAAAAAH7CdVR1pGlKTQ502u3VcqvHPwbF',
            'captcha_secretCheck' => '6LdAqqMqAAAAAFm-aykWNK7Q5K5Lee4NReRu8jXY',
        ];
    }

    public function test_new_register_with_duplicate_values()
    {

        $this->browse(function (Browser $browser) {

            $browser->visit('/login');

            $this->bypassInsecurePage($browser);

            $this->showCaption($browser, 'TC0131 - Check for error message if duplicate values entered');

            $browser->type('#first_name', 'Test')
                ->type('#last_name', 'User')
                ->type('#email', $this->user->email)
                ->type('#company', 'Test Inc')
                ->type('#address', '123 Test Street')
                ->select('#country', 'India')
                ->type('#mobilenum', '9876543210')
                ->type('#password', 'Test@2001')
                ->type('#confirm_pass', 'Test@2001')
                ->press('#register')
                ->pause(3000);

            $browser->assertSee('The email address has already been taken. Please choose a different email.');
        });
    }


    public function test_new_register_with_mandatory_fields_left_blank()
    {

        $this->browse(function (Browser $browser) {

            $browser->visit('/login');

            $this->bypassInsecurePage($browser);

            $this->showCaption($browser, 'TC0130 - Check for error message if mandatory fields left blank');

            $browser->press('#register')
                ->pause(3000);

            $browser->assertSee('First name is required')
                ->assertSee('Last name is required')
                ->assertSee('Email is required')
                ->assertSee('Company name is required')
                ->assertSee('Address is required')
                ->assertSee('Mobile number is required')
                ->assertSee('Password is required')
                ->assertSee('Confirm password is required');
        });
    }


    public function test_register_with_invalid_first_and_last_name()
    {
        $this->browse(function (Browser $browser) {

            $browser->visit('/login');

            $this->bypassInsecurePage($browser);

            $this->showCaption($browser, 'TC0129 - Check for registration with invalid first name,lastname and email');

            $browser->type('#first_name', '12345678')
                ->type('#last_name', '12345678')
                ->type('#email', $this->user->email)
                ->type('#company', 'Test Inc')
                ->type('#address', '123 Test Street')
                ->select('#country', 'India')
                ->type('#mobilenum', '9876543210')
                ->type('#password', 'Test@2001')
                ->type('#confirm_pass', 'Test@2001')
                ->press('#register')
                ->pause(3000);

            $browser->assertSee('Please enter a valid first name')
                ->assertSee('Please enter a valid last name');
        });
    }

    public function test_register_with_valid_details()
    {
        $this->browse(function (Browser $browser) {

            $browser->visit('/login');

            $this->bypassInsecurePage($browser);

            $this->showCaption($browser, 'TC0129 - Check for registration with invalid first name,lastname and email');

            $browser->type('#first_name', 'Demo')
                ->type('#last_name', 'admin')
                ->type('#email', 'testnewuser@gmail.com')
                ->type('#company', 'Test Inc')
                ->type('#address', '123 Test Street')
                ->select('#country', 'India')
                ->type('#mobilenum', '9876543210')
                ->type('#password', 'Test@2001')
                ->type('#confirm_pass', 'Test@2001')
                ->press('#register')
                ->pause(3000);

            $browser->assertSee(__('message.registration_complete'));
        });
    }


    public function test_register_with_v2_recaptcha()
    {
        $this->enableRecaptcha($this->data['v2']['nocaptcha_sitekey'], $this->data['v2']['captcha_secretCheck'], 'v2');

        $this->browse(function (Browser $browser) {

            $browser->visit('/login');

            $this->bypassInsecurePage($browser);

            $this->showCaption($browser, 'check for registration with v2 recaptcha');

            $browser->type('#first_name', 'Demo')
                ->type('#last_name', 'admin')
                ->type('#email', 'testnewuser@gmail.com')
                ->type('#company', 'Test Inc')
                ->type('#address', '123 Test Street')
                ->select('#country', 'India')
                ->type('#mobilenum', '9876543210')
                ->type('#password', 'Test@2001')
                ->type('#confirm_pass', 'Test@2001')
                ->press('#register')
                ->pause(3000);

            $browser->assertSee('Please verify that you are not a robot.');
        });
    }


    public function test_register_with_v3_recaptcha()
    {
        $this->enableRecaptcha($this->data['v3']['nocaptcha_sitekey'], $this->data['v3']['captcha_secretCheck'], 'v3');

        $this->browse(function (Browser $browser) {

            $browser->visit('/login');

            $this->bypassInsecurePage($browser);

            $this->showCaption($browser, 'check for registration with v3 recaptcha');

            $browser->type('#first_name', 'Demo')
                ->type('#last_name', 'admin')
                ->type('#email', \Str::random(8).'@gmail.com')
                ->type('#company', 'Test Inc')
                ->type('#address', '123 Test Street')
                ->select('#country', 'India')
                ->type('#mobilenum', '9876543210')
                ->type('#password', 'Test@2001')
                ->type('#confirm_pass', 'Test@2001')
                ->pause(3000)
                ->press('#register')
                ->pause(3000);

            $browser->assertSee(__('message.registration_complete'));
        });
    }
}
