<?php

namespace App\Http\Controllers\Auth;

use App\EmailValidationResults;
use App\Events\UserRegisteredEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\ProfileRequest;
use App\Model\Common\EmailMobileValidationProviders;
use App\Model\Common\ManagerSetting;
use App\Model\Common\StatusSetting;
use App\Rules\Honeypot;
use App\User;
use Exception;
use Facades\Spatie\Referer\Referer;
use Hash;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Log;
use Logger;
use Session;

class RegisterController extends Controller
{
    use RegistersUsers;

    protected string $redirectTo = '/';

    public function __construct()
    {
        $this->middleware('guest');
        $this->middleware('recaptcha:register')->only('postRegister');
    }

    /**
     * @return array<mixed>
     */
    public function emailVerification(mixed $email): array
    {
        try {
            $map = [
                'safe' => 1,
                'catch_all' => 2,
                'unknown' => 4,
                'invalid' => 8,
                'disabled' => 16,
                'disposable' => 32,
                'inbox_full' => 64,
                'role_account' => 128,
                'spamtrap' => 256,
            ];

            /** @var EmailMobileValidationProviders $reoonProvider */
            $reoonProvider = EmailMobileValidationProviders::where('provider', 'reoon')
                ->select('api_key', 'mode', 'accepted_output')
                ->firstOrFail();
            ['api_key' => $apikey, 'mode' => $mode, 'accepted_output' => $accepted_output] = $reoonProvider->toArray();

            $response = Http::get('https://emailverifier.reoon.com/api/v1/verify', [
                'email' => $email,
                'key' => $apikey,
                'mode' => $mode,
            ]);
            $content = $response->json();
            $status = $content['status'];
            $statusBit = $map[$status] ?? 0;
            $emailResult = EmailValidationResults::create(['email' => $email, 'status' => $status, 'method' => $content['verification_mode'], 'result' => json_encode($content), 'registration' => 'Completed']);
            if (($statusBit & $accepted_output) || $content['status'] == 'valid' || isset($content['reason']) && $content['reason'] == 'Not enough credits available. Please recharge.' || $content['status'] == 'error') {
                return ['status' => true, 'id' => $emailResult->id];
            }

            return ['status' => false, 'id' => $emailResult->id];
        } catch (Exception $exception) {
            Log::error($exception->getMessage());

            // Fail open: if the verifier is unreachable, let the user proceed
            return ['status' => true, 'id' => null];
        }
    }

    private function vonagePhoneVerification(mixed $provider, string $phone): bool
    {
        ['api_key' => $apikey, 'mode' => $mode,'api_secret' => $apisecret] = EmailMobileValidationProviders::where('provider', $provider)
            ->select('api_key', 'mode', 'api_secret')
            ->firstOrFail()
            ->toArray();

        $response = Http::get('https://api.nexmo.com/ni/'.$mode.'/json', [
            'api_key' => $apikey,
            'api_secret' => $apisecret,
            'number' => $phone,
        ]);
        if ($response->successful() && $response->json('status_message') == 'Success') {
            return true;
        }

        return $response->json('status_message') == 'Partner quota exceeded';
    }

    private function abstractPhoneVerification(mixed $provider, string $phone): bool
    {
        $apikey = EmailMobileValidationProviders::where('provider', $provider)->value('api_key');

        $response = Http::get('https://phonevalidation.abstractapi.com/v1/', [
            'api_key' => $apikey,
            'phone' => $phone,
        ]);

        return $response->successful() && $response->json('valid');
    }

    private function phoneVerification(string $phone): bool
    {
        $provider = EmailMobileValidationProviders::where('type', 'mobile')
            ->where('to_use', 1)
            ->value('provider');

        if ($provider == 'vonage') {
            return $this->vonagePhoneVerification($provider, $phone);
        }

        return $this->abstractPhoneVerification($provider, $phone);
    }

    public function postRegister(ProfileRequest $request, User $user): JsonResponse
    {
        $this->validate($request, [
            'registerForm' => [new Honeypot],
        ]);
        try {
            /** @var StatusSetting $status */
            $status = StatusSetting::select(
                'email_validation_status',
                'mobile_validation_status',
                'emailverification_status',
                'msg91_status'
            )->first();

            if ($status->email_validation_status) {
                $emailVerifier = $this->emailVerification($request->input('email'));
                if (! $emailVerifier['status']) {
                    $user = $this->getUserDetails($request);
                    EmailValidationResults::where('id', $emailVerifier['id'])->update($user);

                    return errorResponse(__('message.email_provided_wrong'));
                }
            }

            if ($status->mobile_validation_status) {
                $mobileVerifier = $this->phoneVerification($request->input('mobile_code').$request->input('mobile'));
                if (! $mobileVerifier) {
                    return errorResponse(__('message.mobile_provided_wrong'));
                }
            }

            $location = getLocation();

            $managerSettings = ManagerSetting::whereIn('manager_role', ['account', 'sales'])
                ->pluck('auto_assign', 'manager_role');

            $state = getStateByCode($location['iso_code'], $location['state']);

            $user->state = $state['id'];
            $user->town = $location['city'];
            $user->password = Hash::make($request->input('password'));
            $user->profile_pic = '';
            $user->mobile_verified = 0;
            $user->email_verified = 0;
            $user->mobile = ltrim((string) $request->input('mobile'), '0');
            $user->mobile_code = $request->input('mobile_code');
            $user->mobile_country_iso = $request->input('mobile_country_iso');
            $user->country = $request->input('country');
            $user->company = strip_tags((string) $request->input('company'));
            $user->address = strip_tags((string) $request->input('address'));
            $user->email = strip_tags((string) $request->input('email'));
            $user->user_name = strip_tags((string) $request->input('email'));
            $user->first_name = strip_tags((string) $request->input('first_name'));
            $user->last_name = strip_tags((string) $request->input('last_name'));
            $user->ip = $location['ip'];
            $user->timezone_id = (int) getTimezoneByName($location['timezone']);
            $user->referrer = Referer::get();
            $user->active = 1;
            $user->role = 'user';
            $user->account_manager = $managerSettings->get('account') ? (string) $user->assignManagerByPosition('account_manager') : null;
            $user->manager = $managerSettings->get('sales') ? User::find($user->assignManagerByPosition('manager')) : null;
            $user->save();

            $need_verify = ($status->emailverification_status || $status->msg91_status) ? 1 : 0;

            event(new UserRegisteredEvent($user, 'register'));

            Session::put([
                'justStarted' => true,
                'verification_user_id' => $user->id,
            ]);

            $this->logActivityRegister($user);

            Session::flash('user', $user);

            return successResponse(__('message.registration_complete'), ['need_verify' => $need_verify]);
        } catch (Exception $exception) {
            Logger::exception($exception);

            return errorResponse(__('message.something_wrong'));
        }
    }

    /**
     * @return array<mixed>
     */
    public function getUserDetails(mixed $request): array
    {
        $location = getLocation();

        $state = getStateByCode($location['iso_code'], $location['state']);

        return [
            'state' => $state['id'],
            'town' => $location['city'],
            'mobile' => ltrim((string) $request->input('mobile'), '0'),
            'mobile_code' => $request->input('mobile_code'),
            'mobile_country_iso' => $request->input('mobile_country_iso'),
            'country' => $request->input('country'),
            'company' => strip_tags((string) $request->input('company')),
            'address' => strip_tags((string) $request->input('address')),
            'email' => strip_tags((string) $request->input('email')),
            'first_name' => strip_tags((string) $request->input('first_name')),
            'last_name' => strip_tags((string) $request->input('last_name')),
            'registration' => 'Not Completed',
            'ip' => $location['ip'],
            'timezone_id' => getTimezoneByName($location['timezone']),
        ];
    }

    public function logActivityRegister(mixed $user): void
    {
        if (! $user) {
            return;
        }

        $userUrl = url('clients/'.$user->id);

        $name = e($user->first_name.' '.$user->last_name);
        $message = sprintf("User <a href='%s'><strong>%s</strong></a> was created.", $userUrl, $name);

        logActivity(
            $message,
            'created',
            'authentication',
            $user
        );
    }
}
