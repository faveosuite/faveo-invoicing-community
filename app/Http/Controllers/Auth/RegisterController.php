<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\ProfileRequest;
use App\Jobs\AddUserToExternalService;
use App\Model\Common\Setting;
use App\Model\Common\SocialMedia;
use App\Model\Common\StatusSetting;
use App\Rules\CaptchaValidation;
use App\User;
use Exception;
use Facades\Spatie\Referer\Referer;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
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

    /**
     * This function performs post registration operations(creating user,add user to pipedrive,mailchimp).
     *
     * @param ProfileRequest $request
     * @param User $user
     * @return \HTTP|JsonResponse
     * @throws ValidationException
     */
    public function postRegister(ProfileRequest $request, User $user)
    {
        $this->validate($request, [
            'g-recaptcha-response' => [isCaptchaRequired()['is_required'], new CaptchaValidation()],
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
                'manager' => $user->assignSalesManager(),
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
            $result = [$ex->getMessage()];

            return response()->json($result);
        }
    }

    /**
     * This function returns the email and msg91 status this helps in verifying users email and mobile number.
     *
     * @param
     * @param
     * @return int
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
     *
     * @return int
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

    /**
     * This function returns the rendered widget.
     *
     * @param
     * @param
     * @return \HTTP
     * @throws
     */
    public function footer1(){

        $set = new \App\Model\Common\Setting();
        $set = $set->findOrFail(1);
        $social = SocialMedia::get();
        $footerWidgetTypes = ['footer1','footer2','footer3'];
        $isV2RecaptchaEnabledForNewsletter=0;
        foreach ($footerWidgetTypes as $widgetType) {
            $widget = \App\Model\Front\Widgets::where('publish', 1)->where('type', $widgetType)->select('name', 'content', 'allow_tweets', 'allow_mailchimp', 'allow_social_media')->first();
            $mailchimpKey = \App\Model\Common\Mailchimp\MailchimpSetting::value('api_key');

            if ($widget) {
                $data[$widgetType]=$this->renderWidget($widget, $set, $social, $mailchimpKey);
            }
        }
        return successResponse('success', $data);
    }

    /**
     * This function renders the footer widget.
     *
     * @param $widget
     * @param $set
     * @param $social
     * @param $mailchimpKey
     * @return string
     */
    function renderWidget($widget, $set, $social, $mailchimpKey)
    {
        $tweetDetails = $widget->allow_tweets == 1 ? '<div id="tweets" class="twitter"></div>' : '';

        $socialMedia = '';
        if ($widget->allow_social_media) {
            // Social Media Icons
            $socialMedia .= '<ul class="list list-unstyled">';
            if ($set->company_email) {
                $socialMedia .= '<li class="d-flex align-items-center mb-4">
                                    <i class="fa-regular fa-envelope fa-xl"></i>&nbsp;&nbsp;
                                    <a href="mailto:' . $set->company_email . '" class="d-inline-flex align-items-center text-decoration-none text-color-grey text-color-hover-primary font-weight-semibold text-4-5">' . $set->company_email . '</a>
                                </li>';
            }
            if ($set->phone) {
                $socialMedia .= '<li class="d-flex align-items-center mb-4">
                                    <i class="fas fa-phone text-4 p-relative top-2"></i>&nbsp;
                                    <a href="tel:' . $set->phone . '" class="d-inline-flex align-items-center text-decoration-none text-color-grey text-color-hover-primary font-weight-semibold text-4-5">+' . $set->phone_code . ' ' . $set->phone . '</a>
                                </li>';
            }
            $socialMedia .= '</ul>';

            // Social Icons
            $socialMedia .= '<ul class="social-icons social-icons-clean social-icons-medium">';
            foreach ($social as $media) {
                $socialMedia .= '<li class="social-icons-' . strtolower($media->name) . '">
                                    <a href="' . $media->link . '" target="_blank" data-bs-toggle="tooltip" title="' . ucfirst($media->name) . '">
                                        <i class="fab fa-' . strtolower($media->name) . ' text-color-grey-lighten"></i>
                                    </a>
                                </li>';
            }
            $socialMedia .= '</ul>';
        }

        $status =  StatusSetting::select('recaptcha_status','v3_recaptcha_status', 'msg91_status', 'emailverification_status', 'terms')->first();

        $mailchimpSection = '';
        if ($mailchimpKey !== null && $widget->allow_mailchimp == 1) {
            $mailchimpSection .= '<div id="mailchimp-message" style="width: 86%;"></div>
                                                <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center">
                                                    <form id="newsletterForm" class="form-style-3 w-100">
                                                        <div class="input-group mb-3">
                                                            <input class="custom-input newsletterEmail" placeholder="Email Address" name="newsletterEmail" id="newsletterEmail" type="email">
                                                        </div>
                                                        <!-- Honeypot fields (hidden) -->
                                                        <div class="mb-3" style="display: none;">
                                                            <label>Leave this field empty</label>
                                                            <input type="text" name="mailhoneypot_field" value="">
                                                        </div>';
            if ($status->recaptcha_status === 1 || $status->v3_recaptcha_status === 1) {

                if ($status->recaptcha_status === 1) {
                    $mailchimpSection .= '
            <div class="mb-3">
                <div id="mailchimp_recaptcha"></div>
                <div class="robot-verification mb-3" id="mailchimpcaptcha"></div>
                <span id="mailchimpcaptchacheck"></span>
            </div>
        ';
                } elseif ($status->v3_recaptcha_status === 1) {
                    $mailchimpSection .= '
                <input type="hidden" id="g-recaptcha-mailchimp" class="g-recaptcha-token" name="g-recaptcha-response">
        ';
                }
            }
            $mailchimpSection .= '<button class="btn btn-primary mb-3" id="mailchimp-subscription" type="submit"><strong>GO!</strong></button>
                                            </form>
                                          </div>';
        }

        // Check if the 'menu' class exists in the widget content
        $hasMenuClass = strpos($widget->content, 'menu') !== false;

        // Add class if 'menu' class exists in the widget content
        if ($hasMenuClass) {
            $widget->content = str_replace('<ul', '<ul class="list list-styled columns-lg-2 px-2"', $widget->content);
        }

        return '<div class="col-lg-4">
                    <div class="widget-container">
                        <h4 class="text-color-dark font-weight-bold mb-3">' . $widget->name . '</h4>
                        <div class="widget-content">
                            <p class="text-3-5 font-weight-medium pe-lg-2">' . $widget->content . '</p>
                            ' . $tweetDetails . '
                            ' . ($widget->allow_social_media ? $socialMedia : '') . '
                        </div>
                        ' . $mailchimpSection . '
                    </div>
                </div>';
    }


}
