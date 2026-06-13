<?php

namespace App\Http\Controllers\Auth;

use App\ApiKey;
use App\Http\Controllers\Controller;
use App\Model\Common\Country;
use App\Model\Common\State;
use App\Model\User\AccountActivate;
use App\User;
use App\VerificationAttempt;
use Illuminate\Http\Request;

class BaseAuthController extends Controller
{
    protected function getNewCountry($newCode)
    {
        return Country::where('phonecode', $newCode)->value('country_code_char2');
    }

    //Required Fields for Zoho
    public function reqFields($user, $email)
    {
        $user = $user->where('email', $email)->first();
        $country = Country::whereCountryCodeChar2($user->country)->value('country_name');
        $state = State::where('country_code', $user->country)
            ->where('iso2', $user->state)
            ->value('state_subdivision_name');
        $phone = $user->mobile;
        $code = $user->mobile_code;
        if ($user) {
            $xml = '      <Leads>
                        <row no="1">
                        <FL val="Lead Source">Faveo Billing</FL>
                        <FL val="Company">'.$user->company.'</FL>
                        <FL val="First Name">'.$user->first_name.'</FL>
                        <FL val="Last Name">'.$user->last_name.'</FL>
                        <FL val="Email">'.$user->email.'</FL>
                        <FL val="Manager">'.$user->manager.'</FL>
                         <FL val="Phone">'.$code.''.$phone.'</FL>
                        <FL val="Mobile">'.$code.''.$phone.'</FL>
                        <FL val="Industry">'.$user->bussiness.'</FL>
                        <FL val="City">'.$user->town.'</FL>
                        <FL val="Street">'.$user->address.'</FL>
                        <FL val="State">'.$state.'</FL>
                        <FL val="Country">'.$country.'</FL>
                        <FL val="Zip Code">'.$user->zip.'</FL>
                        </row>
                        </Leads>';

            return $xml;
        }
    }

    public function sendActivation($email, $method)
    {
        $user = User::where('email', $email)->first();
        $contact = getContactData();
        if (! $user) {
            throw new \Exception(__('message.activation_link_sent'));
        }

        try {
            $activate_model = new AccountActivate();

            if ($method == 'GET') {
                $response = $activate_model->where('email', $email)->first();

                if ($response) {
                    $token = random_int(100000, 999999);
                    $response->update(['token' => $token]);
                } else {
                    // Create a new record if it doesn't exist
                    $token = random_int(100000, 999999);
                    $activate_model->create(['email' => $email, 'token' => $token]);
                }
            } else {
                // For non-GET methods, always create a new record
                $token = random_int(100000, 999999);
                $activate_model->create(['email' => $email, 'token' => $token]);
            }

            // Check the settings
            $settings = \App\Model\Common\Setting::find(1);

            // Retrieve the template
            $template = \App\Model\Common\TemplateType::getSelectedTemplate('welcome_mail');
            $website_url = url('/');
            $replace = [
                'name' => $user->first_name.' '.$user->last_name,
                'username' => $user->email,
                'otp' => $token,
                'website_url' => $website_url,
                'contact' => $contact['contact'],
                'logo' => $contact['logo'],
                'company_email' => $settings->company_email,
                'reply_email' => $settings->company_email,
            ];

            $type = $template?->type()->value('name') ?? '';

            $mail = new \App\Http\Controllers\Common\PhpMailController();
            $mail->SendEmail($settings->email, $user->email, $template->data, $template->name, $template->type()->value('name'), $replace, $type);
        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage());
        }
    }

    protected function addUserToPipedrive($user, $pipeDriveStatus)
    {
        if ($pipeDriveStatus) {
            $token = ApiKey::value('pipedrive_api_key');
            $result = $this->searchUserPresenceInPipedrive($user->email, $token);

            if (! $result) {
                $countryFullName = Country::where('country_code_char2', $user->country)->value('country_name');
                $pipedrive = new \Devio\Pipedrive\Pipedrive($token);

                // Create Organization
                $orgResponse = $pipedrive->organizations->add(['name' => $user->company]);
                $orgId = $orgResponse->getContent()->data->id;

                // Create Person
                $personResponse = $pipedrive->persons()->add([
                    'name' => $user->first_name.' '.$user->last_name,
                    'email' => $user->email,
                    'phone' => '+'.$user->mobile_code.$user->mobile,
                    'org_id' => $orgId,
                ]);

                $personId = $personResponse->getContent()->data->id;

                // Create Deal
                $pipedrive->deals()->add([
                    'title' => $user->company.' deal',
                    'person_id' => $personId,
                    'org_id' => $orgId,
                ]);
            }
        }
    }

    private function searchUserPresenceInPipedrive($email, $token)
    {
        $pipedriveUrl = 'https://api.pipedrive.com/v1/persons/search?term='.$email.'&api_token='.$token;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $pipedriveUrl);
        curl_setopt($ch, CURLOPT_POST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        $result = curl_exec($ch);
        curl_close($ch);

        return json_decode($result)->data->items;
    }

    protected function addUserToZoho($user, $zohoStatus)
    {
        if ($zohoStatus) {
            $zoho = $this->reqFields($user, $user->email);
            $auth = ApiKey::where('id', 1)->value('zoho_api_key');
            $zohoUrl = 'https://crm.zoho.com/crm/private/xml/Leads/insertRecords??duplicateCheck=1&';
            $query = 'authtoken='.$auth.'&scope=crmapi&xmlData='.$zoho;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $zohoUrl);
            /* allow redirects */
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            /* return a response into a variable */
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            /* times out after 30s */
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            /* set POST method */
            curl_setopt($ch, CURLOPT_POST, 1);
            /* add POST fields parameters */
            curl_setopt($ch, CURLOPT_POSTFIELDS, $query); // Set the request as a POST FIELD for curl.

            //Execute cUrl session
            $response = curl_exec($ch);
            curl_close($ch);
        }
    }

    public function emailverificationAttempt($user)
    {
        $attempt = $user->verificationAttempts->first();

        if ($attempt && $attempt->email_attempt) {
            $attempt->email_attempt = $attempt->email_attempt + 1;
            $attempt->save();
        } else {
            VerificationAttempt::where('user_id', $user->id)->update(['email_attempt' => 1]);
        }
    }

    public function mobileVerificationAttempt($user)
    {
        $mobileAttempt = $user->verificationAttempts->first();

        if ($mobileAttempt && $mobileAttempt->mobile_attempt) {
            $mobileAttempt->mobile_attempt = $mobileAttempt->mobile_attempt + 1;
            $mobileAttempt->save();
        } else {
            VerificationAttempt::where('user_id', $user->id)->update(['mobile_attempt' => 1]);
        }
    }

    protected function userNeedVerified(User $user): bool
    {
        $setting = \App\Model\Common\StatusSetting::first(['emailverification_status', 'msg91_status']);

        if ($setting->emailverification_status == 1 && $user->email_verified != 1) {
            return false;
        }

        if ($setting->msg91_status == 1 && $user->mobile_verified != 1) {
            return false;
        }

        return $user->active == 1;
    }
}
