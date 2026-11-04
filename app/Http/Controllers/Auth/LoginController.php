<?php

namespace App\Http\Controllers\Auth;

use App\ApiKey;
use App\DefaultPage;
use App\Http\Middleware\BlockFailedVerifications;
use App\Http\Requests\Auth\LoginRequest;
use App\Model\Common\StatusSetting;
use App\SocialLogin;
use App\User;
use Cache;
use Exception;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RateLimiter;
use Session;

class LoginController extends BaseAuthController
{
    use AuthenticatesUsers;

    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * JSON config consumed by the Vue guest login/register SPA page.
     * Mirrors the data that showLoginForm() passed to the blade view.
     */
    public function loginConfig(): JsonResponse
    {
        try {
            $status = StatusSetting::select('msg91_status', 'emailverification_status', 'terms')->first();
            if ($status) {
                $status->terms = (int) $status->terms;
            }

            $apiKeys = ApiKey::select('nocaptcha_sitekey', 'terms_url')->first();
            // `type` is stored capitalised (Google, Github, ...); the SPA looks the keys up in lowercase.
            $social = SocialLogin::whereIn('type', ['google', 'github', 'twitter', 'linkedin'])
                ->pluck('status', 'type')
                ->mapWithKeys(fn ($s, $type): array => [strtolower((string) $type) => (int) $s])
                ->toArray();

            return successResponse('login-config', [
                'status' => $status,
                'apiKeys' => $apiKeys,
                'social' => $social,
            ]);
        } catch (Exception $exception) {
            \Logger::exception($exception);

            return errorResponse(__('message.sorry_something_wrong'));
        }
    }

    public function postLoginAndGetToken(LoginRequest $request): mixed
    {
        Auth::shouldUse('web');

        $response = $this->login($request);

        return $this->returnApiV3LoginResponse($response);
    }

    /**
     * Function returns modified response(if required) for login when called via v3 api.
     */
    private function returnApiV3LoginResponse(mixed $response): JsonResponse
    {
        // If not v3 API or user not logged in, just return original response
        if (! isV3Api() || ! Auth::check()) {
            return $response;
        }

        /** @var User $user */
        $user = Auth::user();

        $userInfo = array_merge(
            $user->only(['id', 'first_name', 'last_name', 'email', 'user_name']),
            ['token' => $user->createToken('Billing')->accessToken],
        );

        return successResponse('', $userInfo);
    }

    /**
     * Handle a login request to the application.
     */
    public function login(LoginRequest $request): JsonResponse // 2. Type-hint the LoginRequest
    {
        try {
            // Account-wide limit (any IP): stops guesses spread over many IPs. Checked before the
            // password, so a locked account can't be probed further.
            $accountKey = self::accountLimitKey((string) $request->input('login'));
            if (RateLimiter::tooManyAttempts($accountKey, self::ACCOUNT_MAX_FAILURES)) {
                $seconds = RateLimiter::availableIn($accountKey);

                return errorResponse(
                    __('message.too_many_login_attempts_account', ['minutes' => max(1, (int) ceil($seconds / 60))]),
                    429,
                    ['retry_after' => $seconds]
                );
            }

            // 1. Prepare credentials for both email and username login
            $credentials = $this->buildCredentials($request);

            // 2. Sign in only when the account may log in right away; a verify or 2FA step
            //    first means no login yet (no Login event: cart merge, activity log).
            $pendingStep = null;
            $loggedIn = Auth::attemptWhen($credentials, function (User $user) use (&$pendingStep): bool {
                $pendingStep = match (true) {
                    ! $this->userNeedVerified($user) => 'verify',
                    (bool) $user->is_2fa_enabled => '2fa',
                    default => null,
                };

                return $pendingStep === null;
            }, $request->boolean('remember'));

            if (! $loggedIn && $pendingStep === null) {
                $rateLimitKey = $this->getLoginRateLimitKey($request->input('login'));
                RateLimiter::hit('login-attempt:'.$rateLimitKey, 600);
                RateLimiter::hit($accountKey, self::ACCOUNT_LOCK_SECONDS);

                return errorResponse(__('message.enter_valid_credentials'));
            }

            /** @var User $user */
            $user = Auth::getLastAttempted();

            // Before the verify/2FA steps: the session keeps it until redirectPath() runs after them.
            self::rememberRedirect($request->input('redirect'));

            // Locked out of that step (too many codes / wrong codes): say so here rather than
            // sending them to a page that can only bounce them back to login.
            if ($pendingStep !== null && ($lockout = resolve(BlockFailedVerifications::class)->lockoutMessage($pendingStep, (string) $user->id))) {
                return errorResponse($lockout, 429);
            }

            if ($pendingStep === 'verify') {
                return $this->handleUnverifiedUser($user);
            }
            if ($pendingStep === '2fa') {
                return $this->handleTwoFactorAuthentication($request, $user);
            }

            // 3. Fresh session id for the signed-in user.
            Session::regenerate();

            return successResponse('', ['redirect' => $this->redirectPath()]);
        } catch (Exception $exception) {
            \Logger::exception($exception);

            return errorResponse(__('message.sorry_something_wrong'));
        }
    }

    /**
     * Build the credentials array for authentication.
     * Allows login with either email or username.
     *
     * @return array<mixed>
     */
    private function buildCredentials(Request $request): array
    {
        $loginInput = $request->input('login');
        // A username can itself be an email (it starts as a copy of the email and doesn't follow
        // later email changes), so an email-shaped input matching no email is tried as a username.
        $loginType = filter_var($loginInput, FILTER_VALIDATE_EMAIL) && User::where('email', $loginInput)->exists()
            ? 'email'
            : 'user_name';

        return [
            $loginType => $loginInput,
            'password' => $request->input('password'),
            'active' => 1,
        ];
    }

    /**
     * Handle redirection for an unverified user.
     */
    private function handleUnverifiedUser(User $user): JsonResponse
    {
        Session::put([
            'justStarted' => true,
            'verification_user_id' => $user->id,
        ]);

        Session::flash('user', $user);

        return successResponse('', ['redirect' => url('verify')]);
    }

    /**
     * Prepare the session and redirect for 2FA.
     */
    private function handleTwoFactorAuthentication(Request $request, User $user): JsonResponse
    {
        Session::put([
            'justStarted' => true,
            'verification_user_id' => $user->id,
            '2fa:user:id' => $user->id,
            'remember:user:id' => $request->boolean('remember'),
        ]);

        return successResponse('', ['redirect' => url('verify-2fa')]);
    }

    /**
     * Get the post register / login redirect path.
     *
     * @return string
     */
    public function redirectPath(): UrlGenerator|string
    {
        $auth = Auth::user();

        // Clear rate limit after successful login
        if ($auth) {
            $this->clearRateLimit('login', $auth);
            $this->clearRateLimit('2fa', $auth);
        }

        // Pulled for every role so it's used once.
        $intended = $this->pullIntendedUrl();

        if ($auth && $auth->role === 'user') {
            // Admin pages would only 403 for a user; their default page instead.
            $userCanOpen = $intended !== null && ! preg_match('#^/admin(?:[/?]|\z)#', substr($intended, strlen(url('/'))));

            $target = $userCanOpen ? $intended : (DefaultPage::value('page_url') ?? url('/'));

            // Billing details first; setup then continues to where they were going.
            if ($auth->needsAccountSetup()) {
                redirect()->setIntendedUrl($target);

                return url('account-setup');
            }

            return $target;
        }

        return $intended ?? url('/admin');
    }

    /**
     * Laravel's intended URL — saved when a guest opens a login-only page, or by
     * rememberRedirect() — taken out of the session. Only a page under this app's
     * root counts: "<root>/x", not another host and not the bare root itself (saved
     * as "<root>", no slash), so a guest who just opened the home page still lands
     * on their role's usual page.
     */
    private function pullIntendedUrl(): ?string
    {
        $intended = Session::pull('url.intended');
        $base = url('/');

        if (! is_string($intended) || ! str_starts_with($intended, $base.'/')) {
            return null;
        }

        return $intended;
    }

    /**
     * Sets the SPA login page's ?redirect= as Laravel's intended URL. Only a same-site
     * path ("/x", not "//host", "/\host" or one hiding control characters); anything
     * else is ignored, leaving whatever Laravel already saved.
     */
    public static function rememberRedirect(mixed $path): void
    {
        if (is_string($path) && preg_match('#^/(?![/\\\\])[^\x00-\x20\x7f\\\\]*\z#', $path)) {
            redirect()->setIntendedUrl(url($path));
        }
    }

    /**
     * This function is used to check if the users number and email verified or not.
     *
     * @param  $user
     */
    public function getLoginRateLimitKey(string $emailOrUsername): string
    {
        return md5(request()->ip().':'.strtolower($emailOrUsername));
    }

    /** Failed logins allowed per account (any IP) within ACCOUNT_LOCK_SECONDS; fixed, not escalating. */
    private const ACCOUNT_MAX_FAILURES = 10;

    private const ACCOUNT_LOCK_SECONDS = 3600;

    private static function accountLimitKey(string $login): string
    {
        return 'login-account:'.md5(strtolower(trim($login)));
    }

    private function clearRateLimit(string $context, User $user): void
    {
        switch ($context) {
            case 'login':
                $identifier = $this->getLoginRateLimitKey($user->email ?? $user->username); // @phpstan-ignore property.notFound
                // The account-wide counter too, under whichever name (email / username) was typed.
                $keys = ['login-attempt:'.$identifier, self::accountLimitKey((string) $user->email), self::accountLimitKey((string) $user->user_name)];
                break;

            case '2fa':
                $identifier = $user->id;
                $keys = [
                    '2fa-code:'.$user->id,
                    'recovery-code:'.$user->id,
                ];
                break;

            default:
                return; // do nothing if context not supported
        }

        foreach ($keys as $key) {
            RateLimiter::clear($key);
        }

        Cache::forget(sprintf('penalty_level:%s:%s', $context, $identifier));
        Cache::forget(sprintf('penalty_applied:%s:%s', $context, $identifier));
    }
}
