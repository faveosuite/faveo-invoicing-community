<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Auth\LoginController;
use App\Http\Requests\ValidateSecretRequest;
use App\Rules\Honeypot;
use App\User;
use App\UserBackupCodes;
use Auth;
use Exception;
use Hash;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Date;
use Lang;
use ParagonIE\ConstantTime\Base32;
use PragmaRX\Google2FAQRCode\Google2FA;
use RateLimiter;
use Session;

class Google2FAController extends Controller
{
    use ValidatesRequests;

    /**
     * Create a new authentication controller instance.
     */
    public function __construct()
    {
        $this->middleware('web');
        $this->middleware('auth', ['only' => ['enableTwoFactor', 'disableTwoFactor', 'generateRecoveryCode', 'getRecoveryCode', 'showRecoveryCode', 'postSetupValidateToken']]);
        $this->middleware('recaptcha:login_2fa')->only('postLoginValidateToken');
        $this->middleware('recaptcha:login_recovery')->only('verifyRecoveryCode');
    }

    public function verify2fa()
    {
        if (Session::has('2fa:user:id')) {
            return successResponse('Redirect to 2fa');
//            return view('themes.default1.front.enableTwoFactor');
        } else {
            return successResponse('Login page', ['redirect' => url('login')]);
        }
    }

    /**
     * @return Response
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

    private function generateSecret(): string
    {
        $randomBytes = random_bytes(10);

        return Base32::encodeUpper($randomBytes);
    }

    /**
     * @return Response
     */
    public function postLoginValidateToken(ValidateSecretRequest $request)
    {
        try {
            $session = $request->session();
            $userId = $session->get('2fa:user:id');
            $user = User::findOrFail($userId);

            return $this->handleTwoFactorLogin($request, $user, '2fa-code', function ($user, $request): void {
                $secret = Crypt::decrypt($user->google2fa_secret);
                $isValid = new Google2FA()->verifyKey($secret, $request->totp);

                if (! $isValid) {
                    // MUST throw — handleTwoFactorLogin proceeds to Auth::login unless
                    // validation aborts. A returned response here would be ignored.
                    throw new Exception(__('message.invalid_passcode'));
                }
            });
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    public function showVerifyPassword()
    {
        return successResponse('password_confirmation_not_required');
    }

    public function verifyPassword(Request $request)
    {
        if (! $request->user_password && $request->login_type == 'social') {
            Session::put('auth.password_confirmed_at', time());

            return successResponse('password_verified');
        }

        $user = Auth::user();
        if (Hash::check($request->input('user_password'), $user->getAuthPassword())) {
            Session::put('auth.password_confirmed_at', time());

            return successResponse('password_verified');
        }

        return errorResponse('password_incorrect');
    }

    public function postSetupValidateToken(Request $request)
    {
        $user = $request->user();
        $google2fa = new Google2FA();
        $secret = Crypt::decrypt($user->google2fa_secret);

        $valid = $google2fa->verifyKey($secret, $request->totp);

        if ($valid == true) {
            $user->is_2fa_enabled = 1;
            $user->google2fa_activation_date = Date::now();
            $user->save();

            return successResponse(Lang::get('message.valid_passcode'));
        }

        return errorResponse(Lang::get('message.invalid_code_2fa'));
    }

    /**
     * Disables 2FA for a user/agent, wipes out all the details related to 2FA from the Database.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function disableTwoFactor(Request $request)
    {
        $user = $request->userId ? User::where('id', $request->userId)->first() : $request->user();
        if (Auth::user()->role != 'admin' && $user->id != Auth::user()->id) {
            return errorResponse(__('message.cannot_disable_2fa'));
        }

        $user->google2fa_secret = null;
        $user->google2fa_activation_date = null;
        $user->is_2fa_enabled = 0;
        $user->save();

        UserBackupCodes::where('user_id', $user->id)->delete();

        return successResponse(Lang::get('message.2fa_disabled'));
    }

    public function generateRecoveryCode()
    {
        $codes = $this->createCodes();
        $userId = Auth::user()->id;

        UserBackupCodes::where('user_id', $userId)->delete();
        foreach ($codes as $code) {
            UserBackupCodes::create(['user_id' => $userId, 'backup_codes' => $code]);
        }

        return successResponse('', ['code' => $codes]);
    }

    public function getRecoveryCode()
    {
        $userId = Auth::user()->id;
        $codes = UserBackupCodes::where('user_id', $userId)->pluck('backup_codes')->toArray();

        if (empty($codes)) {
            $codes = $this->createCodes();
            foreach ($codes as $code) {
                UserBackupCodes::create(['user_id' => $userId, 'backup_codes' => $code]);
            }
        }

        return successResponse('', ['code' => $codes]);
    }

    private function createCodes(): array
    {
        $codes = [];
        for ($i = 0; $i < 10; $i++) {
            $codes[] = bin2hex(random_bytes(10));
        }

        return $codes;
    }

    public function showRecoveryCode()
    {
        if (session('2fa:user:id')) {
            return successResponse('Redirect to RecoveryCode');
//            return view('themes.default1.front.recoveryCode');
        }

        return successResponse('Login page', ['redirect' => url('login')]);
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

            return $this->handleTwoFactorLogin($request, $user, 'recovery-code', function ($user, $request): void {
                $code = UserBackupCodes::where('user_id', $user->id)
                    ->where('backup_codes', $request->rec_code)
                    ->first();

                if (! $code) {
                    throw new Exception(__('message.invalid_recovery_code'));
                }

                $code->delete();
            });
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    private function handleTwoFactorLogin(Request $request, User $user, string $rateLimiterKey, callable $validator)
    {
        // Rate limit for 6 hours
        RateLimiter::hit(sprintf('%s:%s', $rateLimiterKey, $user->id));

        // Run the type-specific validation logic. Validators MUST throw on a failed
        // check (the caller's try/catch turns it into an error response); control
        // only reaches Auth::login below when validation passed.
        $validator($user, $request);

        // Clear session identifiers
        $session = $request->session();
        $remember = $session->get('remember:user:id', default: false);
        $session->forget(['2fa:user:id', 'remember:user:id']);

        // If it's part of password reset flow
        if ($token = $session->get('reset_token')) {
            $session->put('2fa_verified', value: true);

            return successResponse('', ['redirect' => route('password.reset', ['token' => $token])]);
        }

        // Normal login flow
        $request->session()->regenerate();
        Auth::login($user, $remember);

        $loginController = new LoginController();
        $loginController->logActivityLogin($user);
        $loginController->convertCart();

        return successResponse('', ['redirect' => $loginController->redirectPath()]);
    }

    public function verifySession()
    {
        return successResponse('active');
    }
}
