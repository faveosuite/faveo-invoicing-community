<?php

namespace Tests\Browser\Auth;

use App\User;
use Laravel\Dusk\Browser;
use Tests\Browser\Helpers\DuskHelper;
use Tests\DuskTestCase;

class LoginTest extends DuskTestCase
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

    /**
     * A Dusk test example.
     */
    public function test_user_with_invalid_details(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login');

            $this->bypassInsecurePage($browser);

            $this->showCaption($browser, 'Test user with invalid details to see frontend validation is working');

            $browser->press('#login-btn');

            $browser->assertSee('Please enter your username or email address.');

            $browser->assertSee('Please enter your password.');

            $browser->type('#username', 'testuser');

            $browser->assertSee('Please enter a valid email address.');
        });
    }

    public function test_user_with_invalid_password()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login');

            $this->bypassInsecurePage($browser);

            $this->showCaption($browser, 'TC0091 - Login as Admin valid user name and invalid password');

            $browser->type('#username', $this->user->email)
                ->type('#pass', 'Demopass@123')
                ->press('#login-btn');

            $this->showCaption($browser, 'TC0091 - Login as Admin valid user name and invalid password');

            $browser->pause(3000);

            $browser->assertSee('Please Enter a valid Password');
        });
    }

    public function test_user_with_invalid_username_details(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login');

            $this->bypassInsecurePage($browser);

            $this->showCaption($browser, 'TC0090 - Login as Admin with invalid user name and valid password');

            $browser->type('#username', 'test@example.com')
                ->type('#pass', 'Demopass@123')
                ->press('#login-btn');

            $this->showCaption($browser, 'TC0090 - Login as Admin with invalid user name and valid password');

            $browser->pause(2000);

            $browser->assertSee('Please Enter a valid Email');
        });
    }

    public function test_login_with_recaptcha_enabled()
    {
        $this->enableRecaptcha($this->data['v3']['nocaptcha_sitekey'], $this->data['v3']['captcha_secretCheck'], 'v3');

        $this->browse(function (Browser $browser) {
            $browser->visit('/login');

            $this->bypassInsecurePage($browser);

            $this->showCaption($browser, 'TC0087 - Login as Admin with valid user name and password');

            $browser->pause(1000);

            $browser->type('#username', $this->user->email)
                ->type('#pass', 'Demopass@1234')
                ->pause(3000)
                ->press('#login-btn');

            $this->showCaption($browser, 'TC0087 - Login as Admin with valid user name and password');

            $browser->pause(5000)
                ->assertPathIs('*/my-invoices');

            $browser->logout();
        });
    }

    public function test_login_with_v2_recaptcha_enabled()
    {
        $this->enableRecaptcha($this->data['v2']['nocaptcha_sitekey'], $this->data['v2']['captcha_secretCheck'], 'v2');

        $this->browse(function (Browser $browser) {
            $browser->visit('/login');

            $this->bypassInsecurePage($browser);

            $this->showCaption($browser, 'TC0092 - Login as Admin without check reCaptcha');

            $browser->type('#username', $this->user->email)
                ->type('#pass', 'Demopass@1234')
                ->press('#login-btn');

            $this->showCaption($browser, 'TC0092 - Login as Admin without check reCaptcha');

            $browser->assertSee('Please verify that you are not a robot.');

            $browser->pause(5000);
        });
    }
}
