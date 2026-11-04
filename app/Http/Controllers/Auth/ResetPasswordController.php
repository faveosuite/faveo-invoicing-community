<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Rules\Honeypot;
use App\User;
use DB;
use Exception;
use Hash;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Auth\Passwords\PasswordBroker;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Session;

class ResetPasswordController extends Controller
{
    /** Session flag set once the 2FA code is accepted. */
    private const TWO_FA_VERIFIED_KEY = '2fa_verified';

    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;

    /**
     * Where to redirect users after resetting their password.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * SPA shell for the reset link. The 2FA session keys are written here, in the page
     * request itself: the SPA's reset-validate XHR runs in parallel with other app-shell
     * XHRs, and sessions aren't locked, so a slower one would save its stale copy over them.
     */
    public function showResetPage(Request $request, mixed $token = null): View
    {
        $this->showResetForm($request, $token);

        return view('client');
    }

    public function showResetForm(Request $request, mixed $token = null): JsonResponse
    {
        try {
            $user = $this->resetUser((string) $request->query('email', ''), (string) $token);
            if (! $user) {
                return errorResponse(__('message.reset_link_expired'));
            }

            if ($user->is_2fa_enabled && Session::get(self::TWO_FA_VERIFIED_KEY) !== $token) {
                Session::put('2fa:user:id', $user->id);
                Session::put('verification_user_id', $user->id);
                Session::put('justStarted', true);
                Session::put('reset_token', $token);

                return successResponse('', ['redirect' => url('verify-2fa')]);
            }

            return successResponse('Reset page', [['reset_token' => $token, 'email' => $user->email]]);
        } catch (Exception $exception) {
            \Logger::exception($exception);

            return errorResponse(__('message.sorry_something_wrong'));
        }
    }

    /**
     * Reset the given user's password (Laravel's password broker checks the hashed
     * token and its expiry, then deletes it).
     */
    public function reset(Request $request): JsonResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::defaults()],
            'honeypot' => [new Honeypot],
        ], [
            'token.required' => __('validation.token_validation.token_required'),
            'email.required' => __('validation.custom_email.required'),
            'email.email' => __('validation.custom_email.email'),
            'password.required' => __('validation.token_validation.password_required'),
            'password.confirmed' => __('validation.token_validation.password_confirmed'),
        ]);

        try {
            $token = (string) $request->input('token');
            $user = $this->resetUser((string) $request->input('email'), $token);
            if (! $user) {
                return errorResponse(__('message.reset_link_expired'));
            }

            // 2FA must be passed for *this* link (checked on page load too, but this endpoint can be posted to directly).
            if ($user->is_2fa_enabled && Session::get(self::TWO_FA_VERIFIED_KEY) !== $token) {
                return errorResponse(__('message.reset_link_expired'));
            }

            $status = DB::transaction(fn () => Password::broker()->reset(
                $request->only('email', 'password', 'password_confirmation', 'token'),
                function (User $user, string $password): void {
                    Session::forget([self::TWO_FA_VERIFIED_KEY, 'reset_token']);
                    $user->password = Hash::make($password);
                    $user->save();
                    // Log out all other sessions.
                    deleteUserSessions($user->id, $password);
                }
            ));

            if ($status !== PasswordBroker::PASSWORD_RESET) {
                return errorResponse(__('message.reset_link_expired'));
            }

            Session::flash('success', __('message.password_changed_successfully'));

            return successResponse(__('message.password_changed_successfully'), ['redirect' => url('login')]);
        } catch (Exception $exception) {
            \Logger::exception($exception);

            return errorResponse(__('message.sorry_something_wrong'));
        }
    }

    /**
     * The user a reset link belongs to, if the link (email + token) is valid and not expired.
     */
    private function resetUser(string $email, string $token): ?User
    {
        if ($email === '' || $token === '') {
            return null;
        }
        $broker = Password::broker();
        $user = $broker->getUser(['email' => $email]);

        return $user instanceof User && $broker->tokenExists($user, $token) ? $user : null;
    }
}
