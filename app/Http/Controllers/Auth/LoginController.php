<?php

namespace App\Http\Controllers\Auth;

use App\ApiKey;
use App\Http\Requests\Auth\LoginRequest;
use App\Model\Common\ChatScript;
use App\Model\Common\Country;
use App\Model\Common\StatusSetting;

use App\SocialLogin;
use App\User;
use Cache;
use Config;
use Exception;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
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
        $this->middleware('guest')->except(['logout', 'store-basic-details', 'loginConfig']);
        $this->middleware(['blockFailedVerifications:login', 'recaptcha:login'])->only('login');
    }

    /**
     * JSON config consumed by the Vue guest login/register SPA page.
     * Mirrors the data that showLoginForm() passed to the blade view.
     */
    public function loginConfig()
    {
        try {
            $status = StatusSetting::select('msg91_status', 'emailverification_status', 'terms')->first();
            if ($status) {
                $status->terms = (bool) $status->terms;
            }

            $apiKeys = ApiKey::select('nocaptcha_sitekey', 'terms_url')->first();
            $analyticsTag = ChatScript::where('google_analytics', 1)->where('on_registration', 1)->value('google_analytics_tag');
            $location = getLocation();

            $social = SocialLogin::whereIn('type', ['google', 'github', 'twitter', 'linkedin'])
                ->pluck('status', 'type')
                ->map(fn ($s): int => (int) $s)
                ->toArray();

            return successResponse('login-config', [
                'status' => $status,
                'apiKeys' => $apiKeys,
                'analyticsTag' => $analyticsTag,
                'location' => $location,
                'social' => $social,
            ]);
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    public function postLoginAndGetToken(LoginRequest $request)
    {
        Auth::shouldUse('web');

        $response = $this->login($request);

        return $this->returnApiV3LoginResponse($response);
    }

    /**
     * Function returns modified response(if required) for login when called via v3 api.
     */
    private function returnApiV3LoginResponse($response)
    {
        // If not v3 API or user not logged in, just return original response
        if (! isV3Api() || ! Auth::check()) {
            return $response;
        }

        $user = Auth::user();

        $userInfo = array_merge(
            $user->only(['id', 'first_name', 'last_name', 'email', 'user_name']),
            ['token' => $user->createToken('Billing')->accessToken],
        );

        return successResponse('', $userInfo);
    }

    /**
     * Handle a login request to the application.
     *
     * @return
     */
    public function login(LoginRequest $request) // 2. Type-hint the LoginRequest
    {
        try {
            // 1. Prepare credentials for both email and username login
            $credentials = $this->buildCredentials($request);

            // 2. Attempt to authenticate the user
            if (! Auth::attempt($credentials, $request->boolean('remember'))) {
                $rateLimitKey = $this->getLoginRateLimitKey($request->input('email_username'));
                RateLimiter::hit('login-attempt:' . $rateLimitKey, 600);

                return errorResponse(__('message.enter_valid_credentials'));
            }

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
        } catch(Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    /**
     * Build the credentials array for authentication.
     * Allows login with either email or username.
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
    private function handleUnverifiedUser(User $user)
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
    private function handleTwoFactorAuthentication(Request $request, User $user)
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
    public function redirectPath(): \Illuminate\Contracts\Routing\UrlGenerator|string
    {
        $auth = Auth::user();

        // Clear rate limit after successful login
        if ($auth) {
            $this->clearRateLimit('login', $auth);
            $this->clearRateLimit('2fa', $auth);
        }

        return url(($auth && $auth->role === 'user') ? '/' : '/admin');
    }

    /**
     * This function redirects to the social login based on the provider(twitter,gitHub).
     *
     * @param  $provider
     * @param
     * @return RedirectResponse
     *
     * @throws
     */
    public function redirectToGithub($provider)
    {
        $details = SocialLogin::where('type', $provider)->first();

        Config::set(sprintf('services.%s.redirect', $provider), $details->redirect_url);
        Config::set(sprintf('services.%s.client_id', $provider), $details->client_id);
        Config::set(sprintf('services.%s.client_secret', $provider), $details->client_secret);

        //return Socialite::driver($provider)->redirect();
        return successResponse('success', ['url' => Socialite::driver($provider)->redirect()->getTargetUrl()]);
    }

    /**
     * This function performs the whole social login operations(creating new user, if existing user just logging in).
     *
     * @param  $provider
     * @param
     * @return RedirectResponse
     *
     * @throws
     */
    public function handler($provider)
    {
        $details = SocialLogin::where('type', $provider)->first();
        Config::set(sprintf('services.%s.redirect', $provider), $details->redirect_url);
        Config::set(sprintf('services.%s.client_id', $provider), $details->client_id);
        Config::set(sprintf('services.%s.client_secret', $provider), $details->client_secret);

        $githubUser = Socialite::driver($provider)->user();
        $location = getLocation();

        $state = getStateByCode($location['iso_code'], $location['state']);

        $existingUser = User::where('email', $githubUser->getEmail())->first();

        if ($existingUser) {
            $existingUser->active = '1';

            $existingUser->role = $existingUser->role == 'admin' ? 'admin' : 'user';

            $existingUser->save();
            $user = $existingUser;
        } else {
            $user = User::create([
                'email' => $githubUser->getEmail(),
                'user_name' => $githubUser->getEmail(),
                'first_name' => $githubUser->getName(),
                'ip' => $location['ip'],
                'timezone_id' => getTimezoneByName($location['timezone']),
                'state' => $state['id'],
                'town' => $location['city'],
                'country' => Country::where('country_name', strtoupper((string) $location['country']))->value('country_code_char2'),
            ]);
            $user->active = 1;
            $user->role = 'user';
            $user->save();
        }

        if ($user && ($user->active == 1 && $user->mobile_verified !== 1)) {
            return redirect('verify')->with('user', $user);
        }

        Auth::login($user);

        if (\Auth::user()->is_2fa_enabled == 1) {
            $userId = \Auth::user()->id;
            Session::put([
                '2fa:user:id' => $userId,
                'remember:user:id' => false,
            ]);
            \Auth::logout();

            return redirect(url('verify-2fa'));
        }

        if (Auth::check()) {
            return redirect($this->redirectPath());
        }
    }

    /**
     * This function stores basic details for social logins.
     *
     * @param
     * @return
     * @throws
     */
    public function storeBasicDetails(Request $request)
    {
        try {
            $this->validate($request, [
                'company' => 'required|string',
                'address' => 'required|string',
            ],
                [
                    'company.required' => __('validation.company_validation.company_required'),
                    'company.string' => __('validation.company_validation.company_string'),
                    'address.required' => __('validation.company_validation.address_required'),
                    'address.string' => __('validation.company_validation.company_string'),
                ]);

            $user = Auth::user();
            $user->company = $request->company;
            $user->address = $request->address;
            $user->save();

            return successResponse(__('message.updated-successfully'));
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    /**
     * This function is used to check if the users number and email verified or not.
     *
     * @param  $user
     * @param
     *
     * @throws
     */
    public function getLoginRateLimitKey(string $emailOrUsername): string
    {
        return md5(request()->ip().':'.strtolower($emailOrUsername));
    }

    private function clearRateLimit(string $context, User $user): void
    {
        switch ($context) {
            case 'login':
                $identifier = $this->getLoginRateLimitKey($user->email ?? $user->username);
                $keys = ['login-attempt:' . $identifier];
                break;

            case '2fa':
                $identifier = $user->id;
                $keys = [
                    '2fa-code:' . $user->id,
                    'recovery-code:' . $user->id,
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

    public function logActivityLogin($user): void
    {
        if (! $user) {
            return;
        }

        $userUrl = url('clients/' . $user->id);

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
