<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\ProfileRequest;
use App\Model\Common\Setting;
use App\Model\Common\StatusSetting;
use App\User;
use Facades\Spatie\Referer\Referer;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Validator;
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

    public function postRegister(ProfileRequest $request, User $user)
    {
        $apiKeys = StatusSetting::value('recaptcha_status');
        $captchaRule = $apiKeys ? 'required|' : 'sometimes|';
        $this->validate($request, [
            'g-recaptcha-response-1' => $captchaRule.'captcha',
        ], [
            'g-recaptcha-response-1.required' => 'Robot Verification Failed. Please Try Again.',
        ]);
        try {
            $location = getLocation();

            $state_code = $location['iso_code'].'-'.$location['state'];

            $state = getStateByCode($state_code);

            $user = [
                'state' => $state['id'],
                'town' => $location['city'],
                'password' => \Hash::make($request->input('password')),
                'profile_pic' => '',
                'active' => 0,
                'mobile_verified' => 0,
                'mobile' => ltrim($request->input('mobile'), '0'),
                'mobile_code' => $request->input('mobile_code'),
                'country' => $request->input('country'),
                'role' => 'user',
                'company' => strip_tags($request->input('company')),
                'address' => strip_tags($request->input('address')),
                'email' => strip_tags($request->input('email')),
                'user_name' => strip_tags($request->input('email')),
                'first_name' => strip_tags($request->input('first_name')),
                'last_name' => strip_tags($request->input('last_name')),
                'manager' => $user->assignSalesManager(),
                'ip' => $location['ip'],
                'timezone_id' => getTimezoneByName($location['timezone']),
                'referrer' => Referer::get(),

            ];

            $userInput = User::create($user);

            $mailchimpStatus = StatusSetting::value('mailchimp_status');
            if ($mailchimpStatus == 1) {
                $mailchimp = new \App\Http\Controllers\Common\MailChimpController();
                $r = $mailchimp->addSubscriber($user);
            }

            activity()->log('User <strong>'.$user['first_name'].' '.$user['last_name'].'</strong> was created');

            $need_verify = $this->getEmailMobileStatusResponse($userInput);

            \Session::flash('user', $userInput);

            return successResponse(__('message.registration_complete'), ['need_verify' => $need_verify]);
        } catch (\Exception $ex) {
            app('log')->error($ex->getMessage());
            $result = [$ex->getMessage()];

            return response()->json($result);
        }
    }

    protected function getEmailMobileStatusResponse($user)
    {
        $response = StatusSetting::first(['emailverification_status', 'msg91_status']);

        // Set default values
        $user->mobile_verified = 0;
        $user->active = 0;

        if ($response->msg91_status === 1) {
            $user->active = $response->emailverification_status !== 1 ? 1 : 0;
        }

        if ($response->emailverification_status === 1 && $response->msg91_status !== 1) {
            $user->mobile_verified = 1;
        }

        $user->save();

        // Determine the need to verify
        $need_verify = ($response->emailverification_status === 0 && $response->msg91_status === 0) ? 0 : 1;

        return $need_verify;
    }


    protected function getUserCurrency($userCountry)
    {
        $currency = Setting::find(1)->default_currency;
        if ($userCountry->currency->status) {
            return $userCountry->currency->code;
        }

        return $currency;
    }

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
