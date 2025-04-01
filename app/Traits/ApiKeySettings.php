<?php

namespace App\Traits;

use App\ApiKey;
use App\FileSystemSettings;
use \DrewM\MailChimp\MailChimp;
use Illuminate\Support\Facades\Http;
use App\Http\Requests\UpdateStoragePathRequest;
use App\Model\Common\Mailchimp\MailchimpSetting;
use App\Model\Common\StatusSetting;
use Aws\Exception\AwsException;
use Aws\S3\S3Client;
use DateTime;
use Illuminate\Http\Request;
use mysql_xdevapi\Exception;

//////////////////////////////////////////////////////////////
//TRAIT FOR SAVING API STATUS AND API KEYS //
////////////////////////////////////////////////////////////////

trait ApiKeySettings
{
    public function licenseDetails(Request $request)
    {
        $status = $request->input('status');
        $licenseApiSecret = $request->input('license_api_secret');
        $licenseApiUrl = $request->input('license_api_url');
        $licenseApiClientId = $request->input('license_client_id');
        $licenseApiClientSecret = $request->input('license_client_secret');
        $licenseApiGrantType = $request->input('license_grant_type');
        StatusSetting::where('id', 1)->update(['license_status' => $status]);
        ApiKey::where('id', 1)->update(['license_api_secret' => $licenseApiSecret, 'license_api_url' => $licenseApiUrl,
            'license_client_id' => $licenseApiClientId, 'license_client_secret' => $licenseApiClientSecret,
            'license_grant_type' => $licenseApiGrantType, ]);

        return ['message' => 'success', 'update' => \Lang::get('message.license_setting')];
    }

    public function licenseStatus(Request $request)
    {
        $status = $request->input();
        if (is_array($status)&& key_exists('status', $status)) {
            $lstatus = $request->input('status');
            StatusSetting::where('id', 1)->update(['license_status' => $lstatus]);
            return ['message' => 'success', 'update' => \Lang::get('message.license_status')];
        }

        if (is_array($status)&& key_exists('mstatus', $status)) {
            $mstatus = $request->input('mstatus');
            StatusSetting::find(1)->update(['msg91_status' => $mstatus]);
            return ['message' => 'success', 'update' => \Lang::get('message.mobile_status')];
        }

        if (is_array($status)&& key_exists('mailchimpstatus', $status)) {
            $mailchimpstatus = $request->input('mailchimpstatus');
            StatusSetting::find(1)->update(['mailchimp_status' => $mailchimpstatus]);
            return ['message' => 'success', 'update' => \Lang::get('message.mailchimp_status')];
        }


        if (is_array($status)&& key_exists('termsStatus', $status)) {
            $termsStatus = $request->input('termsStatus');
            StatusSetting::find(1)->update(['terms' => $termsStatus]);
            return ['message' => 'success', 'update' => \Lang::get('message.terms_status')];
        }

        if (is_array($status)&& key_exists('twitterstatus', $status)) {
            $twitterstatus = $request->input('twitterstatus');
            StatusSetting::find(1)->update(['twitter_status' => $twitterstatus]);
            return ['message' => 'success', 'update' => \Lang::get('message.twitter_status')];
        }

        if (is_array($status)&& key_exists('zohostatus', $status)) {
            $twitterstatus = $request->input('zohostatus');
            StatusSetting::find(1)->update(['zoho_status' => $twitterstatus]);
            return ['message' => 'success', 'update' => \Lang::get('message.zoho_status')];
        }

        if (is_array($status)&& key_exists('pipedrivestatus', $status)) {
            $twitterstatus = $request->input('pipedrivestatus');
            StatusSetting::find(1)->update(['pipedrive_status' => $twitterstatus]);
            return ['message' => 'success', 'update' => \Lang::get('message.pipedrive_status')];
        }

        if (is_array($status)&& key_exists('githubstatus', $status)) {
            $twitterstatus = $request->input('githubstatus');
            StatusSetting::find(1)->update(['github_status' => $twitterstatus]);
            return ['message' => 'success', 'update' => \Lang::get('message.github_status')];
        }

        if (is_array($status)&& key_exists('gcaptchastatus', $status)) {
            $twitterstatus = $request->input('gcaptchastatus');
            StatusSetting::find(1)->update(['v3_v2_recaptcha_status' => $twitterstatus]);
            return ['message' => 'success', 'update' => \Lang::get('message.google_status')];
        }

    }


    public function mobileStatus(Request $request)
    {

        $status = $request->input('status');

    }

    //Save Auto Update status in Database
    public function updateDetails(Request $request)
    {
        $status = $request->input('status');
        $updateApiSecret = $request->input('update_api_secret');
        $updateApiUrl = $request->input('update_api_url');
        StatusSetting::where('id', 1)->update(['update_settings' => $status]);
        ApiKey::where('id', 1)->update(['update_api_secret' => $updateApiSecret, 'update_api_url' => $updateApiUrl]);

        return ['message' => 'success', 'update' => 'Auto update settings saved'];
    }

    /*
     * Update Msg91 Details In Database
     */
    public function updatemobileDetails(Request $request)
    {
        $status = $request->input('status');
        $key = $request->input('msg91_auth_key');
        StatusSetting::find(1)->update(['msg91_status' => $status]);
        ApiKey::find(1)->update(['msg91_auth_key' => $key, 'msg91_sender' => $request->input('msg91_sender'), 'msg91_template_id' => $request->input('msg91_template_id')]);

        return ['message' => 'success', 'update' => \Lang::get('message.mobile_setting')];
    }

    /*
     * Update Zoho Details In Database
     */
    public function updatezohoDetails(Request $request)
    {
        $status = $request->input('status');
        $key = $request->input('zoho_key');
        StatusSetting::find(1)->update(['zoho_status' => $status]);
        ApiKey::find(1)->update(['zoho_api_key' => $key]);

        return ['message' => 'success', 'update' => \Lang::get('message.zoho_status')];
    }

    /*
     * Update Email Status In Database
     */
    public function updateEmailDetails(Request $request)
    {
        $status = $request->input('status');
        StatusSetting::find(1)->update(['emailverification_status' => $status]);
        return ['message' => 'success', 'update' => \Lang::get('message.email_setting')];
    }

    /*
     * Update Domain Check status In Database
     */
    public function updatedomainCheckDetails(Request $request)
    {
        $status = $request->input('status');
        StatusSetting::find(1)->update(['domain_check' => $status]);

        return ['message' => 'success', 'update' => 'Domain check status saved'];
    }

    /*
    * Update Twitter Details In Database
    */
    public function updatetwitterDetails(Request $request)
    {
        $consumer_key = $request->input('consumer_key');
        $consumer_secret = $request->input('consumer_secret');
        $access_token = $request->input('access_token');
        $token_secret = $request->input('token_secret');
        $status = $request->input('status');
        StatusSetting::find(1)->update(['twitter_status' => $status]);
        ApiKey::find(1)->update(['twitter_consumer_key' => $consumer_key, 'twitter_consumer_secret' => $consumer_secret, 'twitter_access_token' => $access_token, 'access_tooken_secret' => $token_secret]);

        return ['message' => 'success', 'update' => \Lang::get('message.twitter_setting')];
    }

    public function updatepipedriveDetails(Request $request)
    {
        $pipedriveKey = $request->input('pipedrive_key');
        $status = $request->input('status');
        StatusSetting::find(1)->update(['pipedrive_status' => $status]);
        ApiKey::find(1)->update(['pipedrive_api_key' => $pipedriveKey]);
        return ['message' => 'success', 'update' => \Lang::get('message.pipedrive_setting')];

    }

    public function updateMailchimpProductStatus(Request $request)
    {
        StatusSetting::first()->update(['mailchimp_product_status' => $request->input('status')]);

        return ['message' => 'success', 'update' => 'Mailchimp products group status saved'];
    }

    public function updateMailchimpIsPaidStatus(Request $request)
    {
        StatusSetting::first()->update(['mailchimp_ispaid_status' => $request->input('status')]);

        return ['message' => 'success', 'update' => 'Mailchimp is paid status saved'];
    }

    public function updateMailchimpDetails(Request $request)
    {
        try {

            $chimp_auth_key = $request->input('mailchimp_auth_key');

            $dc = substr($chimp_auth_key, strpos($chimp_auth_key, '-') + 1);
            // Mailchimp API URL
            $url = "https://{$dc}.api.mailchimp.com/3.0/";
            // Make an API request
            $response = Http::withBasicAuth('anystring', $chimp_auth_key)->get($url);
            if ($response->successful()) {
                $status = $request->input('status');
                StatusSetting::find(1)->update(['mailchimp_status' => $status]);
                MailchimpSetting::find(1)->update(['api_key' => $chimp_auth_key]);
                $mailchimpverifiedStatus=1;


                $mailchimp_set = new MailchimpSetting();
                $set = $mailchimp_set->firstOrFail();
                $mail_api_key = $set->api_key;
                $mailchimp = new \Mailchimp\Mailchimp($mail_api_key);
                $allists = $mailchimp->get('lists?count=20')['lists'];
                $selectedList[] = $set->list_id;
                $subscribe_status=MailchimpSetting::pluck('subscribe_status')->first();

                return [
                    'message' => 'success',
                    'update' => \Lang::get('message.mailchimp_setting'),
                    'mailchimpverifiedStatus' => $mailchimpverifiedStatus,
                    'status'=>$status,
                    'allLists' => $allists,
                    'selectedList' => $selectedList,
                    'subscribe_status' => $subscribe_status,
                ];
            } else {
//                $mailchimpverifiedStatus=0;
                $status = $request->input('status');

                return [
                    'message' => 'error',
                    'update' => \Lang::get('message.mailchimp_apikey_error'),
//                    'mailchimpverifiedStatus' => $mailchimpverifiedStatus
                ];            }
        }catch(\Exception $e){
            $mailchimpStatus=0;
            $status = $request->input('status');

            return [
                'message' => 'error',
                'update' => \Lang::get('message.mailchimp_apikey_error'),
//                'mailchimpStatus' => $mailchimpStatus
            ];
        }
    }

    public function updateTermsDetails(Request $request)
    {
        $terms_url = $request->input('terms_url');
        $status = (int) $request->input('status');
        StatusSetting::find(1)->update(['terms' => $status]);
        ApiKey::find(1)->update(['terms_url' => $terms_url]);

        return ['message' => 'success', 'update' => \Lang::get('message.terms_setting')];
    }

    /**
     * Get Date.
     */
    public function getDate($dbdate)
    {
        $created = new DateTime($dbdate);
        $tz = \Auth::user()->timezone()->first()->name;
        $created->setTimezone(new \DateTimeZone($tz));
        $date = $created->format('M j, Y, g:i a '); //5th October, 2018, 11:17PM
        $newDate = $date;

        return $newDate;
    }

    public function getDateFormat($dbdate = '')
    {
        $created = new DateTime($dbdate);
        $tz = \Auth::user()->timezone()->first()->name;
        $created->setTimezone(new \DateTimeZone($tz));
        $date = $created->format('Y-m-d H:m:i');

        return $date;
    }

    public function saveConditions()
    {
        if (\Request::get('expiry-commands') && \Request::get('activity-commands')) {
            $expiry_commands = \Request::get('expiry-commands');
            $expiry_dailyAt = \Request::get('expiry-dailyAt');
            $activity_commands = \Request::get('activity-commands');
            $activity_dailyAt = \Request::get('activity-dailyAt');
            $subexpiry_commands = \Request::get('subexpiry-commands');
            $subexpiry_dailyAt = \Request::get('subexpiry-dailyAt');
            $postexpiry_commands = \Request::get('postsubexpiry-commands');
            $postexpiry_dailyAt = \Request::get('postsubexpiry-dailyAt');
            $cloud_commands = \Request::get('cloud-commands');
            $cloud_dailyAt = \Request::get('cloud-dailyAt');
            $invoice_commands = \Request::get('invoice-commands');
            $invoice_dailyAt = \Request::get('invoice-dailyAt');

            $activity_command = $this->getCommand($activity_commands, $activity_dailyAt);
            $expiry_command = $this->getCommand($expiry_commands, $expiry_dailyAt);
            $subexpiry_command = $this->getCommand($subexpiry_commands, $subexpiry_dailyAt);
            $postexpiry_command = $this->getCommand($postexpiry_commands, $postexpiry_dailyAt);
            $expiry_command = $this->getCommand($expiry_commands, $expiry_dailyAt);
            $cloud_command = $this->getCommand($cloud_commands, $cloud_dailyAt);
            $invoice_command = $this->getCommand($invoice_commands, $invoice_dailyAt);
            $jobs = ['expiryMail' => $expiry_command, 'deleteLogs' => $activity_command, 'subsExpirymail' => $subexpiry_commands, 'postExpirymail' => $postexpiry_command, 'cloud' => $cloud_command, 'invoice' => $invoice_command];

            $this->storeCommand($jobs);
        }
    }

    public function getCommand($command, $daily_at)
    {
        if ($command == 'dailyAt') {
            $command = "dailyAt,$daily_at";
        }

        return $command;
    }

    public function storeCommand($array = [])
    {
        $command = new \App\Model\Mailjob\Condition();
        $commands = $command->get();
        if ($commands->count() > 0) {
            foreach ($commands as $condition) {
                $condition->delete();
            }
        }
        if (count($array) > 0) {
            foreach ($array as $key => $save) {
                $command->create([
                    'job' => $key,
                    'value' => $save,
                ]);
            }
        }
    }

    public function showFileStorage()
    {
        $fileStorageSettings = FileSystemSettings::first();

        $fileStorage = (object) [
            'disk' => $fileStorageSettings->disk ?? '',
            'local_file_storage_path' => env('STORAGE_PATH', storage_path('app/public')),
            's3_bucket' => env('AWS_BUCKET', ''),
            's3_region' => env('AWS_DEFAULT_REGION', ''),
            's3_access_key' => env('AWS_ACCESS_KEY_ID', ''),
            's3_secret_key' => env('AWS_SECRET_ACCESS_KEY', ''),
            's3_endpoint_url' => env('AWS_ENDPOINT', ''),
            's3_url' => env('AWS_URL', ''),
            's3_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', ''),
        ];

        return view('themes.default1.common.setting.file-storage', compact('fileStorage'));
    }

    public function updateStoragePath(UpdateStoragePathRequest $request)
    {
        $disk = $request->input('disk');
        $fileStorageSettings = FileSystemSettings::first();

        $response = match ($disk) {
            'system' => $this->updateLocalStorage($request, $fileStorageSettings),
            's3' => $this->updateS3Storage($request, $fileStorageSettings),
        };

        if ($response->status() !== 200) {
            return $response;
        }

        $fileStorageSettings->save();

        return successResponse(trans('message.setting_updated'));
    }

    protected function updateLocalStorage($request, $fileStorageSettings)
    {
        $path = $request->input('path');

        $fileStorageSettings->fill([
            'disk' => 'system',
            'local_file_storage_path' => $path,
        ]);

        setEnvValue('STORAGE_PATH', $path);

        return successResponse();
    }

    protected function updateS3Storage($request, $fileStorageSettings)
    {
        $fileStorageSettings->disk = 's3';

        $s3fields = [
            's3_bucket' => $request->input('s3_bucket'),
            's3_region' => $request->input('s3_region'),
            's3_access_key' => $request->input('s3_access_key'),
            's3_secret_key' => $request->input('s3_secret_key'),
            's3_endpoint_url' => $request->input('s3_endpoint_url'),
            's3_url' => $request->input('s3_url'),
            's3_path_style_endpoint' => $request->input('s3_path_style_endpoint'),
        ];

        if (! $this->validateS3Credentials(
            $request->input('s3_region'),
            $request->input('s3_access_key'),
            $request->input('s3_secret_key'),
            $request->input('s3_endpoint_url'),
            $request->input('s3_bucket'),
            $request->input('s3_url'),
            $request->input('s3_path_style_endpoint')
        )) {
            return errorResponse(trans('message.s3_error'));
        }

        $this->updateS3EnvSettings($s3fields);

        return successResponse();
    }

    protected function updateS3EnvSettings($s3fields)
    {
        foreach ($s3fields as $key => $value) {
            $envKey = match ($key) {
                's3_bucket' => 'AWS_BUCKET',
                's3_region' => 'AWS_DEFAULT_REGION',
                's3_access_key' => 'AWS_ACCESS_KEY_ID',
                's3_secret_key' => 'AWS_SECRET_ACCESS_KEY',
                's3_endpoint_url' => 'AWS_ENDPOINT',
                's3_url' => 'AWS_URL',
                's3_path_style_endpoint' => 'AWS_USE_PATH_STYLE_ENDPOINT',
            };

            if ($envKey) {
                setEnvValue($envKey, $value);
            }
        }
    }

    private function validateS3Credentials($s3Region, $s3AccessKey, $s3SecretKey, $s3EndpointUrl, $s3Bucket, $s3Url, $s3PathStyleEndpoint)
    {
        try {
            $s3Client = new S3Client([
                'region' => $s3Region,
                'version' => 'latest',
                'credentials' => [
                    'key' => $s3AccessKey,
                    'secret' => $s3SecretKey,
                ],
                'endpoint' => $s3EndpointUrl,
                'url' => $s3Url,
                'use_path_style_endpoint' => $s3PathStyleEndpoint === 'true' ? true : false,
            ]);

            return $s3Client->doesBucketExist($s3Bucket);
        } catch (AwsException $e) {
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }
}
