<?php

namespace Tests\Browser\Auth;

use App\Model\Common\StatusSetting;
use App\Model\User\AccountActivate;
use App\User;
use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\Group;
use Tests\Browser\Helpers\DuskHelper;
use Tests\DuskTestCase;

class RegisterTest extends DuskTestCase
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

    #[Group('register')]
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
                ->type('#mobilenum', random_int(1111111111, 9999999999))
                ->type('#password', 'Test@2001')
                ->type('#confirm_pass', 'Test@2001')
                ->press('#register')
                ->pause(3000);

            $browser->assertSee('The email address has already been taken. Please choose a different email.');
        });
    }

    #[Group('register')]
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

    #[Group('register')]
    public function test_register_with_invalid_first_and_last_name_and_email()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login');

            $this->bypassInsecurePage($browser);

            $this->showCaption($browser, 'TC0129 - Check for registration with invalid first name,lastname and email');

            $browser->type('#first_name', '12345678')
                ->type('#last_name', '12345678')
                ->type('#email', 'asdfghjj')
                ->type('#company', 'Test Inc')
                ->type('#address', '123 Test Street')
                ->select('#country', 'India')
                ->type('#mobilenum', random_int(1111111111, 9999999999))
                ->type('#password', 'Test@2001')
                ->type('#confirm_pass', 'Test@2001')
                ->press('#register')
                ->pause(3000);

            $browser->assertSee('Please enter a valid first name')
                ->assertSee('Please enter a valid last name')
                ->assertSee('Please enter a valid email address.');
        });
    }

    #[Group('register')]
    public function test_register_witt_invalid_lastname_and_email()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login');

            $this->bypassInsecurePage($browser);

            $this->showCaption($browser, 'TC0128 - Check for registration with invalid last name and invalid email');

            $browser->type('#first_name', 'Demo')
                ->type('#last_name', '124234')
                ->type('#email', 'testcom')
                ->type('#company', 'Test Inc')
                ->type('#address', '123 Test Street')
                ->select('#country', 'India')
                ->type('#mobilenum', random_int(1111111111, 9999999999))
                ->type('#password', 'Test@2001')
                ->type('#confirm_pass', 'Test@2001')
                ->press('#register')
                ->pause(3000);

            $browser->assertSee('Please enter a valid last name')
                ->assertSee('Please enter a valid email address.');
        });
    }

    #[Group('register')]
    public function test_invalid_firstname_and_lastname()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login');

            $this->bypassInsecurePage($browser);

            $this->showCaption($browser, 'TC0127 - Check for invalid first name and invalid last name');

            $browser->type('#first_name', '3424534')
                ->type('#last_name', '32452')
                ->type('#email', 'testnewuser@gmail.com')
                ->type('#company', 'Test Inc')
                ->type('#address', '123 Test Street')
                ->select('#country', 'India')
                ->type('#mobilenum', random_int(1111111111, 9999999999))
                ->type('#password', 'Test@2001')
                ->type('#confirm_pass', 'Test@2001')
                ->press('#register')
                ->pause(3000);

            $browser->assertSee('Please enter a valid first name')
                ->assertSee('Please enter a valid last name');
        });
    }

    #[Group('register')]
    public function test_check_for_registration()
    {
        StatusSetting::first()->update([
            'msg91_status' => 1,
            'emailverification_status' => 1,
        ]);

        $this->enableEmailAndMobile(true, false);

        $this->browse(function (Browser $browser) {
            $browser->visit('/login');

            $this->bypassInsecurePage($browser);

            $this->showCaption($browser, 'TC0126 - Check for the registration');

            $email = \Str::random(10).'@gmail.com';

            $browser->type('#first_name', 'Demo')
                ->type('#last_name', 'admin')
                ->type('#email', $email)
                ->type('#company', 'Test Inc')
                ->type('#address', '123 Test Street')
                ->select('#country', 'India')
                ->type('#mobilenum', random_int(1111111111, 9999999999))
                ->type('#password', 'Test@2001')
                ->type('#confirm_pass', 'Test@2001')
                ->press('#register')
                ->pause(10000);

            $otp = AccountActivate::where('email', $email)->value('token');

            $browser->type('#email_otp', $otp);
            $browser->pause(5000);
            $browser->press('#emailVerifyBtn');
            $browser->pause(5000);
            $browser->assertSee('Well Done! You’re all set! Registration complete.');
            $this->enableEmailAndMobile(false, false);
        });
    }

    #[Group('register')]
    public function test_for_terms_field_validation()
    {
        $this->enableOrDisableTerms(true);

        $this->browse(function (Browser $browser) {
            $browser->visit('/login');

            $this->bypassInsecurePage($browser);

            $this->showCaption($browser, 'TC0125 - Check for terms field validation');

            $browser->type('#first_name', 'Demo')
                ->type('#last_name', 'admin')
                ->type('#email', $this->user->email)
                ->type('#company', 'Test Inc')
                ->type('#address', '123 Test Street')
                ->select('#country', 'India')
                ->type('#mobilenum', random_int(1111111111, 9999999999))
                ->type('#password', 'Test@2001')
                ->type('#confirm_pass', 'Test@2001')
                ->press('#register')
                ->pause(3000);

            $browser->assertSee('You must agree to the terms and conditions');

            $browser->pause(3000);
        });

        $this->enableOrDisableTerms(false);
    }

    #[Group('register')]
    public function test_for_terms_and_condition_field_validation()
    {
        $this->enableOrDisableTerms(true);

        $this->browse(function (Browser $browser) {
            $browser->visit('/login');

            $this->bypassInsecurePage($browser);

            $this->showCaption($browser, 'TC0124 - Check for Terms and conditions field validation');

            $existingWindows = $browser->driver->getWindowHandles();

            $browser->type('#first_name', 'Demo')
                ->type('#last_name', 'admin')
                ->type('#email', $this->user->email)
                ->type('#company', 'Test Inc')
                ->type('#address', '123 Test Street')
                ->select('#country', 'India')
                ->type('#mobilenum', random_int(1111111111, 9999999999))
                ->type('#password', 'Test@2001')
                ->type('#confirm_pass', 'Test@2001')
                ->clickAtXPath('/html/body/div[2]/div[1]/div[2]/div[3]/div/section/div/div/div[6]/div[2]/form/div[10]/div/div/label/a')
                ->pause(3000);

            $newWindows = $browser->driver->getWindowHandles();
            $newTab = array_diff($newWindows, $existingWindows);

            $this->assertCount(1, $newTab, 'A new tab should be opened for T&C');
        });

        $this->enableOrDisableTerms(false);
    }

    #[Group('register')]
    public function test_for_mandatory_validation_in_re_enter_password_field()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login');

            $this->bypassInsecurePage($browser);

            $this->showCaption($browser, 'TC0123 - Check for mandatory validation in Re-enter password field');

            $browser->type('#first_name', 'Demo')
                ->type('#last_name', 'admin')
                ->type('#email', $this->user->email)
                ->type('#company', 'Test Inc')
                ->type('#address', '123 Test Street')
                ->select('#country', 'India')
                ->type('#mobilenum', random_int(1111111111, 9999999999))
                ->type('#password', 'Test@2001')
                ->keys('#password', '{tab}') // move focus out of password field
                ->pause(500)
                ->press('#register');

            $browser->assertSee('Confirm password is required');

            $browser->pause(3000);
        });
    }

    #[Group('register')]
    public function test_invalid_re_enter_password_field()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login');

            $this->bypassInsecurePage($browser);

            $this->showCaption($browser, 'TC0122 - Check with invalid Re-enter password field');

            $browser->type('#first_name', 'Demo')
                ->type('#last_name', 'admin')
                ->type('#email', $this->user->email)
                ->type('#company', 'Test Inc')
                ->type('#address', '123 Test Street')
                ->select('#country', 'India')
                ->type('#mobilenum', random_int(1111111111, 9999999999))
                ->type('#password', 'Test2001')
                ->type('#confirm_pass', 'Test2001')
                ->pause(500)
                ->press('#register');

            $browser->assertSee('Password must contain at least 8 characters, one uppercase letter, one lowercase letter, one number, and one special character.');

            $browser->pause(3000);
        });
    }

    #[Group('register')]
    public function test_password_field_mandatory_validation()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login');

            $this->bypassInsecurePage($browser);

            $this->showCaption($browser, 'TC0121 - Check for Password field mandatory validation');

            $browser->type('#first_name', 'Demo')
                ->type('#last_name', 'admin')
                ->type('#email', $this->user->email)
                ->type('#company', 'Test Inc')
                ->type('#address', '123 Test Street')
                ->select('#country', 'India')
                ->type('#mobilenum', random_int(1111111111, 9999999999))
                ->pause(500)
                ->press('#register');

            $browser->assertSee('Password is required');

            $browser->pause(3000);
        });
    }

    #[Group('register')]
    public function test_with_invalid_password()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login');

            $this->bypassInsecurePage($browser);

            $this->showCaption($browser, 'TC0120 - Check with invalid Password');

            $browser->type('#first_name', 'Demo')
                ->type('#last_name', 'admin')
                ->type('#email', $this->user->email)
                ->type('#company', 'Test Inc')
                ->type('#address', '123 Test Street')
                ->select('#country', 'India')
                ->type('#mobilenum', random_int(1111111111, 9999999999))
                ->type('#password', 'Test2001')
                ->type('#confirm_pass', 'Test2001')
                ->pause(500)
                ->press('#register');

            $browser->assertSee('Password must contain at least 8 characters, one uppercase letter, one lowercase letter, one number, and one special character.');

            $browser->pause(3000);
        });
    }

    #[Group('register')]
    public function test_for_password_field_validation_in_signup_for_free()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login');

            $this->bypassInsecurePage($browser);

            $this->showCaption($browser, 'TC0119 - Check for Password field validation - in signup for free');

            $browser->type('#first_name', 'Demo')
                ->type('#last_name', 'admin')
                ->type('#email', \Str::random(10).'@gmail.com')
                ->type('#company', 'Test Inc')
                ->type('#address', '123 Test Street')
                ->select('#country', 'India')
                ->type('#mobilenum', random_int(1111111111, 9999999999))
                ->type('#password', 'Test@2001')
                ->type('#confirm_pass', 'Test@2001')
                ->pause(500)
                ->press('#register');

            $browser->pause(3000);

            $browser->assertSee('You’re all set! Registration complete.');
        });
    }

    #[Group('register')]
    public function test_for_mobile_field_validation_in_sign_up()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login');

            $this->bypassInsecurePage($browser);

            $this->showCaption($browser, 'TC0118 - Check for Mobile field validation in sign up');

            $browser->type('#first_name', 'Demo')
                ->type('#last_name', 'admin')
                ->type('#email', \Str::random(10).'@gmail.com')
                ->type('#company', 'Test Inc')
                ->type('#address', '123 Test Street')
                ->select('#country', 'India')
                ->type('#password', 'Test@2001')
                ->type('#confirm_pass', 'Test@2001')
                ->pause(500)
                ->press('#register');

            $browser->assertSee('Mobile number is required');
        });
    }

    #[Group('register')]
    public function test_with_invalid_mobile_number_for_sign_up()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login');

            $this->bypassInsecurePage($browser);

            $this->showCaption($browser, 'TC0117 - Check with invalid Mobile number for sign up');

            $browser->type('#first_name', 'Demo')
                ->type('#last_name', 'admin')
                ->type('#email', \Str::random(10).'@gmail.com')
                ->type('#company', 'Test Inc')
                ->type('#address', '123 Test Street')
                ->select('#country', 'India')
                ->type('#mobilenum', '12345')
                ->type('#password', 'Test@2001')
                ->type('#confirm_pass', 'Test@2001')
                ->pause(500)
                ->press('#register');

            $browser->assertSee('Please enter a valid phone number');

            $browser->pause(3000);
        });
    }

    #[Group('register')]
    public function test_for_mobile_field_validation()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login');

            $this->bypassInsecurePage($browser);

            $this->showCaption($browser, 'TC0116 - Check for Mobile field validation');

            $browser->type('#first_name', 'Demo')
                ->type('#last_name', 'admin')
                ->type('#email', \Str::random(10).'@gmail.com')
                ->type('#company', 'Test Inc')
                ->type('#address', '123 Test Street')
                ->select('#country', 'India')
                ->type('#mobilenum', random_int(1111111111, 9999999999))
                ->type('#password', 'Test@2001')
                ->type('#confirm_pass', 'Test@2001')
                ->pause(500)
                ->press('#register');

            $browser->pause(3000);

            $browser->assertSee('You’re all set! Registration complete.');
        });
    }

    #[Group('register')]
    public function test_for_company_name_validation()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login');

            $this->bypassInsecurePage($browser);

            $this->showCaption($browser, 'TC0115 - Check for Company name validation');

            $browser->type('#first_name', 'Demo')
                ->type('#last_name', 'admin')
                ->type('#email', \Str::random(10).'@gmail.com')
                ->type('#address', '123 Test Street')
                ->select('#country', 'India')
                ->type('#mobilenum', random_int(1111111111, 9999999999))
                ->type('#password', 'Test@2001')
                ->type('#confirm_pass', 'Test@2001')
                ->pause(500)
                ->press('#register');

            $browser->assertSee('Company name is required');
        });
    }

    #[Group('register')]
    public function test_for_invalid_company_name()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login');

            $this->bypassInsecurePage($browser);

            $this->showCaption($browser, 'TC0114 - Check for invalid Company Name');

            $browser->type('#first_name', 'Demo')
                ->type('#last_name', 'admin')
                ->type('#email', \Str::random(10).'@gmail.com')
                ->type('#company', \Str::random(51))
                ->type('#address', '123 Test Street')
                ->select('#country', 'India')
                ->type('#mobilenum', random_int(1111111111, 9999999999))
                ->type('#password', 'Test@2001')
                ->type('#confirm_pass', 'Test@2001')
                ->pause(500)
                ->press('#register')
                ->pause(1000);

            $browser->assertSee('Company name may not be greater than 50 characters.');
        });
    }

    #[Group('register')]
    public function test_for_company_name_field_validation()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login');

            $this->bypassInsecurePage($browser);

            $this->showCaption($browser, 'TC0113 - Check for Company Name field validation');

            $browser->type('#first_name', 'Demo')
                ->type('#last_name', 'admin')
                ->type('#email', \Str::random(10).'@gmail.com')
                ->type('#company', 'Test Inc')
                ->type('#address', '123 Test Street')
                ->select('#country', 'India')
                ->type('#mobilenum', random_int(1111111111, 9999999999))
                ->type('#password', 'Test@2001')
                ->type('#confirm_pass', 'Test@2001')
                ->pause(500)
                ->press('#register');

            $browser->pause(3000);

            $browser->assertSee('You’re all set! Registration complete.');
        });
    }

    #[Group('register')]
    public function test_for_email_field_validation1()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login');

            $this->bypassInsecurePage($browser);

            $this->showCaption($browser, 'TC0112 - Check for Email field validation1');

            $browser->type('#first_name', 'Demo')
                ->type('#last_name', 'admin')
                ->type('#company', 'Test Inc')
                ->type('#address', '123 Test Street')
                ->select('#country', 'India')
                ->type('#mobilenum', random_int(1111111111, 9999999999))
                ->type('#password', 'Test@2001')
                ->type('#confirm_pass', 'Test@2001')
                ->pause(500)
                ->press('#register');

            $browser->assertSee('Email is required');
        });
    }

    #[Group('register')]
    public function test_for_invalid_email_id()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login');

            $this->bypassInsecurePage($browser);

            $this->showCaption($browser, 'TC0111 - Check for invalid email id');

            $browser->type('#first_name', 'Demo')
                ->type('#last_name', 'admin')
                ->type('#email', \Str::random(10))
                ->type('#company', 'Test Inc')
                ->type('#address', '123 Test Street')
                ->select('#country', 'India')
                ->type('#mobilenum', random_int(1111111111, 9999999999))
                ->type('#password', 'Test@2001')
                ->type('#confirm_pass', 'Test@2001')
                ->pause(500)
                ->press('#register');

            $browser->assertSee('Please enter a valid email address.');
        });
    }

    #[Group('register')]
    public function test_for_duplicate_email_id()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login');

            $this->bypassInsecurePage($browser);

            $this->showCaption($browser, 'TC0110 - Check for duplicate email id');

            $browser->type('#first_name', 'Demo')
                ->type('#last_name', 'admin')
                ->type('#email', $this->user->email)
                ->type('#company', 'Test Inc')
                ->type('#address', '123 Test Street')
                ->select('#country', 'India')
                ->type('#mobilenum', random_int(1111111111, 9999999999))
                ->type('#password', 'Test@2001')
                ->type('#confirm_pass', 'Test@2001')
                ->pause(500)
                ->press('#register')
                ->pause(3000);

            $browser->assertSee('The email address has already been taken. Please choose a different email.');
        });
    }

    #[Group('register')]
    public function test_for_email_field_validation()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login');

            $this->bypassInsecurePage($browser);

            $this->showCaption($browser, 'TC0109 - Check for Email field validation');

            $browser->type('#first_name', 'Demo')
                ->type('#last_name', 'admin')
                ->type('#email', \Str::random(10).'@gmail.com')
                ->type('#company', 'Test Inc')
                ->type('#address', '123 Test Street')
                ->select('#country', 'India')
                ->type('#mobilenum', random_int(1111111111, 9999999999))
                ->type('#password', 'Test@2001')
                ->type('#confirm_pass', 'Test@2001')
                ->pause(500)
                ->press('#register');

            $browser->pause(3000);

            $browser->assertSee('You’re all set! Registration complete.');
        });
    }

    #[Group('register')]
    public function test_for_error_message_if_last_name_field_left_blank()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login');

            $this->bypassInsecurePage($browser);

            $this->showCaption($browser, 'TC0108 - Check for error message if Last name field left blank');

            $browser->type('#first_name', 'Demo')
                ->type('#email', \Str::random(10).'@gmail.com')
                ->type('#company', 'Test Inc')
                ->type('#address', '123 Test Street')
                ->select('#country', 'India')
                ->type('#mobilenum', random_int(1111111111, 9999999999))
                ->type('#password', 'Test@2001')
                ->type('#confirm_pass', 'Test@2001')
                ->pause(500)
                ->press('#register');

            $browser->assertSee('Last name is required');
        });
    }

    #[Group('register')]
    public function test_for_invalid_last_name_validation()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login');

            $this->bypassInsecurePage($browser);

            $this->showCaption($browser, 'TC0107 - Check for invalid last name validation');

            $browser->type('#first_name', 'Demo')
                ->type('#last_name', '123456789')
                ->type('#email', \Str::random(10).'@gmail.com')
                ->type('#company', 'Test Inc')
                ->type('#address', '123 Test Street')
                ->select('#country', 'India')
                ->type('#mobilenum', random_int(1111111111, 9999999999))
                ->type('#password', 'Test@2001')
                ->type('#confirm_pass', 'Test@2001')
                ->pause(500)
                ->press('#register');

            $browser->assertSee('Please enter a valid last name');
        });
    }

    #[Group('register')]
    public function test_for_last_name_field_validation1()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login');

            $this->bypassInsecurePage($browser);

            $this->showCaption($browser, 'TC0106 - Check for Last name field validation1');

            $browser->type('#first_name', 'Demo')
                ->type('#last_name', 'admin')
                ->type('#email', \Str::random(10).'@gmail.com')
                ->type('#company', 'Test Inc')
                ->type('#address', '123 Test Street')
                ->select('#country', 'India')
                ->type('#mobilenum', random_int(1111111111, 9999999999))
                ->type('#password', 'Test@2001')
                ->type('#confirm_pass', 'Test@2001')
                ->pause(500)
                ->press('#register');

            $browser->pause(3000);

            $browser->assertSee('You’re all set! Registration complete.');
        });
    }

    #[Group('register')]
    public function test_for_error_message_if_first_name_field_left_blank()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login');

            $this->bypassInsecurePage($browser);

            $this->showCaption($browser, 'TC0105 - Check for error message if first name field left blank');

            $browser->type('#email', \Str::random(10).'@gmail.com')
                ->type('#company', 'Test Inc')
                ->type('#address', '123 Test Street')
                ->select('#country', 'India')
                ->type('#mobilenum', random_int(1111111111, 9999999999))
                ->type('#password', 'Test@2001')
                ->type('#confirm_pass', 'Test@2001')
                ->pause(500)
                ->press('#register');

            $browser->pause(3000);

            $browser->assertSee('First name is required')
                ->assertSee('Last name is required');
        });
    }

    #[Group('register')]
    public function test_for_first_name_field_validation1()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login');

            $this->bypassInsecurePage($browser);

            $this->showCaption($browser, 'TC0104 - Check for First name field validation');

            $browser->type('#first_name', 'Demo')
                ->type('#last_name', 'admin')
                ->type('#email', \Str::random(10).'@gmail.com')
                ->type('#company', 'Test Inc')
                ->type('#address', '123 Test Street')
                ->select('#country', 'India')
                ->type('#mobilenum', random_int(1111111111, 9999999999))
                ->type('#password', 'Test@2001')
                ->type('#confirm_pass', 'Test@2001')
                ->pause(500)
                ->press('#register');

            $browser->pause(3000);

            $browser->assertSee('You’re all set! Registration complete.');
        });
    }

    #[Group('register')]
    public function test_register_with_v2_recaptcha()
    {
        $this->enableRecaptcha('v2');

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
                ->type('#mobilenum', random_int(1111111111, 9999999999))
                ->type('#password', 'Test@2001')
                ->type('#confirm_pass', 'Test@2001')
                ->press('#register')
                ->pause(3000);

            $browser->assertSee('Please verify that you are not a robot.');
        });
    }

    #[Group('register')]
    public function test_register_with_v3_recaptcha()
    {
        $this->enableRecaptcha('v3');

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
                ->type('#mobilenum', random_int(1111111111, 9999999999))
                ->type('#password', 'Test@2001')
                ->type('#confirm_pass', 'Test@2001')
                ->pause(3000)
                ->press('#register')
                ->pause(3000);

            $browser->assertSee(__('message.registration_complete'));
        });
    }
}
