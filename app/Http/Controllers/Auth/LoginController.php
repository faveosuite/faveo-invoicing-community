<?php

namespace App\Http\Controllers\Auth;

use App\ApiKey;
use App\Http\Controllers\Controller;
use App\Model\Common\Bussiness;
use App\Model\Common\ChatScript;
use App\Model\Common\Country;
use App\Model\Common\StatusSetting;
use App\Rules\CaptchaValidation;
use App\SocialLogin;
use App\User;
use App\VerificationAttempt;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
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
     * This function performs login operations checks the validates email,password and redirects 2fa status of the user.
     *
     * @param Request $request
     * @param
     * @return \Illuminate\Http\RedirectResponse
     * @throws
     */
    public function login(Request $request)
    {
        $this->validate($request, [
            'email_username' => 'required',
            'password1' => 'required',
            'g-recaptcha-response' => [isCaptchaRequired()['is_required'], new CaptchaValidation()],
        ], [
            'g-recaptcha-response.required' => __('message.robot_verification'),
            'email_username.required' => __('message.password_email'),
            'password1.required' => __('message.please_enter_password'),
        ]);
        $loginInput = $request->input('email_username');
        $password = $request->input('password1');
        // Find user by email or username
        $user = User::where('email', $loginInput)->first();
        if (! $user) {
            return redirect()->back()->withInput()->withErrors([
                'login' => __('message.enter_a_email'),
            ]);
        }
        // Validate password
        if (! \Hash::check($password, $user->password)) {
            return redirect()->back()->withInput()->withErrors([
                'password' => __('message.please_enter_valid_password'),
            ]);
        }
        // Check account activation and mobile verification
        if (! $this->userNeedVerified($user)) {
            $attempts = VerificationAttempt::find($user->id);
            if ($attempts && $attempts->updated_at->lte(Carbon::now()->subHours(6))) {
                $attempts->update([
                    'mobile_attempt' => 0,
                    'email_attempt' => 0,
                ]);
            }
            if ($attempts && ($attempts->mobile_attempt >= 2 || $attempts->email_attempt >= 3)) {
                $remainingTime = Carbon::parse($attempts->updated_at)->addHours(6)->diffInSeconds(Carbon::now());
                return redirect()->back()->withErrors(__('message.verify_time_limit_exceed', ['time' => formatDuration($remainingTime)]));
            }
            return redirect('verify')->with('user', $user);
        }
        // Check if 2FA is enabled
        if ($user->is_2fa_enabled) {
            $request->session()->put('2fa:user:id', $user->id);
            $request->session()->put('remember:user:id', $request->has('remember'));
            return redirect('2fa/validate');
        }

        // Attempt login
        $auth = \Auth::attempt([
            'email' => $user->email,
            'password' => $password,
            'active' => 1,
        ], $request->has('remember'));
        if (! $auth) {
            return redirect()->back()->withInput()->withErrors([
                'login' => __('message.auth_failed_try_again'),
            ]);
        }
        $this->convertCart();

        activity()->log('Logged In');

        return redirect($this->redirectPath());
    }

    /**
     * Get the post register / login redirect path.
     *
     * @return string
     */
    public function redirectPath()
    {
        $auth = Auth::user();
        $sessionUrl = Redirect()->getIntendedUrl();

        if ($sessionUrl) {
            $appUrl = rtrim(env('APP_URL'), '/').'/';
            $sessionUrl = str_replace($appUrl, '', $sessionUrl);
        }

        $defaultPath = ($auth && $auth->role !== 'user') ? '/client-dashboard' : '/';

        return $sessionUrl ?: $defaultPath;
    }


    /**
     * This function redirects to the social login based on the provider(twitter,gitHub).
     *
     * @param $provider
     * @param
     * @return \Illuminate\Http\RedirectResponse
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
     * @param $provider
     * @param
     * @return \Illuminate\Http\RedirectResponse
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
     * @param Request $request
     * @param
     * @return \Illuminate\Http\RedirectResponse
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
     * @param $user
     * @param
     * @return bool
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
}
