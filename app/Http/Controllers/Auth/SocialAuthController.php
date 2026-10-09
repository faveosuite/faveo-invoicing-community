<?php

namespace App\Http\Controllers\Auth;

use App\ApiKey;
use App\Events\UserRegisteredEvent;
use App\Model\Common\ManagerSetting;
use App\Model\Common\StatusSetting;
use App\Rules\PhoneNumber;
use App\SocialAccount;
use App\SocialLogin;
use App\User;
use Exception;
use Facades\Spatie\Referer\Referer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Laravel\Socialite\AbstractUser;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Facades\Socialite;
use Logger;

/**
 * Google / GitHub / Twitter / LinkedIn login.
 *
 *  redirect()  → returns the provider URL to the SPA
 *  callback()  → known account: log in. Email matches a normal account: link + log in.
 *                Otherwise park the profile in the session and send the user to
 *                /social/complete, where the details a normal registration asks for are collected.
 *  complete()  → creates the user from those details and logs them in.
 *
 * Everything after the provider hands us a verified identity (activation, email/mobile
 * verification, 2FA) goes through the same checks as the password login.
 */
class SocialAuthController extends BaseAuthController
{
    private const PROVIDERS = ['google', 'github', 'twitter', 'linkedin'];

    /** LinkedIn retired its old API; apps created now only get "Sign In with LinkedIn using OpenID Connect". */
    private const DRIVERS = ['linkedin' => 'linkedin-openid'];

    public function __construct()
    {
        $this->middleware('guest');
    }

    public function redirect(string $provider): JsonResponse
    {
        try {
            $url = $this->driver($provider)->redirect()->getTargetUrl();

            return successResponse('success', ['url' => $url]);
        } catch (Exception $exception) {
            Logger::exception($exception);

            return errorResponse(__('message.social_login_unavailable'));
        }
    }

    public function callback(Request $request, string $provider): RedirectResponse
    {
        // Google sends ?error=access_denied, Twitter sends ?denied=... when the user cancels.
        if ($request->hasAny(['error', 'denied'])) {
            return redirect($this->errorUrl('cancelled'));
        }

        try {
            /** @var AbstractUser $socialUser */
            $socialUser = $this->driver($provider)->user();

            return redirect($this->signIn(strtolower($provider), $socialUser));
        } catch (Exception $exception) {
            Logger::exception($exception);

            return redirect($this->errorUrl('failed'));
        }
    }

    public function completeConfig(): JsonResponse
    {
        $signup = Session::get('social_signup');
        if (! $signup) {
            return successResponse('', ['redirect' => url('login')]);
        }

        return successResponse('social-complete', [
            'first_name' => $signup['first_name'],
            'last_name' => $signup['last_name'],
            'email' => $signup['email'],
            'provider' => $signup['provider'],
            'terms' => (int) StatusSetting::value('terms'),
            'terms_url' => ApiKey::value('terms_url'),
            'location' => getLocation(),
        ]);
    }

    public function complete(Request $request): JsonResponse
    {
        $signup = Session::get('social_signup');
        if (! $signup) {
            return errorResponse(__('message.social_session_expired'));
        }

        $email = $signup['email'] ?? $request->input('email');

        $request->merge(['email' => $email])->validate([
            'first_name' => ['required', 'min:2', 'max:30'],
            'last_name' => ['required', 'max:30'],
            'email' => ['required', 'email', 'unique:users', 'unique:settings,email', 'unique:settings,company_email'],
            'company' => ['required', 'max:50'],
            'mobile' => ['required', 'unique:users', new PhoneNumber($request->input('mobile_country_iso'))],
            'mobile_code' => ['required'],
            'address' => ['required', 'string', 'regex:/^[^<>]*$/'],
            'country' => ['required', 'exists:countries,country_code_char2'],
            'terms' => [StatusSetting::value('terms') ? 'accepted' : 'sometimes'],
        ]);

        try {
            $user = $this->createUser($request, $signup);
        } catch (Exception $exception) {
            Logger::exception($exception);

            return errorResponse(__('message.something_wrong'));
        }

        return successResponse('', ['redirect' => $this->finish($user)]);
    }

    private function driver(string $provider): Provider
    {
        $provider = strtolower($provider);

        $details = in_array($provider, self::PROVIDERS, true)
            ? SocialLogin::where('type', $provider)->where('status', 1)->first()
            : null;

        if (! $details || ! $details->client_id) {
            throw new Exception(sprintf('Social login "%s" is not enabled.', $provider));
        }

        $driver = self::DRIVERS[$provider] ?? $provider;

        Config::set('services.'.$driver, [
            'client_id' => $details->client_id,
            'client_secret' => $details->client_secret,
            // Fixed by our route; the admin registers this exact URL with the provider.
            'redirect' => url('auth/callback/'.$provider),
        ]);

        return Socialite::driver($driver);
    }

    /**
     * Decide what to do with an identity the provider has just confirmed. Returns the URL to send the browser to.
     */
    private function signIn(string $provider, AbstractUser $socialUser): string
    {
        $providerId = (string) $socialUser->getId();

        $account = SocialAccount::where(['provider' => $provider, 'provider_id' => $providerId])->first();
        if ($account && ($user = User::find($account->user_id))) {
            return $this->finish($user);
        }

        $email = $socialUser->getEmail();
        $existing = $email ? User::where('email', $email)->first() : null;

        if ($existing) {
            // Only trust the email if the provider says it is verified, and never hand an admin account
            // to someone who only controls an email address at a third party.
            if (! $this->emailVerifiedByProvider($provider, $socialUser) || $existing->role === 'admin') {
                return $this->errorUrl('email_exists');
            }

            SocialAccount::create(['user_id' => $existing->id, 'provider' => $provider, 'provider_id' => $providerId]);

            return $this->finish($existing);
        }

        $name = trim((string) $socialUser->getName());
        [$first, $last] = array_pad(explode(' ', $name, 2), 2, '');

        Session::put('social_signup', [
            'provider' => $provider,
            'provider_id' => $providerId,
            'first_name' => $first,
            'last_name' => $last,
            'email' => $email,
            'email_verified' => $email && $this->emailVerifiedByProvider($provider, $socialUser),
        ]);

        return url('social/complete');
    }

    private function emailVerifiedByProvider(string $provider, AbstractUser $socialUser): bool
    {
        return match ($provider) {
            // Socialite's GitHub driver only ever returns the primary *verified* email.
            'github' => (bool) $socialUser->getEmail(),
            'google', 'linkedin' => filter_var($socialUser->getRaw()['email_verified'] ?? false, FILTER_VALIDATE_BOOLEAN),
            default => false,
        };
    }

    /**
     * @param  array<string, mixed>  $signup
     */
    private function createUser(Request $request, array $signup): User
    {
        $location = getLocation();
        $state = getStateByCode((string) ($location['iso_code'] ?? ''), (string) ($location['state'] ?? ''));
        $managerSettings = ManagerSetting::whereIn('manager_role', ['account', 'sales'])->pluck('auto_assign', 'manager_role');

        $user = new User;
        $user->fill([
            'first_name' => strip_tags((string) $request->input('first_name')),
            'last_name' => strip_tags((string) $request->input('last_name')),
            'email' => strip_tags((string) $request->input('email')),
            'user_name' => strip_tags((string) $request->input('email')),
            'company' => strip_tags((string) $request->input('company')),
            'address' => strip_tags((string) $request->input('address')),
            'country' => $request->input('country'),
            'mobile' => ltrim((string) $request->input('mobile'), '0'),
            'mobile_code' => $request->input('mobile_code'),
            'mobile_country_iso' => $request->input('mobile_country_iso'),
            'state' => $state['id'],
            'town' => $location['city'] ?? null,
            'ip' => $location['ip'] ?? $request->ip(),
            'timezone_id' => (int) getTimezoneByName($location['timezone'] ?? ''),
            'referrer' => Referer::get(),
            'profile_pic' => '',
            // No password: the user signs in through the provider, or uses "forgot password" to set one.
            'password' => Hash::make(Str::random(40)),
            'email_verified' => $signup['email_verified'] && $signup['email'] ? 1 : 0,
            'mobile_verified' => 0,
        ]);
        $user->active = 1;
        $user->role = 'user';
        $user->account_manager = $managerSettings->get('account') ? (string) $user->assignManagerByPosition('account_manager') : null;
        $user->setAttribute('manager', $managerSettings->get('sales') ? $user->assignManagerByPosition('manager') : null);
        $user->save();

        SocialAccount::create(['user_id' => $user->id, 'provider' => $signup['provider'], 'provider_id' => $signup['provider_id']]);
        Session::forget('social_signup');

        event(new UserRegisteredEvent($user, 'register'));
        resolve(RegisterController::class)->logActivityRegister($user);

        return $user;
    }

    /**
     * The same gates the password login applies once credentials are known good.
     */
    private function finish(User $user): string
    {
        if ($user->active != 1) {
            return $this->errorUrl('inactive');
        }

        if (! $this->userNeedVerified($user)) {
            Session::put(['justStarted' => true, 'verification_user_id' => $user->id]);

            return url('verify');
        }

        if ($user->is_2fa_enabled) {
            Session::put(['2fa:user:id' => $user->id, 'remember:user:id' => false]);

            return url('verify-2fa');
        }

        Auth::login($user);
        Session::regenerate();

        $login = resolve(LoginController::class);
        $login->logActivityLogin($user);

        return (string) $login->redirectPath(); // @phpstan-ignore cast.string
    }

    private function errorUrl(string $reason): string
    {
        return url('login').'?social_error='.$reason;
    }
}
