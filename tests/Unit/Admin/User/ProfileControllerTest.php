<?php

namespace Tests\Unit\Admin\User;

use App\Model\User\Password;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\DBTestCase;

class ProfileControllerTest extends DBTestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
        $this->getLoggedInUser('admin');
    }

    public function testUpdateProfileWithoutAnyErrors()
    {
        $response=$this->call('PATCH', 'profile', [
            'first_name' => 'update first',
            'last_name' => 'update last',
            'company' => 'update company',
            'mobile' => '0123456789',
            'address' => 'update address',
            'timezone_id' => '1',
            'user_name' => 'update name',
            'email' => 'updated@example.com',
            'country'=>'USA',
        ]);

        // Asserting all fields
        $this->assertEquals('update first', $this->user->first_name);
        $this->assertEquals('update last', $this->user->last_name);
        $this->assertEquals('update company', $this->user->company);
        $this->assertEquals('0123456789', $this->user->mobile);
        $this->assertEquals('update address', $this->user->address);
        $this->assertEquals('1', $this->user->timezone_id);
        $this->assertEquals('update name', $this->user->user_name);
        $this->assertEquals('updated@example.com', $this->user->email);
    }

    public function testUpdateProfileWithErrors()
    {
        $this->getLoggedInUser('admin');

        $response = $this->call('PATCH', 'profile', [
            'first_name' => 'update first',
            'company' => 'update company',
            'mobile' => '0123456789',
            'address' => 'update address',
            'timezone_id' => '1',
            'user_name' => 'update name',
        ]);

        $response->assertSessionHasErrors(['email']);
        $response->assertSessionHasErrors(['last_name']);
    }

    public function testUpdatePasswordSuccess()
    {
        // Manually update the password first
        \Auth::user()->update(['password' => \Hash::make('Test@1234')]);

        $response = $this->call('PATCH', 'password', [
            'old_password' => 'Test@1234',
            'new_password' => 'NewTest@1234',
            'confirm_password' => 'NewTest@1234',
        ]);

        // Assert the password has been updated correctly
        $this->assertTrue(\Hash::check('NewTest@1234', \Auth::user()->getAuthPassword()));

        // Assert the old password no longer works
        $this->assertFalse(\Hash::check('Test@1234', \Auth::user()->getAuthPassword()));

        $this->assertEquals(session('success1'), 'Updated Successfully');
    }

    public function testPasswordResetLinkExpiredAfterUpdatingThePasswordFromUI()
    {
        $password = new \App\Model\User\Password();

        $user = \Auth::user();
        $token = str_random(40);
        $activate = $password->create(['email' => $user->email, 'token' => $token, 'created_at' => \Carbon\Carbon::now()]);

        $this->assertEquals(1, Password::where('email', $user->email)->get()->count());

        \Auth::user()->update(['password' => \Hash::make('Test@1234')]);

        Password::where('email', $user->email)->get();

        $response = $this->call('PATCH', 'password', [
            'old_password' => 'Test@1234',
            'new_password' => 'NewTest@1234',
            'confirm_password' => 'NewTest@1234',
        ]);

        $this->assertEquals(0, Password::where('email', $user->email)->get()->count());
    }
}
