<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Auth\LoginController;
use App\Http\Requests\ValidateSecretRequest;
use App\Rules\CaptchaValidation;
use App\Rules\Honeypot;
use App\User;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use ParagonIE\ConstantTime\Base32;
use PragmaRX\Google2FAQRCode\Google2FA;
use RateLimiter;

class Google2FAController extends Controller
{
    use ValidatesRequests;

    /**
     * Create a new authentication controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('web');
    }

    public function verify2fa()
    {
        if (\Session::has('2fa:user:id')) {
            return view('themes.default1.front.enableTwoFactor');
        } else {
            return redirect('login');
        }
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function enableTwoFactor(Request $request)
    {
        $user = $request->user();
        $google2fa = new Google2FA();
        $secret = $this->generateSecret();
        $user->google2fa_secret = Crypt::encrypt($secret);
        $user->save();
        $imageDataUri = $google2fa->getQRCodeInline(
            $request->getHttpHost(),
            $user->email,
            $secret,
            200
        );

        return successResponse('', ['image' => $imageDataUri, 'secret' => $secret]);
    }

    private function generateSecret()
    {
        $randomBytes = random_bytes(10);

        return Base32::encodeUpper($randomBytes);
    }

    /**
     * @param  App\Http\Requests\ValidateSecretRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function postLoginValidateToken(ValidateSecretRequest $request)
    {
        try {
            $session = $request->session();
            $userId = $session->get('2fa:user:id');
            $user = User::findOrFail($userId);

            return $this->handleTwoFactorLogin($request, $user, '2fa-code', function ($user, $request) {
                $secret = Crypt::decrypt($user->google2fa_secret);
                $isValid = (new Google2FA())->verifyKey($secret, $request->totp);

                if (! $isValid) {
                    throw new \Exception(__('message.invalid_passcode'));
                }
            });
        } catch (\Exception $e) {
            return errorResponse($e->getMessage());
        }
    }

    public function verifyPassword(Request $request)
    {
        if (! $request->user_password && $request->login_type == 'social') {
            return successResponse('password_verified');
        } else {
            $user = \Auth::user();
            if (\Hash::check($request->input('user_password'), $user->getAuthPassword())) {
                return successResponse('password_verified');
            } else {
                return errorResponse('password_incorrect');
            }
        }
    }

    public function postSetupValidateToken(Request $request)
    {
        $user = $request->user();
        $google2fa = new Google2FA();
        $secret = Crypt::decrypt($user->google2fa_secret);

        $valid = $google2fa->verifyKey($secret, $request->totp);

        if ($valid == true) {
            $user->is_2fa_enabled = 1;
            $user->google2fa_activation_date = \Carbon\Carbon::now();
            $user->save();

            return successResponse(\Lang::get('message.valid_passcode'));
        }

        return errorResponse(\Lang::get('message.invalid_passcode'));
    }

    /**
     * Disables 2FA for a user/agent, wipes out all the details related to 2FA from the Database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return json \Illuminate\Http\Response
     */
    public function disableTwoFactor(Request $request)
    {
        $user = $request->userId ? User::where('id', $request->userId)->first() : $request->user();
        if (\Auth::user()->role != 'admin' && $user->id != \Auth::user()->id) {
            return errorResponse(__('message.cannot_disable_2fa'));
        }
        //make secret column blank
        $user->google2fa_secret = null;
        $user->google2fa_activation_date = null;
        $user->is_2fa_enabled = 0;
        $user->backup_code = null;
        $user->code_usage_count = 0;
        $user->save();

        return successResponse(\Lang::get('message.2fa_disabled'));
    }

    public function generateRecoveryCode()
    {
        $code = str_random(20);
        User::where('id', \Auth::user()->id)->update(['backup_code' => $code, 'code_usage_count' => 0]);

        return successResponse(['code' => $code]);
    }

    public function getRecoveryCode()
    {
        $code = User::find(\Auth::user()->id)->backup_code;

        return successResponse(['code' => $code]);
    }

    public function showRecoveryCode()
    {
        if (session('2fa:user:id')) {
            return view('themes.default1.front.recoveryCode');
        }

        return redirect('login');
    }

    public function verifyRecoveryCode(Request $request)
    {
        $this->validate($request, [
            'rec_code' => 'required',
            'recovery_code' => [new Honeypot()],
        ], [
            'rec_code.required' => __('validation.please_enter_recovery_code'),
        ]);

        try {
            $session = $request->session();
            $userId = $session->get('2fa:user:id');
            $user = User::findOrFail($userId);

            return $this->handleTwoFactorLogin($request, $user, 'recovery-code', function ($user, $request) {
                if ($user->code_usage_count == 1) {
                    throw new \Exception(__('message.code_authenticator_disable_2fa'));
                }

                if ($request->rec_code !== $user->backup_code) {
                    throw new \Exception(__('message.invalid_recovery_code'));
                }

                $user->code_usage_count = 1;
                $user->save();
            });
        } catch (\Exception $e) {
            return errorResponse($e->getMessage());
        }
    }

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
        \Session::forget('toggleState');
    }

    private function handleTwoFactorLogin(Request $request, User $user, string $rateLimiterKey, callable $validator)
    {
        // Rate limit for 6 hours
        RateLimiter::hit("{$rateLimiterKey}:{$user->id}");

        // Run the type-specific validation logic
        $validator($user, $request);

        // Clear session identifiers
        $session = $request->session();
        $session->forget(['2fa:user:id', 'remember:user:id']);

        // If it's part of password reset flow
        if ($token = $session->get('reset_token')) {
            $session->put('2fa_verified', true);

            return successResponse('', ['redirect' => route('password.reset', ['token' => $token])]);
        }

        // Normal login flow
        \Auth::login($user, $session->get('remember:user:id'));
        $this->convertCart();

        return successResponse('', ['redirect' => (new LoginController())->redirectPath()]);
    }

    public function verifySession()
    {
        return successResponse('active');
    }
}
