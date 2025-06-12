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
        $this->validate($request, [
            'token' => 'required',
            'email' => 'required|email',
            'password' => [
                'required',
                'confirmed',
                new StrongPassword(),
            ],
            'g-recaptcha-response' => [isCaptchaRequired()['is_required'], new CaptchaValidation('resetPassword')],
            'reset' => [new Honeypot()]
        ], ['g-recaptcha-response.required' => 'Please verify that you are not a robot.',
        ]);
        try {
            $token = $request->input('token');
            $pass = $request->input('password');
            $email = $request->input('email');
            $password = new \App\Model\User\Password();
            $password_tokens = $password->where('email', '=', $email)->first();
            if ($password_tokens) {
                if ($password_tokens->token == $token) {
                    $user = new \App\User();
                    $user = $user->where('email', $email)->first();
                    if ($user) {
                        \Session::forget('2fa_verified');
                        \Session::forget('reset_token');

                        $user->password = \Hash::make($pass);
                        $user->save();

                        //logout all other session when password is updated
                        \Auth::logoutOtherDevices($pass);

                        \DB::table('password_resets')->where('email', $user->email)->delete();

                        return redirect('login')->with('success', 'You have successfully changed your password');
                    } else {
                        return redirect()->back()
                                    ->withInput($request->only('email'))
                                    ->with('fails', 'User cannot be identified');
                    }
                } else {
                    return redirect()->back()
                            ->withInput($request->only('email'))
                            ->with('fails', 'Cannot reset password. Invalid modification of data suspected.');
                }
            } else {
                return redirect()->back()
                        ->withInput($request->only('email'))
                        ->with('fails', 'Cannot reset password.');
            }
        } catch (\Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }
}
