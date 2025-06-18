<?php

namespace Tests\Browser\Auth;

use App\User;
use Laravel\Dusk\Browser;
use Tests\Browser\Helpers\DuskHelper;
use Tests\DuskTestCase;
use PHPUnit\Framework\Attributes\Group;

class LoginTest extends DuskTestCase
{
    use DuskHelper;

    protected $user;

    /*
     * The following test cases were developed
     * based on the test cases listed in QA Touch.
     * Each test scenario reflects the test requirements
     * documented in the QA Touch.
     */

    protected function setUp(): void
    {
        parent::setUp();

        $this->runDbSetup();

        $this->user = User::factory()->create([
            'password' => bcrypt('Demopass@1234'),
        ]);
    }


    #[Group('forgot_password')]
    #[Group('login_test_group')]
    public function test_for_forgot_password_by_giving_incorrect_email_id()
    {
        $this->setUpEmail(true);

        $this->browse(function (Browser $browser) {
            $browser->visit('/login');

            $this->bypassInsecurePage($browser);

            $this->showCaption($browser, 'TC0096 - Check for Forgot password by giving incorrect email id');

            $browser->clickAtXPath('/html/body/div[2]/div[1]/div[2]/div[3]/div/section/div/div/div[6]/div[1]/form/div[3]/div[2]/div/a');

            $browser->pause(2000);

            $browser->type('#email', 'testuser@gmail.com')
                ->press('#resetmail')
                ->pause(500);

            $browser->assertSee(__('message.reset_instructions', ['email' => 'testuser@gmail.com']));
        });
    }


    #[Group('forgot_password')]
    #[Group('login_test_group')]
    public function test_for_forgot_password_by_giving_invalid_email_id()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login');

            $this->bypassInsecurePage($browser);

            $this->showCaption($browser, 'TC0095 - Check for Forgot password by giving invalid emailid');

            $browser->clickAtXPath('/html/body/div[2]/div[1]/div[2]/div[3]/div/section/div/div/div[6]/div[1]/form/div[3]/div[2]/div/a');

            $browser->pause(2000);

            $browser->type('#email', 'testuser')
                ->press('#resetmail')
                ->pause(500);

            $browser->assertSee('Please enter a valid email address.');
        });
    }

    #[Group('forgot_password')]
    #[Group('login_test_group')]
    public function test_navigate_to_login_page_from_forgot_password_page()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login');

            $this->bypassInsecurePage($browser);

            $this->showCaption($browser, 'TC0094 - Navigate to login page from Forgot password page');

            $browser->clickAtXPath('/html/body/div[2]/div[1]/div[2]/div[3]/div/section/div/div/div[6]/div[1]/form/div[3]/div[2]/div/a');

            $browser->pause(2000);

            $browser->assertPathIs('*/password/reset');

            $browser->clickAtXPath('//*[@id="resetPasswordForm"]/div[2]/div/a');

            $browser->pause(2000);

            $browser->assertPathIs('*/login');
        });
    }

    #[Group('forgot_password')]
    #[Group('login_test_group')]
    public function test_for_forgot_password_by_giving_valid_email_id()
    {
        $this->setUpEmail(true);

        $this->browse(function (Browser $browser) {
            $browser->visit('/login');

            $this->bypassInsecurePage($browser);

            $this->showCaption($browser, 'TC0093 - Check for Forgot password by giving valid emailid');

            $browser->clickAtXPath('/html/body/div[2]/div[1]/div[2]/div[3]/div/section/div/div/div[6]/div[1]/form/div[3]/div[2]/div/a');

            $browser->pause(2000);

            $browser->type('#email', $this->user->email)
                ->press('#resetmail');

            $expectedMessage = __('message.reset_instructions', ['email' => $this->user->email]);

            $browser->waitForText($expectedMessage, 15);
            $browser->assertSee($expectedMessage);
        });
    }

    #[Group('login')]
    #[Group('login_test_group')]
    public function test_login_as_admin_without_check_recaptcha()
    {
        $this->user = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->enableRecaptcha('v2');

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

        $this->disableRecaptcha();
    }

    #[Group('login')]
    #[Group('login_test_group')]
    public function test_login_as_admin_valid_user_name_and_invalid_password()
    {
        $this->user = User::factory()->create([
            'role' => 'admin',
        ]);
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

    #[Group('login')]
    #[Group('login_test_group')]
    public function test_login_as_admin_with_invalid_user_name_and_valid_password(): void
    {
        $this->user = User::factory()->create([
            'role' => 'admin',
        ]);
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


    #[Group('login')]
    #[Group('login_test_group')]
    public function test_login_as_admin_with_invalid_user_name_and_password(): void
    {
        $this->user = User::factory()->create([
            'role' => 'admin',
        ]);
        $this->browse(function (Browser $browser) {
            $browser->visit('/login');

            $this->bypassInsecurePage($browser);

            $this->showCaption($browser, 'TC0089 - Login as Admin with invalid username and password');

            $browser->type('#username', 'test@example.com')
                ->type('#pass', 'Demo123')
                ->press('#login-btn');

            $this->showCaption($browser, 'TC0089 - Login as Admin with invalid username and password');

            $browser->pause(2000);

            $browser->assertSee('Please Enter a valid Email');
        });
    }

    #[Group('login')]
    #[Group('login_test_group')]
    public function test_login_as_admin_with_captcha_option()
    {
        // through dusk we cannot pass the recaptcha v2 checkbox, so we use v3 for testing
        $this->user = User::factory()->create([
            'role' => 'admin',
            'password' => bcrypt('Demopass@1234'),
        ]);

        $this->enableRecaptcha('v3');

        $this->browse(function (Browser $browser) {
            $browser->visit('/login');

            $this->bypassInsecurePage($browser);

            $this->showCaption($browser, 'TC0088 - Login as Admin with Captcha option');

            $browser->type('#username', $this->user->email)
                ->type('#pass', 'Demopass@1234')
                ->pause(3000)
                ->press('#login-btn');

            $this->showCaption($browser, 'TC0088 - Login as Admin with Captcha option');

            $browser->pause(5000)
                ->assertPathIs('*/client-dashboard');

            $browser->logout();
        });
    }


    #[Group('login')]
    #[Group('login_test_group')]
    public function test_login_as_admin_with_valid_user_name_and_password()
    {
        $this->user = User::factory()->create([
            'role' => 'admin',
            'password' => bcrypt('Demopass@1234'),
        ]);

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
                ->assertPathIs('*/client-dashboard');

            $browser->logout();
            
            $browser->pause(5000);
        });
    }


    #[Group('login')]
    #[Group('login_test_group')]
    public function test_login_as_client_without_check_recaptcha()
    {
        $this->user = User::factory()->create([
            'role' => 'user',
        ]);

        $this->enableRecaptcha('v2');

        $this->browse(function (Browser $browser) {
            $browser->visit('/login');

            $this->bypassInsecurePage($browser);

            $this->showCaption($browser, 'TC0086 - Login as Client without check reCaptcha');

            $browser->type('#username', $this->user->email)
                ->type('#pass', 'Demopass@1234')
                ->press('#login-btn');

            $this->showCaption($browser, 'TC0086 - Login as Client without check reCaptcha');

            $browser->assertSee('Please verify that you are not a robot.');

            $browser->pause(5000);
        });

        $this->disableRecaptcha();
    }

    #[Group('login')]
    #[Group('login_test_group')]
    public function test_login_as_client_valid_user_name_and_invalid_password()
    {
        $this->user = User::factory()->create([
            'role' => 'user',
        ]);
        $this->browse(function (Browser $browser) {
            $browser->visit('/login');

            $this->bypassInsecurePage($browser);

            $this->showCaption($browser, 'TC0085 - Login as Client valid user name and invalid password');

            $browser->type('#username', $this->user->email)
                ->type('#pass', 'Demopass@123')
                ->press('#login-btn');

            $this->showCaption($browser, 'TC0085 - Login as Client valid user name and invalid password');

            $browser->pause(3000);

            $browser->assertSee('Please Enter a valid Password');
        });
    }

    #[Group('login')]
    #[Group('login_test_group')]
    public function test_login_as_client_with_invalid_user_name_and_valid_password(): void
    {
        $this->user = User::factory()->create([
            'role' => 'user',
        ]);
        $this->browse(function (Browser $browser) {
            $browser->visit('/login');

            $this->bypassInsecurePage($browser);

            $this->showCaption($browser, 'TC0084 - Login as Client with invalid user name and valid password');

            $browser->type('#username', 'test@example.com')
                ->type('#pass', 'Demopass@123')
                ->press('#login-btn');

            $this->showCaption($browser, 'TC0084 - Login as Client with invalid user name and valid password');

            $browser->pause(2000);

            $browser->assertSee('Please Enter a valid Email');
        });
    }


    #[Group('login')]
    #[Group('login_test_group')]
    public function test_login_as_client_with_invalid_user_name_and_password(): void
    {
        $this->user = User::factory()->create([
            'role' => 'user',
        ]);
        $this->browse(function (Browser $browser) {
            $browser->visit('/login');

            $this->bypassInsecurePage($browser);

            $this->showCaption($browser, 'TC0083 - Login as Client with invalid username and password');

            $browser->type('#username', 'test@example.com')
                ->type('#pass', 'Demo123')
                ->press('#login-btn');

            $this->showCaption($browser, 'TC0083 - Login as Client with invalid username and password');

            $browser->pause(2000);

            $browser->assertSee('Please Enter a valid Email');
        });
    }

    #[Group('login')]
    #[Group('login_test_group')]
    public function test_login_as_client_with_captcha_option()
    {
        // through dusk we cannot pass the recaptcha v2 checkbox, so we use v3 for testing
        $this->user = User::factory()->create([
            'role' => 'user',
            'password' => bcrypt('Demopass@1234'),
        ]);

        $this->enableRecaptcha('v3');

        $this->browse(function (Browser $browser) {
            $browser->visit('/login');

            $this->bypassInsecurePage($browser);

            $this->showCaption($browser, 'TC0082 - Login as client with Captcha option');

            $browser->type('#username', $this->user->email)
                ->type('#pass', 'Demopass@1234')
                ->pause(3000)
                ->press('#login-btn');

            $this->showCaption($browser, 'TC0082 - Login as client with Captcha option');

            $browser->pause(5000)
                ->assertPathIs('*/my-invoices');

            $browser->logout();
        });
    }


    #[Group('login')]
    #[Group('login_test_group')]
    public function test_login_as_client_with_valid_user_name_and_password()
    {
        $this->user = User::factory()->create([
            'role' => 'user',
            'password' => bcrypt('Demopass@1234'),
        ]);

        $this->browse(function (Browser $browser) {
            $browser->visit('/login');

            $this->bypassInsecurePage($browser);

            $this->showCaption($browser, 'TC0081 - Login as Client with valid user name and password');

            $browser->pause(1000);

            $browser->type('#username', $this->user->email)
                ->type('#pass', 'Demopass@1234')
                ->pause(3000)
                ->press('#login-btn');

            $this->showCaption($browser, 'TC0081 - Login as Client with valid user name and password');

            $browser->pause(5000)
                ->assertPathIs('*/my-invoices');

            $browser->logout();
        });
    }
}
