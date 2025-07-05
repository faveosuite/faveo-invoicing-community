<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\ProfileRequest;
use App\Jobs\AddUserToExternalService;
use App\Model\Common\EmailMobileValidationProviders;
use App\Model\Common\ManagerSetting;
use App\Model\Common\Setting;
use App\Model\Common\StatusSetting;
use App\Rules\CaptchaValidation;
use App\Rules\Honeypot;
use App\User;
use Exception;
use Facades\Spatie\Referer\Referer;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Symfony\Component\Mime\Email;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */
    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    public function emailVerification($email)
    {
        $map = [
            'safe' => 1,
            'catch_all' => 2,
            'unknown' => 4,
        ];

        ['api_key' => $apikey, 'mode' => $mode,'accepted_output' => $accepted_output] = EmailMobileValidationProviders::where('provider', 'reoon')
            ->select('api_key', 'mode', 'accepted_output')
            ->first()
            ->toArray();

        $response = Http::get('https://emailverifier.reoon.com/api/v1/verify', [
            'email' => $email,
            'key' => $apikey,
            'mode' => $mode,
        ]);
        $content = $response->json();
        $status = $content['status'];
        $statusBit = $map[$status] ?? 0;

        if (($statusBit & $accepted_output) || $content['status'] == 'valid' || isset($content['reason']) && $content['reason'] == 'Not enough credits available. Please recharge.') {
            return true;
        }

        return false;
    }

    private function vonagePhoneVerification($provider, $phone)
    {
        ['api_key' => $apikey, 'mode' => $mode,'api_secret' => $apisecret] = EmailMobileValidationProviders::where('provider', $provider)
            ->select('api_key', 'mode', 'api_secret')
            ->first()
            ->toArray();

        $response = Http::get('https://api.nexmo.com/ni/'.$mode.'/json', [
            'api_key' => $apikey,
            'api_secret' => $apisecret,
            'number' => $phone,
        ]);
        if ($response->successful() && $response->json('status_message') == 'Success' || $response->json('status_message') == 'Partner quota exceeded') {
            return true;
        }

        return false;
    }

    private function abstractPhoneVerification($provider, $phone)
    {
        $apikey = EmailMobileValidationProviders::where('provider', $provider)->value('api_key');

        $response = Http::get('https://phonevalidation.abstractapi.com/v1/', [
            'api_key' => $apikey,
            'phone' => $phone,
        ]);

        if ($response->successful() && $response->json('valid')) {
            return true;
        }

        return false;
    }

    private function phoneVerification($phone)
    {
        $provider = EmailMobileValidationProviders::where('type', 'mobile')
            ->where('to_use', 1)
            ->value('provider');

        if ($provider == 'vonage') {
            $response = $this->vonagePhoneVerification($provider, $phone);
        } else {
            $response = $this->abstractPhoneVerification($provider, $phone);
        }

        return $response;
    }

    /**
     * This function performs post registration operations(creating user,add user to pipedrive,mailchimp).
     *
     * @param  ProfileRequest  $request
     * @param  User  $user
     * @return \HTTP|JsonResponse
     *
     * @throws ValidationException
     */
    public function postRegister(ProfileRequest $request, User $user)
    {
        $this->validate($request, [
            'g-recaptcha-response' => [isCaptchaRequired()['is_required'], new CaptchaValidation('register')],
            'registerForm' => [new Honeypot()],
        ]);
        try {
            [$emailValidationStatus, $mobileValidationStatus] = array_values(StatusSetting::select('email_validation_status', 'mobile_validation_status')->first()->toArray());

            if ($emailValidationStatus) {
                $emailVerifier = $this->emailVerification($request->input('email'));
                if (! $emailVerifier) {
                    return errorResponse(\Lang::get('message.email_provided_wrong'));
                }
            }

            if ($mobileValidationStatus) {
                $mobileVerifier = $this->phoneVerification($request->input('mobile_code').$request->input('mobile'));
                if (! $mobileVerifier) {
                    return errorResponse(\Lang::get('message.mobile_provided_wrong'));
                }
            }

            $location = getLocation();

            $accountManagerStatus = ManagerSetting::whereManagerRole('account')->value('auto_assign');
            $salesManagerStatus = ManagerSetting::whereManagerRole('sales')->value('auto_assign');

            $state = getStateByCode($location['iso_code'], $location['state']);

            $user = [
                'state' => $state['id'],
                'town' => $location['city'],
                'password' => \Hash::make($request->input('password')),
                'profile_pic' => '',
                'active' => 1,
                'mobile_verified' => 0,
                'email_verified' => 0,
                'mobile' => ltrim($request->input('mobile'), '0'),
                'mobile_code' => $request->input('mobile_code'),
                'mobile_country_iso' => $request->input('mobile_country_iso'),
                'country' => $request->input('country'),
                'role' => 'user',
                'company' => strip_tags($request->input('company')),
                'address' => strip_tags($request->input('address')),
                'email' => strip_tags($request->input('email')),
                'user_name' => strip_tags($request->input('email')),
                'first_name' => strip_tags($request->input('first_name')),
                'last_name' => strip_tags($request->input('last_name')),
                'account_manager' => $accountManagerStatus ? $user->assignManagerByPosition('account_manager') : null,
                'manager' => $salesManagerStatus ? $user->assignManagerByPosition('manager') : null,
                'ip' => $location['ip'],
                'timezone_id' => getTimezoneByName($location['timezone']),
                'referrer' => Referer::get(),

            ];

            $userInput = User::create($user);

            activity()->log('User <strong>'.$user['first_name'].' '.$user['last_name'].'</strong> was created');

            $need_verify = $this->getEmailMobileStatusResponse();

            AddUserToExternalService::dispatch($userInput);

            $userInput->save();

            \Session::flash('user', $userInput);

            return successResponse(__('message.registration_complete'), ['need_verify' => $need_verify]);
        } catch (Exception $ex) {
            app('log')->error($ex->getMessage());

            return errorResponse($ex->getMessage());
        }
    }

    /**
     * This function returns the email and msg91 status this helps in verifying users email and mobile number.
     *
     * @param
     * @param
     * @return int
     *
     * @throws
     */
    protected function getEmailMobileStatusResponse()
    {
        $response = StatusSetting::first(['emailverification_status', 'msg91_status']);

        return ($response->emailverification_status || $response->msg91_status) ? 1 : 0;
    }

    /**
     * This function returns the default currency.
     *
     * @param
     * @param
     * @return int
     *
     * @throws
     */
    protected function getUserCurrency($userCountry)
    {
        $currency = Setting::find(1)->default_currency;
        if ($userCountry->currency->status) {
            return $userCountry->currency->code;
        }

        return $currency;
    }

    /**
     * This function returns the default currency symbol.
     *
     * @param
     * @return int
     *
     * @throws
     */
    protected function getUserCurrencySymbol($userCountry)
    {
        $currency_symbol = Setting::find(1)->default_symbol;
        if ($userCountry->currency->status) {
            return $userCountry->currency->symbol;
        }

        return $currency_symbol;
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        // return Validator::make($data, [
        //     'name' => 'required|string|max:255',
        //     'email' => 'required|string|email|max:255|unique:users',
        //     'password' => 'required|string|min:6|confirmed',
        // ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data)
    {
        // return User::create([
        //     'name' => $data['name'],
        //     'email' => $data['email'],
        //     'password' => bcrypt($data['password']),
        // ]);
    }
}
