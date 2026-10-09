<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Http\Controllers\Auth;

use App\SocialAccount;
use App\SocialLogin;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialUser;
use Mockery;
use Tests\TestCase;

class SocialAuthControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\App\Http\Middleware\Install::class);
    }

    private function provider(?string $email, array $raw = [], string $id = 'pid-1'): void
    {
        SocialLogin::where('type', 'google')->update(['status' => 1, 'client_id' => 'x', 'client_secret' => 'y', 'redirect_url' => 'http://localhost/auth/callback/google']);

        $su = (new SocialUser)->setRaw($raw)->map(['id' => $id, 'name' => 'Jane Doe', 'email' => $email]);
        $driver = Mockery::mock(Provider::class);
        $driver->shouldReceive('user')->andReturn($su);
        Socialite::shouldReceive('driver')->with('google')->andReturn($driver);
    }

    public function test_disabled_provider_goes_back_to_login(): void
    {
        SocialLogin::where('type', 'google')->update(['status' => 0]);

        $this->get('/auth/callback/google')->assertRedirect(url('login').'?social_error=failed');
    }

    public function test_cancelled_at_provider(): void
    {
        $this->get('/auth/callback/google?error=access_denied')->assertRedirect(url('login').'?social_error=cancelled');
    }

    public function test_unknown_user_is_parked_for_profile_completion(): void
    {
        $this->provider('new-'.uniqid().'@example.com', ['email_verified' => true]);

        $this->get('/auth/callback/google')->assertRedirect(url('social/complete'));
        $this->assertSame('Jane', session('social_signup.first_name'));
        $this->assertSame('Doe', session('social_signup.last_name'));
        $this->assertTrue(session('social_signup.email_verified'));
    }

    public function test_unverified_email_never_links_to_existing_account(): void
    {
        $user = User::factory()->create(['role' => 'user', 'active' => 1]);
        $this->provider($user->email, ['email_verified' => false]);

        $this->get('/auth/callback/google')->assertRedirect(url('login').'?social_error=email_exists');
        $this->assertSame(0, SocialAccount::where('user_id', $user->id)->count());
        $this->assertGuest();
    }

    public function test_admin_account_is_never_linked_by_email(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'active' => 1]);
        $this->provider($admin->email, ['email_verified' => true]);

        $this->get('/auth/callback/google')->assertRedirect(url('login').'?social_error=email_exists');
        $this->assertGuest();
    }

    public function test_verified_email_links_and_logs_in(): void
    {
        $user = User::factory()->create(['role' => 'user', 'active' => 1, 'email_verified' => 1, 'mobile_verified' => 1, 'is_2fa_enabled' => 0]);
        $this->provider($user->email, ['email_verified' => true]);

        $this->get('/auth/callback/google')->assertRedirect();
        $this->assertAuthenticatedAs($user);
        $this->assertSame(1, SocialAccount::where(['user_id' => $user->id, 'provider' => 'google'])->count());
    }

    public function test_inactive_user_is_not_reactivated(): void
    {
        $user = User::factory()->create(['role' => 'user', 'active' => 0]);
        SocialAccount::create(['user_id' => $user->id, 'provider' => 'google', 'provider_id' => 'pid-1']);
        $this->provider($user->email, ['email_verified' => true]);

        $this->get('/auth/callback/google')->assertRedirect(url('login').'?social_error=inactive');
        $this->assertSame(0, (int) $user->fresh()->active);
        $this->assertGuest();
    }

    public function test_complete_without_session_is_rejected(): void
    {
        $this->postJson('/auth/social-complete', [])->assertStatus(400);
    }

    public function test_complete_creates_verified_user_and_logs_in(): void
    {
        $email = 'social-'.uniqid().'@example.com';
        \App\Model\Common\StatusSetting::query()->update(['terms' => 0, 'emailverification_status' => 0, 'msg91_status' => 0]);

        $this->withSession(['social_signup' => [
            'provider' => 'google', 'provider_id' => 'pid-new', 'first_name' => 'Jane', 'last_name' => 'Doe',
            'email' => $email, 'email_verified' => true,
        ]])->postJson('/auth/social-complete', [
            'first_name' => 'Jane', 'last_name' => 'Doe', 'company' => 'Acme', 'address' => '1 Main St',
            'country' => 'IN', 'mobile' => '9876543210', 'mobile_code' => '91', 'mobile_country_iso' => 'in',
        ])->assertOk();

        $user = User::where('email', $email)->firstOrFail();
        $this->assertSame(1, (int) $user->email_verified);
        $this->assertSame('user', $user->role);
        $this->assertSame(1, SocialAccount::where(['user_id' => $user->id, 'provider_id' => 'pid-new'])->count());
        $this->assertAuthenticatedAs($user);
        $this->assertNull(session('social_signup'));
    }

    public function test_complete_validates_required_fields(): void
    {
        $this->withSession(['social_signup' => [
            'provider' => 'twitter', 'provider_id' => 'p', 'first_name' => 'J', 'last_name' => '', 'email' => null, 'email_verified' => false,
        ]])->postJson('/auth/social-complete', [])->assertStatus(412); // this app reports validation failures as 412
        $this->assertGuest();
    }
}
