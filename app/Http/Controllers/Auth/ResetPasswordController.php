<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Model\Common\StatusSetting;
use App\Rules\CaptchaValidation;
use App\Rules\Honeypot;
use App\Rules\StrongPassword;
use App\User;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;

class ResetPasswordController extends Controller
{
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
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
        $this->middleware(['recaptcha:reset'])->only('reset');
    }

    public function showResetForm(Request $request, $token = null)
    {
        try {
            $reset = \DB::table('password_resets')->select('email', 'created_at')->where('token', $token)->first();

            if ($reset && Carbon::parse($reset->created_at)->addMinutes(config('auth.passwords.users.expire')) > Carbon::now()) {
                $status = StatusSetting::find(1, ['recaptcha_status', 'v3_recaptcha_status']);

                $user = User::where('email', $reset->email)->first();

                if ($user && $user->is_2fa_enabled && ! \Session::get('2fa_verified')) {
                    \Session::put('2fa:user:id', $user->id);
                    \Session::put('verification_user_id', $user->id);
                    \Session::put('justStarted', true);
                    \Session::put('reset_token', $token);

                    return redirect('verify-2fa');
                }

                return view('themes.default1.front.auth.reset', compact('status'))
                    ->with(['reset_token' => $token, 'email' => $reset->email]);
            } else {
                return redirect('login')->with('fails', \Lang::get('message.reset_link_expired'));
            }
        } catch (\Exception $ex) {
            return redirect('login')->with('fails', $ex->getMessage());
        }
    }

    /**
     * Reset the given user's password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function reset(Request $request)
    {
        // Validate request
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', new StrongPassword()],
            'reset' => [new Honeypot()],
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

            $passwordToken = \App\Model\User\Password::where('email', $email)->first();

            if (!$passwordToken || $passwordToken->token !== $token) {
                return errorResponse(__('message.cannot_reset_password_invalid'));
            }

            $user = \App\User::where('email', $email)->first();
            if (!$user) {
                return errorResponse(__('message.user_cannot_identifer'));
            }

            // Begin atomic transaction
            \DB::transaction(function () use ($user, $newPassword) {
                \Session::forget(['2fa_verified', 'reset_token']);

                $user->password = \Hash::make($newPassword);
                $user->save();

                // Logout all other sessions
                \Auth::logoutOtherDevices($newPassword);

                // Delete password reset token
                \DB::table('password_resets')->where('email', $user->email)->delete();
            });

            \Session::flash('success', __('message.password_changed_successfully'));

            return successResponse(__('message.password_changed_successfully'), ['redirect' => url('login')]);

        } catch (\Exception $ex) {
            return errorResponse($ex->getMessage());
        }
    }
}
