<?php

namespace Tests\Unit\Backend\Http\Controllers\Auth;

use App\DefaultPage;
use App\Http\Middleware\Install;
use App\Model\Common\StatusSetting;
use App\SocialLogin;
use App\User;
use App\UserBackupCodes;
use Hash;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Testing\TestResponse;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialUser;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\DBTestCase;

/**
 * After login (password, 2FA or social) the user goes back to Laravel's intended URL:
 * the protected page they opened as a guest, or the SPA login page's ?redirect=.
 */
#[Group('login-redirect')]
class LoginRedirectTest extends DBTestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        // Real middleware (session, auth redirects); only the installer check is skipped.
        $this->withoutMiddleware(Install::class);
        DefaultPage::query()->delete(); // a user's default landing page is url('/')
        StatusSetting::create(['emailverification_status' => 0, 'msg91_status' => 0, 'recaptcha_status' => 0]);
    }

    private function login(User $user, mixed $redirect = null, string $password = 'password'): TestResponse
    {
        return $this->postJson('/login', array_filter([
            'email_username' => $user->email,
            'password1' => $password,
            'login' => ['pot_field' => '', 'time_field' => encrypt(time() - 10)],
            'redirect' => $redirect,
        ], fn ($value): bool => $value !== null));
    }

    private function user(array $attributes = []): User
    {
        return User::factory()->create($attributes + [
            'role' => 'user', 'password' => Hash::make('password'), 'is_2fa_enabled' => 0,
            'active' => 1, 'email_verified' => 1, 'mobile_verified' => 1,
        ]);
    }

    private function admin(): User
    {
        return $this->user(['role' => 'admin']);
    }

    /** A guest opening a login-only page: Laravel bounces them to login and saves the page. */
    private function openAsGuest(string $path): void
    {
        $this->get($path)->assertRedirect();
        $this->assertSame(url($path), session('url.intended'));
    }

    // ── SPA ?redirect= ───────────────────────────────────────────────

    public function test_user_goes_back_to_the_redirect_page(): void
    {
        $this->login($this->user(), '/store?group=1')
            ->assertOk()
            ->assertJsonPath('data.redirect', url('/store?group=1'));

        $this->assertNull(session('url.intended')); // used once
    }

    public function test_admin_goes_back_to_the_redirect_page(): void
    {
        $this->login($this->admin(), '/store?group=1')->assertJsonPath('data.redirect', url('/store?group=1'));
    }

    public function test_without_an_intended_page_a_user_lands_on_the_default_page(): void
    {
        $this->login($this->user())->assertJsonPath('data.redirect', url('/'));
    }

    public function test_without_an_intended_page_an_admin_lands_on_admin(): void
    {
        $this->login($this->admin())->assertJsonPath('data.redirect', url('/admin'));
    }

    // ── Pages opened directly as a guest ─────────────────────────────

    public function test_user_goes_back_to_the_client_page_they_opened(): void
    {
        $this->openAsGuest('/my-orders');

        $this->login($this->user())->assertJsonPath('data.redirect', url('/my-orders'));
    }

    public function test_admin_goes_back_to_the_admin_page_they_opened(): void
    {
        $this->openAsGuest('/admin/orders/5');

        $this->login($this->admin())->assertJsonPath('data.redirect', url('/admin/orders/5'));
    }

    public function test_user_is_never_sent_to_an_admin_page(): void
    {
        $this->openAsGuest('/admin/orders/5');

        $this->login($this->user())->assertJsonPath('data.redirect', url('/'));
        $this->assertNull(session('url.intended'));
    }

    public function test_user_redirect_to_an_admin_page_is_also_ignored(): void
    {
        $this->login($this->user(), '/admin?tab=1')->assertJsonPath('data.redirect', url('/'));
    }

    public function test_opening_the_home_page_keeps_the_admin_landing_page(): void
    {
        $this->openAsGuest('/');

        $this->login($this->admin())->assertJsonPath('data.redirect', url('/admin'));
    }

    public function test_user_default_page_still_wins_over_a_home_page_visit(): void
    {
        DefaultPage::create(['name' => 'Store', 'page_url' => url('/store')]);
        $this->openAsGuest('/');

        $this->login($this->user())->assertJsonPath('data.redirect', url('/store'));
    }

    public function test_redirect_overrides_an_older_intended_page(): void
    {
        $this->openAsGuest('/my-invoices');

        $this->login($this->user(), '/store')->assertJsonPath('data.redirect', url('/store'));
    }

    // ── Safety ──────────────────────────────────────────────────────

    public static function unsafeRedirects(): array
    {
        return [
            'other site' => ['https://evil.test/x'],
            'protocol-relative' => ['//evil.test'],
            'backslash host' => ['/\\evil.test'],
            'tab hiding //' => ["/\t/evil.test"],
            'newline' => ["/store\n"],
            'no leading slash' => ['store'],
            'array' => [['/store']],
        ];
    }

    #[DataProvider('unsafeRedirects')]
    public function test_unsafe_redirect_is_ignored(mixed $redirect): void
    {
        $this->login($this->user(), $redirect)
            ->assertOk()
            ->assertJsonPath('data.redirect', url('/'));
    }

    public function test_unsafe_redirect_leaves_the_page_laravel_saved(): void
    {
        $this->openAsGuest('/my-orders');

        $this->login($this->user(), '//evil.test')->assertJsonPath('data.redirect', url('/my-orders'));
    }

    public function test_intended_url_on_another_host_is_ignored(): void
    {
        session(['url.intended' => 'https://evil.test/my-orders']);

        $this->login($this->user())->assertJsonPath('data.redirect', url('/'));
    }

    public function test_wrong_password_does_not_save_the_redirect(): void
    {
        $this->login($this->user(), '/store', 'wrong-password');

        $this->assertNull(session('url.intended'));
    }

    // ── Verify / 2FA / social ────────────────────────────────────────

    public function test_2fa_user_does_2fa_first_then_goes_to_the_redirect_page(): void
    {
        $user = $this->user(['is_2fa_enabled' => 1]);
        UserBackupCodes::create(['user_id' => $user->id, 'backup_codes' => 'recovery-abc123']);

        $this->login($user, '/store?group=1')->assertJsonPath('data.redirect', url('verify-2fa'));
        $this->assertGuest();

        $this->postJson('/verify-recovery-code', [
            'rec_code' => 'recovery-abc123',
            'recovery_code' => ['pot_field' => '', 'time_field' => encrypt(time() - 10)],
        ])->assertOk()->assertJsonPath('data.redirect', url('/store?group=1'));

        $this->assertAuthenticatedAs($user);
        $this->assertNull(session('url.intended'));
    }

    public function test_unverified_user_verifies_first_and_the_page_is_kept_for_later(): void
    {
        StatusSetting::query()->update(['emailverification_status' => 1]);

        $this->login($this->user(['email_verified' => 0]), '/store')->assertJsonPath('data.redirect', url('verify'));

        $this->assertGuest();
        $this->assertSame(url('/store'), session('url.intended'));
    }

    private function google(string $email): void
    {
        SocialLogin::where('type', 'google')->update(['status' => 1, 'client_id' => 'x', 'client_secret' => 'y']);

        $driver = Mockery::mock(Provider::class);
        $driver->shouldReceive('redirect')->andReturn(new RedirectResponse('https://accounts.google.test/auth'));
        $driver->shouldReceive('user')->andReturn((new SocialUser)->setRaw(['email_verified' => true])->map(['id' => 'pid-1', 'name' => 'Jane Doe', 'email' => $email]));
        Socialite::shouldReceive('driver')->with('google')->andReturn($driver);
    }

    public function test_social_login_goes_back_to_the_redirect_page(): void
    {
        $user = $this->user();
        $this->google($user->email);

        $this->getJson('/auth/redirect/google?redirect='.urlencode('/store?group=1'))->assertOk();
        $this->get('/auth/callback/google')->assertRedirect(url('/store?group=1'));

        $this->assertAuthenticatedAs($user);
        $this->assertNull(session('url.intended'));
    }

    public function test_social_login_goes_back_to_the_page_opened_as_guest(): void
    {
        $user = $this->user();
        $this->google($user->email);
        $this->openAsGuest('/my-orders');

        $this->getJson('/auth/redirect/google')->assertOk();
        $this->get('/auth/callback/google')->assertRedirect(url('/my-orders'));
    }
}
