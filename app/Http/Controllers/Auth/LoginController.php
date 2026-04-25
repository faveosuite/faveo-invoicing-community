<?php

namespace App\Http\Controllers\Auth;

use App\ApiKey;
use App\Facades\Cart;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Front\CartController;
use App\Http\Requests\Auth\LoginRequest;
use App\Model\Common\Bussiness;
use App\Model\Common\ChatScript;
use App\Model\Common\Country;
use App\Model\Common\StatusSetting;
use App\Model\Payment\Currency;
use App\Model\Payment\Plan;
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

    protected $cart;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except(['logout', 'store-basic-details']);
        $this->middleware(['blockFailedVerifications:login', 'recaptcha:login'])->only('login');
        $this->cart = new Cart();
    }

    /**
     * This function returns to the login page.
     *
     * @param
     * @param
     * @return
     *
     * @throws
     */
    public function showLoginForm()
    {
        try {
            $bussinesses = Bussiness::pluck('name', 'short')->toArray();
            $status = StatusSetting::select('msg91_status', 'emailverification_status', 'terms')->first();
            $apiKeys = ApiKey::select('nocaptcha_sitekey', 'captcha_secretCheck', 'msg91_auth_key', 'terms_url')->first();
            $analyticsTag = ChatScript::where('google_analytics', 1)->where('on_registration', 1)->value('google_analytics_tag');
            $location = getLocation();

            $google_status = SocialLogin::select('status')->where('type', 'google')->value('status');
            $github_status = SocialLogin::select('status')->where('type', 'github')->value('status');
            $twitter_status = SocialLogin::select('status')->where('type', 'twitter')->value('status');
            $linkedin_status = SocialLogin::select('status')->where('type', 'linkedin')->value('status');
            $data = [
                'status' => $status,
                'apiKeys' => $apiKeys,
                'analyticsTag' => $analyticsTag,
                'location' => $location,
                'google_status' => $google_status,
                'github_status' => $github_status,
                'twitter_status' => $twitter_status,
                'linkedin_status' => $linkedin_status,
            ];

//            return successResponse('Login Page', $data);
            return view('themes.default1.front.auth.login-register', compact('bussinesses', 'location', 'status', 'apiKeys', 'analyticsTag', 'google_status', 'github_status', 'linkedin_status', 'twitter_status'));
        } catch (\Exception $ex) {
            \Logger::exception($ex);
            $error = $ex->getMessage();
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
     * @param  LoginRequest  $request
     * @return
     */
    public function login(LoginRequest $request) // 2. Type-hint the LoginRequest
    {
        try {
            // 1. Prepare credentials for both email and username login
            $credentials = $this->buildCredentials($request);

            $rateLimitKey = $this->getLoginRateLimitKey($request->input('email_username'));
            RateLimiter::hit("login-attempt:{$rateLimitKey}", 600);

            // 2. Attempt to authenticate the user
            if (! Auth::attempt($credentials, $request->boolean('remember'))) {
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

            $this->convertCart();

            $this->logActivityLogin($user);

            return successResponse('', ['redirect' => $this->redirectPath()]);
        } catch(\Exception $ex) {
            return errorResponse($ex->getMessage());
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
        $defaultPath = ($this->cart->isEmpty() === false) ? '/show/cart' : $defaultPath;

        return successResponse('success', ['role' => $auth->role]);
        // return redirect()->intended($defaultPath)->getTargetUrl();
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

        //return Socialite::driver($provider)->redirect();
        return successResponse('success', [Socialite::driver($provider)->redirect()]);
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

        $state = getStateByCode($location['iso_code'], $location['state']);

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
                'ip' => $location['ip'],
                'timezone_id' => getTimezoneByName($location['timezone']),
                'state' => $state['id'],
                'town' => $location['city'],
                'country' => Country::where('country_name', strtoupper($location['country']))->value('country_code_char2'),
            ]);
            $user->active = 1;
            $user->role = 'user';
            $user->save();
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
     * @return
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

            return successResponse(__('message.updated-successfully'));
        } catch (\Exception $e) {
            return errorResponse($e->getMessage());
//            Session::flash('error', __('message.please_enter_details'));
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
    public function convertCart()
    {
        $cart = new Cart();
        $contents = $cart->getContent();
        $user = \Auth::user();
        $currencyCode = getCurrencyForClient($user->country);
        $currencySymbol = Currency::where('code', $currencyCode)->value('symbol');
        $cartController = new CartController();
        foreach ($contents as $content) {
            try {
                $plan = Plan::find($content->id);

                // If plan or product is missing, throw to remove it
                throw_if(! $plan || ! $plan->product, new \Exception('Invalid plan or product.'));

                $price = $cartController->planCost($plan->product, $user->id, $content['id']);

                if (! empty($content['attributes']['domain'])) {
                    $price = $price * $content['attributes']['agents'];
                }

                $cart->update($content['id'], [
                    'price' => $price,
                    'attributes' => [
                        'currency' => $currencyCode,
                        'symbol' => $currencySymbol,
                        'agents' => $content['attributes']['agents'],
                        'domain' => $content['attributes']['domain'],
                    ],
                ]);
            } catch (\Exception $e) {
                // Remove item if any exception occurs (missing plan/product or pricing failure)
                $cart->remove($content['id']);
                continue;
            }
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

        return $userId ?? md5(request()->ip().':'.$emailOrUsername);
    }

    private function clearRateLimit(string $context, User $user): void
    {
        switch ($context) {
            case 'login':
                $identifier = $this->getLoginRateLimitKey($user->email ?? $user->username);
                $keys = ["login-attempt:{$identifier}"];
                break;

            case '2fa':
                $identifier = $user->id;
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
        }

        \Cache::forget("penalty_level:{$context}:{$identifier}");
        \Cache::forget("penalty_applied:{$context}:{$identifier}");
    }

    public function logActivityLogin($user): void
    {
        if (! $user) {
            return;
        }

        $userUrl = url("clients/{$user->id}");

        $name = e($user->first_name.' '.$user->last_name);
        $message = "User <a href='{$userUrl}'><strong>{$name}</strong></a> logged in successfully.";

        logActivity(
            $message,
            'login',
            'Authentication',
            $user,
        );
    }
}
