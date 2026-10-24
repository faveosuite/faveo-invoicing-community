<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Model\User\Password;
use App\Rules\Honeypot;
use App\Rules\StrongPassword;
use App\User;
use DB;
use Exception;
use Hash;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
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
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('guest');
        $this->middleware(['recaptcha:reset'])->only('reset');
    }

    public function showResetForm(Request $request, mixed $token = null): JsonResponse
    {
        try {
            $reset = DB::table('password_resets')->select('email', 'created_at')->where('token', $token)->first();

            if ($reset && Date::parse($reset->created_at)->addMinutes(config('auth.passwords.users.expire')) > Date::now()) {
                $user = User::where('email', $reset->email)->first();

                if ($user && $user->is_2fa_enabled && Session::get(self::TWO_FA_VERIFIED_KEY) !== $token) {
                    Session::put('2fa:user:id', $user->id);
                    Session::put('verification_user_id', $user->id);
                    Session::put('justStarted', true);
                    Session::put('reset_token', $token);

                    return successResponse('', ['redirect' => url('verify-2fa')]);
                }

                $data = ['reset_token' => $token, 'email' => $reset->email];

                return successResponse('Reset page', [$data]);
            }

            return errorResponse(__('message.reset_link_expired'));
        } catch (Exception $exception) {
            \Logger::exception($exception);

            return errorResponse(__('message.sorry_something_wrong'));
        }
    }

    /**
     * Reset the given user's password.
     *
     * @return RedirectResponse|JsonResponse
     */
    public function reset(Request $request)
    {
        // Validate request
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', new StrongPassword],
            'reset' => [new Honeypot],
        ], [
            'token.required' => __('validation.token_validation.token_required'),
            'email.required' => __('validation.custom_email.required'),
            'email.email' => __('validation.custom_email.email'),
            'password.required' => __('validation.token_validation.password_required'),
            'password.confirmed' => __('validation.token_validation.password_confirmed'),
        ]);

        try {
            $email = $request->input('email');
            $token = $request->input('token');
            $newPassword = $request->input('password');

            $passwordToken = Password::where('email', $email)->first();

            if (! $passwordToken || ! hash_equals((string) $passwordToken->token, (string) $token)) {
                return errorResponse(__('message.cannot_reset_password_invalid'));
            }

            // showResetForm() checks expiry too, but this endpoint can be posted to directly.
            if (Date::parse($passwordToken->created_at)->addMinutes((int) config('auth.passwords.users.expire')) <= Date::now()) {
                return errorResponse(__('message.reset_link_expired'));
            }

            $user = User::where('email', $email)->first();
            if (! $user) {
                return errorResponse(__('message.user_cannot_identifer'));
            }

            // Same reason: 2FA must be passed for *this* link, not just on page load.
            if ($user->is_2fa_enabled && Session::get(self::TWO_FA_VERIFIED_KEY) !== $token) {
                return errorResponse(__('message.reset_link_expired'));
            }

            // Begin atomic transaction
            DB::transaction(function () use ($user, $newPassword): void {
                Session::forget([self::TWO_FA_VERIFIED_KEY, 'reset_token']);

                $user->password = Hash::make($newPassword);
                $user->save();

                // Logout all other sessions
                deleteUserSessions($user->id, $newPassword);

                // Delete password reset token
                DB::table('password_resets')->where('email', $user->email)->delete();
            });

            Session::flash('success', __('message.password_changed_successfully'));

            return successResponse(__('message.password_changed_successfully'), ['redirect' => url('login')]);
        } catch (Exception $exception) {
            \Logger::exception($exception);

            return errorResponse(__('message.sorry_something_wrong'));
        }
    }
}
