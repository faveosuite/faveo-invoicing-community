<?php

namespace App\Http\Controllers\Auth;

use App\ApiKey;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Model\Common\Bussiness;
use App\Model\Common\ChatScript;
use App\Model\Common\Country;
use App\Model\Common\StatusSetting;
use App\SocialLogin;
use App\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use RateLimiter;
use Session;

class LoginController extends Controller
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
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except(['logout', 'store-basic-details']);
    }

    /**
     * This function returns to the login page.
     *
     * @param
     * @param
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     *
     * @throws
     */
    public function showLoginForm()
    {
        try {
            $bussinesses = Bussiness::pluck('name', 'short')->toArray();
            $status = StatusSetting::select('recaptcha_status', 'v3_recaptcha_status', 'msg91_status', 'emailverification_status', 'terms')->first();
            $apiKeys = ApiKey::select('nocaptcha_sitekey', 'captcha_secretCheck', 'msg91_auth_key', 'terms_url')->first();
            $analyticsTag = ChatScript::where('google_analytics', 1)->where('on_registration', 1)->value('google_analytics_tag');
            $location = getLocation();

            $google_status = SocialLogin::select('status')->where('type', 'google')->value('status');
            $github_status = SocialLogin::select('status')->where('type', 'github')->value('status');
            $twitter_status = SocialLogin::select('status')->where('type', 'twitter')->value('status');
            $linkedin_status = SocialLogin::select('status')->where('type', 'linkedin')->value('status');

            return view('themes.default1.front.auth.login-register', compact('bussinesses', 'location', 'status', 'apiKeys', 'analyticsTag', 'google_status', 'github_status', 'linkedin_status', 'twitter_status'));
        } catch (\Exception $ex) {
            app('log')->error($ex->getMessage());
            $error = $ex->getMessage();
        }
    }

    /**
     * Handle a login request to the application.
     *
     * @param  LoginRequest  $request
     * @return RedirectResponse
     */
    public function login(LoginRequest $request) // 2. Type-hint the LoginRequest
    {
        // 1. Prepare credentials for both email and username login
        $credentials = $this->buildCredentials($request);

        $rateLimitKey = $this->getLoginRateLimitKey($request->input('email_username'));
        RateLimiter::hit("login-attempt:{$rateLimitKey}");

        // 2. Attempt to authenticate the user
        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withInput($request->only('email_username', 'remember'))
                ->with('fails', __('message.enter_valid_credentials'));
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

        $this->convertCart();

        activity()->log('Logged In');

        return redirect()->to($this->redirectPath());
    }

    /**
     * Build the credentials array for authentication.
     * Allows login with either email or username.
     */
    private function buildCredentials(Request $request): array
    {
        $loginInput = $request->input('email_username');
        $loginType = filter_var($loginInput, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

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
            'justStarted_verify' => true,
            'verification_user_id' => $user->id,
        ]);

        return redirect('verify')->with('user', $user);
    }

    /**
     * Prepare the session and redirect for 2FA.
     */
    private function handleTwoFactorAuthentication(Request $request, User $user)
    {
        Auth::logout();

        Session::put([
            'justStarted_2fa' => true,
            'verification_user_id' => $user->id,
            '2fa:user:id' => $user->id,
            'remember:user:id' => $request->boolean('remember'),
        ]);

        return redirect('verify-2fa');
    }

    /**
     * Get the post register / login redirect path.
     *
     * @return string
     */
    public function redirectPath()
    {
        $auth = Auth::user();

        // Clear rate limit after successful login
        if ($auth) {
            $this->clearRateLimit('login', $auth);
            $this->clearRateLimit('2fa', $auth);
        }

        $defaultPath = ($auth && $auth->role === 'user')
            ? '/client-dashboard'
            : '/';

        return redirect()->intended($defaultPath)->getTargetUrl();
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

        \Config::set("services.$provider.redirect", $details->redirect_url);
        \Config::set("services.$provider.client_id", $details->client_id);
        \Config::set("services.$provider.client_secret", $details->client_secret);

        return Socialite::driver($provider)->redirect();
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
        \Config::set("services.$provider.redirect", $details->redirect_url);
        \Config::set("services.$provider.client_id", $details->client_id);
        \Config::set("services.$provider.client_secret", $details->client_secret);

        $githubUser = Socialite::driver($provider)->user();
        $location = getLocation();

        $state_code = $location['iso_code'].'-'.$location['state'];

        $state = getStateByCode($state_code);

        $existingUser = User::where('email', $githubUser->getEmail())->first();

        if ($existingUser) {
            $existingUser->active = '1';

            if ($existingUser->role == 'admin') {
                $existingUser->role = 'admin';
            } else {
                $existingUser->role = 'user';
            }

            $existingUser->save();
            $user = $existingUser;
        } else {
            $user = User::create([
                'email' => $githubUser->getEmail(),
                'user_name' => $githubUser->getEmail(),
                'first_name' => $githubUser->getName(),
                'active' => '1',
                'role' => 'user',
                'ip' => $location['ip'],
                'timezone_id' => getTimezoneByName($location['timezone']),
                'state' => $state['id'],
                'town' => $location['city'],
                'country' => Country::where('country_name', strtoupper($location['country']))->value('country_code_char2'),
            ]);
        }

        if ($user && ($user->active == 1 && $user->mobile_verified !== 1)) {//check for mobile verification
            return redirect('verify')->with('user', $user);
        }

        Auth::login($user);

        if (\Auth::user()->is_2fa_enabled == 1) {//check for 2fa
            $userId = \Auth::user()->id;
            Session::put('2fa:user:id', $userId);
            \Auth::logout();

            return redirect('2fa/validate');
        }
        if (Auth::check()) {
            $this->convertCart();

            return redirect($this->redirectPath());
        }
    }

    /**
     * This function stores basic details for social logins.
     *
     * @param  Request  $request
     * @param
     * @return RedirectResponse
     *
     * @throws
     */
    public function storeBasicDetailsss(Request $request)
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

            return redirect()->back();
        } catch (\Exception $e) {
            Session::flash('error', __('message.please_enter_details'));
        }
    }

    /**
     * This function performs operation on cart after logging in(scenario:when we add products to the cart before logging in, to convert it for the logged-in user).
     *
     * @param
     * @param
     *
     * @throws
     */
    private function convertCart()
    {
        $contents = \Cart::getContent();
        foreach ($contents as $content) {
            $cartcont = new \App\Http\Controllers\Front\CartController();
            $price = $cartcont->planCost($content->id, \Auth::user()->id);
            if ($content->attributes->domain != '') {
                $price = $price * $content->attributes->agents;
            }
            \Cart::update($content->id, [
                'price' => $price,
                'attributes' => [
                    'currency' => getCurrencyForClient(\Auth::user()->country),
                    'symbol' => \App\Model\Payment\Currency::where('code', getCurrencyForClient(\Auth::user()->country))->value('symbol'),
                    'agents' => $content->attributes->agents,
                    'domain' => $content->attributes->domain,
                ],
            ]);
        }
        Session::forget('toggleState');
    }

    /**
     * This function is used to check if the users number and email verified or not.
     *
     * @param  $user
     * @param
     * @return bool
     *
     * @throws
     */
    private function userNeedVerified($user)
    {
        $setting = StatusSetting::first(['emailverification_status', 'msg91_status']);

        if ($setting->emailverification_status == 1 && $user->email_verified != 1) {
            return false;
        }

        if ($setting->msg91_status == 1 && $user->mobile_verified != 1) {
            return false;
        }

        if ($user->active != 1) {
            return false;
        }

        return true;
    }

    public function getLoginRateLimitKey(string $emailOrUsername): string
    {
        $userId = User::query()
            ->where('email', $emailOrUsername)
            ->orWhere('user_name', $emailOrUsername)
            ->value('id');

        return $userId ?? md5(request()->ip() . ':' . $emailOrUsername);
    }

    private function clearRateLimit(string $context, User $user): void
    {
        switch ($context) {
            case 'login':
                $identifier = $this->getLoginRateLimitKey($user->email ?? $user->username);
                $keys = ["login-attempt:{$identifier}"];
                break;

            case '2fa':
                $keys = [
                    "2fa-code:{$user->id}",
                    "recovery-code:{$user->id}",
                ];
                break;

            default:
                return; // do nothing if context not supported
        }

        foreach ($keys as $key) {
            RateLimiter::clear($key);
            \Cache::forget("penalty_level:{$key}");
            \Cache::forget("penalty_applied:{$key}");
        }
    }

}
