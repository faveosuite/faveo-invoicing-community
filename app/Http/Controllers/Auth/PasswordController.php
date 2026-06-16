<?php

namespace App\Http\Controllers\Auth;

use Exception;
use App\Model\User\Password;
use App\User;
use Hash;
use Illuminate\Support\Str;
use App\Model\Common\Setting;
use App\Model\Common\Template;
use App\Model\Common\TemplateType;
use App\Http\Controllers\Common\PhpMailController;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\PasswordBroker;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;

class PasswordController extends Controller
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
     * Create a new password controller instance.
     *
     * @param Guard $auth
     * @param PasswordBroker $passwords
     * @return void
     */
    public function __construct(Guard $auth, PasswordBroker $passwords)
    {
        $this->middleware('guest');
    }

    public function getEmail()
    {
        try {
            return view('themes.default1.front.auth.password');
        } catch (Exception $ex) {
            return redirect()->with('fails', $ex->getMessage());
        }
    }

    /**
     * Display the password reset view for the given token.
     *
     * @param  string  $token
     * @return Response
     */
    public function getReset($token = null)
    {
        if (is_null($token)) {
            throw new NotFoundHttpException();
        }

        return view('themes.default1.front.auth.reset')->with('token', $token);
    }

    /**
     * Reset the given user's password.
     *
     * @param  Request  $request
     * @return Response
     */
    public function postReset(Request $request)
    {
        //dd($request->input('token'));
        $this->validate($request, [
            'token' => 'required',
            //'email' => 'required|email',
            'password' => 'required|confirmed',
        ], [
            'token.required' => __('validation.token_validation.token_required'),
            'password.required' => __('validation.token_validation.password_required'),
            'password.confirmed' => __('validation.token_validation.password_confirmed'),
        ]);
        $token = $request->input('token');
        $pass = $request->input('password');
        $password = new Password();
        $password = $password->where('token', $token)->first();
        if ($password) {
            $user = new User();
            $user = $user->where('email', $password->email)->first();
            if ($user) {
                $user->password = Hash::make($pass);
                $user->save();

                return redirect('auth/login')->with('success', __('message.password_changed_successfully'));
            } else {
                return back()
                    ->withInput($request->only('email'))
                    ->withErrors([
                        'email' => __('message.invalid_email'),
                    ]);
            }
        } else {
            return back()
                ->withInput($request->only('email'))
                ->withErrors([
                    'email' => __('message.invalid_email'),
                ]);
        }
    }

    /**
     * Send a reset link to the given user.
     *
     * @param Request $request
     * @return Response
     */
    public function sendResetLinkEmail(Request $request)
    {
        $this->validate($request, ['email' => 'required|email|exists:users,email'],
            [
                'email.required' => __('validation.custom_email.required'),
                'email.email' => __('validation.custom_email.email'),
                'email.exists' => __('validation.custom_email.exists'),
            ]);
        $email = $request->input('email');
        $token = Str::random(40);
        $password = new Password();
        if ($password->where('email', $email)->first()) {
            $token = $password->where('email', $email)->first()->token;
        } else {
            $activate = $password->create(['email' => $email, 'token' => $token]);
            $token = $activate->token;
        }

        $url = url("password/reset/$token");
        $user = new User();
        $user = $user->where('email', $email)->first();
        if (! $user) {
            return back()->with('fails', __('message.invalid_email'));
        }

        //check in the settings
        $settings = new Setting();
        $setting = $settings->where('id', 1)->first();
        //template
        $templates = new Template();
        $temp_id = $setting->forgot_password;
        $template = $templates->where('id', $temp_id)->first();
        $from = $setting->email;
        $to = $user->email;
        $subject = $template->name;
        $data = $template->data;
        $replace = ['name' => $user->first_name.' '.$user->last_name, 'url' => $url];
        $type = '';
        if ($template) {
            $type_id = $template->type;
            $temp_type = new TemplateType();
            $type = $temp_type->where('id', $type_id)->first()->name;
        }

        $mail = new PhpMailController();
        if ($emailSendingStatus) {
            $mail->sendEmail($from, $to, $data, $subject, $replace, $type);
        }

        return back()->with('success', __('message.resets_instruction').$to.'. '.__('message.check_junk_folder'));
    }
}
