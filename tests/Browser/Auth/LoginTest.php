<?php

namespace Tests\Browser\Auth;

use App\ApiKey;
use App\Model\Common\StatusSetting;
use App\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class LoginTest extends DuskTestCase
{
    protected $user;

    protected $data;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'email' => 'testuser@example.com',
            'password' => bcrypt('Demopass@1234'),
        ]);

        $this->data = [
            'nocaptcha_sitekey' => '6LeNWgUqAAAAADExaPRd4hdOIOVMI9dJSJxESp16',
            'captcha_secretCheck' => '6LeNWgUqAAAAAJLZtAoo1WfwIPHBDXIpovmwEXdB',
        ];
    }

    protected function tearDown(): void
    {
        $this->user->forceDelete();
        parent::tearDown();
    }

    /**
     * A Dusk test example.
     */
    public function test_user_with_invalid_details(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login');

            try {
                $browser->click('#details-button')
                    ->click('#proceed-link');
            } catch (\Exception $e) {
            }

            $browser->press('#login-btn');

            $browser->assertSee('Please enter your username or email address.');

            $browser->assertSee('Please enter your password.');

            $browser->type('#username', 'testuser');

            $browser->assertSee('Please enter a valid email address.');

            $browser->type('#username', 'testuser@example.com')
                ->type('#pass', 'Demopass@123')
                ->press('#login-btn');

            $browser->assertSee('Please Enter a valid Password');
        });
    }

    public function test_user_with_invalid_username_details(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login');

            try {
                $browser->click('#details-button')
                    ->click('#proceed-link');
            } catch (\Exception $e) {
            }

            $browser->type('#username', 'test@example.com')
                ->type('#pass', 'Demopass@123')
                ->press('#login-btn');

            $browser->assertSee('Please Enter a valid Email');
        });
    }

    public function testLoginWithRecaptchaEnabled()
    {
        $this->browse(function (Browser $browser) {
            StatusSetting::first()->update([
                'v3_v2_recaptcha_status' => 1,
                'v3_recaptcha_status' => 1,
            ]);

            ApiKey::first()->update([
                'nocaptcha_sitekey' => $this->data['nocaptcha_sitekey'],
                'captcha_secretCheck' => $this->data['captcha_secretCheck'],
            ]);

            $browser->visit('/login');

            try {
                $browser->click('#details-button')
                    ->click('#proceed-link');
            } catch (\Exception $e) {
            }

            $browser->pause(2000);

            $browser->type('#username', 'testuser@example.com')
                ->type('#pass', 'Demopass@1234')
                ->press('#login-btn');

            $browser->pause(1000);

            $browser->assertPathIs('*/my-invoices');
        });
    }
}
