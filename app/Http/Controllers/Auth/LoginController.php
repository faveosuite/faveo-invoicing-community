<?php

namespace App\Http\Controllers\Auth;

use App\ApiKey;
use App\DefaultPage;
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
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('guest')->except(['logout', 'loginConfig']);
        $this->middleware(['blockFailedVerifications:login', 'recaptcha:login'])->only('login');
    }

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
            $location = getLocation();

            // `type` is stored capitalised (Google, Github, ...); the SPA looks the keys up in lowercase.
            $social = SocialLogin::whereIn('type', ['google', 'github', 'twitter', 'linkedin'])
                ->pluck('status', 'type')
                ->mapWithKeys(fn ($s, $type): array => [strtolower((string) $type) => (int) $s])
                ->toArray();

            return successResponse('login-config', [
                'status' => $status,
                'apiKeys' => $apiKeys,
                'location' => $location,
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
            // 1. Prepare credentials for both email and username login
            $credentials = $this->buildCredentials($request);

            // 2. Attempt to authenticate the user
            if (! Auth::attempt($credentials, $request->boolean('remember'))) {
                $rateLimitKey = $this->getLoginRateLimitKey($request->input('email_username'));
                RateLimiter::hit('login-attempt:'.$rateLimitKey, 600);

                return errorResponse(__('message.enter_valid_credentials'));
            }

            /** @var User $user */
            $user = Auth::user();

            // 3. Handle post-authentication checks (Verification)
            if (! $this->userNeedVerified($user)) {
                return $this->handleUnverifiedUser($user);
            }

            // 4. Check if the user has 2FA enabled
            if ($user->is_2fa_enabled) {
                return $this->handleTwoFactorAuthentication($request, $user);
            }

            // 5. Regenerate session for security
            Session::regenerate();

            $this->logActivityLogin($user);

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
        $loginInput = $request->input('email_username');
        $loginType = filter_var($loginInput, FILTER_VALIDATE_EMAIL) ? 'email' : 'user_name';

        return [
            $loginType => $loginInput,
            'password' => $request->input('password1'),
            'active' => 1,
        ];
    }

    /**
     * Handle redirection for an unverified user.
     */
    private function handleUnverifiedUser(User $user): JsonResponse
    {
        Auth::logout();

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
        Auth::logout();

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

        if ($auth && $auth->role === 'user') {
            return DefaultPage::value('page_url') ?? url('/');
        }

        return url('/admin');
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

    private function clearRateLimit(string $context, User $user): void
    {
        switch ($context) {
            case 'login':
                $identifier = $this->getLoginRateLimitKey($user->email ?? $user->username); // @phpstan-ignore property.notFound
                $keys = ['login-attempt:'.$identifier];
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

    public function logActivityLogin(mixed $user): void
    {
        if (! $user) {
            return;
        }

        $userUrl = url('clients/'.$user->id);

        $name = e($user->first_name.' '.$user->last_name);
        $message = sprintf("User <a href='%s'><strong>%s</strong></a> logged in successfully.", $userUrl, $name);

        logActivity(
            $message,
            'login',
            'Authentication',
            $user,
        );
    }
}
