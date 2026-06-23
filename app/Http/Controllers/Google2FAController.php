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
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Date;
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
        $this->middleware('auth', ['only' => ['enableTwoFactor', 'disableTwoFactor', 'generateRecoveryCode']]);
        $this->middleware('recaptcha:login_2fa')->only('postLoginValidateToken');
        $this->middleware('recaptcha:login_recovery')->only('verifyRecoveryCode');
    }

    public function verify2fa(): JsonResponse
    {
        if (Session::has('2fa:user:id')) {
            return successResponse('Redirect to 2fa');
            //            return view('themes.default1.front.enableTwoFactor');
        } else {
            return successResponse('Login page', ['redirect' => url('login')]);
        }
    }

    public function enableTwoFactor(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $google2fa = new Google2FA;
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

    public function postLoginValidateToken(ValidateSecretRequest $request): JsonResponse
    {
        try {
            $session = $request->session();
            $userId = $session->get('2fa:user:id');
            /** @var User $user */
            $user = User::findOrFail($userId);

            return $this->handleTwoFactorLogin($request, $user, '2fa-code', function ($user, $request): void {
                $secret = Crypt::decrypt((string) $user->google2fa_secret);
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

    public function showVerifyPassword(): JsonResponse
    {
        return successResponse('password_confirmation_not_required');
    }

    public function verifyPassword(Request $request): JsonResponse
    {
        if (! $request->user_password && $request->login_type == 'social') {
            Session::put('auth.password_confirmed_at', time());

            return successResponse('password_verified');
        }

        /** @var User $user */
        $user = Auth::user();
        if (Hash::check($request->input('user_password'), $user->getAuthPassword())) {
            Session::put('auth.password_confirmed_at', time());

            return successResponse('password_verified');
        }

        return errorResponse('password_incorrect');
    }

    /**
     * Disables 2FA for a user/agent, wipes out all the details related to 2FA from the Database.
     */
    public function disableTwoFactor(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->userId ? User::where('id', $request->userId)->firstOrFail() : $request->user();
        /** @var User $authUser */
        $authUser = Auth::user();
        if ($authUser->role != 'admin' && $user->id != $authUser->id) {
            return errorResponse(__('message.cannot_disable_2fa'));
        }

        $user->google2fa_secret = null;
        $user->google2fa_activation_date = null;
        $user->is_2fa_enabled = 0;
        $user->save();

        UserBackupCodes::where('user_id', $user->id)->delete();

        return successResponse(__('message.2fa_disabled'));
    }

    public function generateRecoveryCode(): JsonResponse
    {
        $codes = $this->createCodes();
        /** @var User $authUser */
        $authUser = Auth::user();
        $userId = $authUser->id;

        UserBackupCodes::where('user_id', $userId)->delete();
        foreach ($codes as $code) {
            UserBackupCodes::create(['user_id' => $userId, 'backup_codes' => $code]);
        }

        return successResponse('', ['code' => $codes]);
    }

    /**
     * @return array<mixed>
     */
    private function createCodes(): array
    {
        $codes = [];
        for ($i = 0; $i < 10; $i++) {
            $codes[] = bin2hex(random_bytes(10));
        }

        return $codes;
    }

    public function verifyRecoveryCode(Request $request): JsonResponse
    {
        $this->validate($request, [
            'rec_code' => 'required',
            'recovery_code' => [new Honeypot],
        ], [
            'rec_code.required' => __('validation.please_enter_recovery_code'),
        ]);

        try {
            $session = $request->session();
            $userId = $session->get('2fa:user:id');
            /** @var User $user */
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

    private function handleTwoFactorLogin(Request $request, User $user, string $rateLimiterKey, callable $validator): JsonResponse
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

        $loginController = new LoginController;
        $loginController->logActivityLogin($user);
        $loginController->convertCart(); // @phpstan-ignore method.notFound

        return successResponse('', ['redirect' => $loginController->redirectPath()]);
    }

}
