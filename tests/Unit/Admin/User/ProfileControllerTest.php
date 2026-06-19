<?php

namespace Tests\Unit\Admin\User;

use App\Http\Controllers\User\ProfileController;
use App\Model\User\Password;
use App\User;
use Auth;
use Hash;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Str;
use Mockery;
use Tests\DBTestCase;

class ProfileControllerTest extends DBTestCase
{
    use DatabaseTransactions;

    protected $profileController;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
        $this->getLoggedInUser('admin');
        $this->profileController = Mockery::mock(ProfileController::class)->makePartial();
        $this->app->instance(ProfileController::class, $this->profileController);
    }

    protected function createUser(array $attributes = []): User
    {
        return User::factory()->create($attributes);
    }

    public function test_update_profile_without_any_errors(): void
    {
        $this->call('PATCH', 'profile', [
            'first_name' => 'update first',
            'last_name' => 'update last',
            'company' => 'update company',
            'mobile' => '0123456789',
            'address' => 'update address',
            'timezone_id' => '1',
            'user_name' => 'update name',
            'email' => 'updated@example.com',
            'country' => 'USA',
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

    public function test_update_profile_with_errors(): void
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

    public function test_update_password_success(): void
    {
        // Manually update the password first
        Auth::user()->update(['password' => Hash::make('Test@1234')]);

        $this->call('PATCH', 'password', [
            'old_password' => 'Test@1234',
            'new_password' => 'NewTest@1234',
            'confirm_password' => 'NewTest@1234',
        ]);

        // Assert the password has been updated correctly
        $this->assertTrue(Hash::check('NewTest@1234', Auth::user()->getAuthPassword()));

        // Assert the old password no longer works
        $this->assertFalse(Hash::check('Test@1234', Auth::user()->getAuthPassword()));

        $this->assertEquals(session('success1'), 'Updated Successfully');
    }

    public function test_password_reset_link_expired_after_updating_the_password_from_ui(): void
    {
        $password = new Password;

        $user = Auth::user();
        $token = Str::random(40);
        $password->create(['email' => $user->email, 'token' => $token, 'created_at' => Date::now()]);

        $this->assertEquals(1, Password::where('email', $user->email)->get()->count());

        Auth::user()->update(['password' => Hash::make('Test@1234')]);

        Password::where('email', $user->email)->get();

        $this->call('PATCH', 'password', [
            'old_password' => 'Test@1234',
            'new_password' => 'NewTest@1234',
            'confirm_password' => 'NewTest@1234',
        ]);

        $this->assertEquals(0, Password::where('email', $user->email)->get()->count());
    }
}
