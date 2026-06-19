<?php

namespace App\Traits;

use App\ApiKey;
use App\FileSystemSettings;
use App\Http\Requests\UpdateStoragePathRequest;
use App\Model\Common\Mailchimp\MailchimpSetting;
use App\Model\Common\StatusSetting;
use App\Model\Mailjob\Condition;
use Auth;
use Aws\Exception\AwsException;
use Aws\S3\S3Client;
use DateTime;
use DateTimeZone;
use DrewM\MailChimp\MailChimp;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Lang;

//////////////////////////////////////////////////////////////
//TRAIT FOR SAVING API STATUS AND API KEYS //
////////////////////////////////////////////////////////////////

trait ApiKeySettings
{
    public function licenseStatus(Request $request): \Illuminate\Http\JsonResponse
    {
        $statusData = collect([
            'mstatus' => ['key' => 'msg91_status',         'lang' => __('message.mobile_status')],
            'mailchimpstatus' => ['key' => 'mailchimp_status',     'lang' => __('message.mailchimp_status')],
            'gcaptchastatus' => ['key' => 'recaptcha_status', 'lang' => __('message.google_status')],
            'termsStatus' => ['key' => 'terms',                'lang' => __('message.terms_status')],
            'pipedrivestatus' => ['key' => 'pipedrive_status',     'lang' => __('message.pipedrive_status')],
            'githubstatus' => ['key' => 'github_status',        'lang' => __('message.github_status')],
            'email_validation_status' => ['key' => 'email_validation_status', 'lang' => __('message.email_validation_status')],
            'mobile_validation_status' => ['key' => 'mobile_validation_status', 'lang' => __('message.mobile_validation_status')],
            'whatsapp_status' => ['key' => 'whatsapp_status', 'lang' => 'Whatsapp Status updated successfully'],
        ]);

        try {
            $input = $request->all();

            // Find the first matching status key
            $statusEntry = $statusData->first(fn ($value, $inputKey): bool => array_key_exists($inputKey, $input));

            if (! $statusEntry) {
                return errorResponse(__('message.invalid_key'));
            }

            $inputKey = array_key_first(array_intersect_key($input, $statusData->all()));
            $statusValue = $input[$inputKey];

            StatusSetting::where('id', 1)->update([
                $statusEntry['key'] => $statusValue,
            ]);

            return successResponse($statusEntry['lang']);
        } catch (Exception) {
            return errorResponse(__('message.invalid_key'));
        }
    }

    public function mobileStatus(Request $request): void
    {
        $request->input('status');
    }

    //Save Auto Update status in Database
    /**
     * @return array<mixed>
     */
    public function updateDetails(Request $request): array
    {
        $status = $request->input('status');
        $updateApiSecret = $request->input('update_api_secret');
        $updateApiUrl = $request->input('update_api_url');
        StatusSetting::where('id', 1)->update(['update_settings' => $status]);
        ApiKey::where('id', 1)->update(['update_api_secret' => $updateApiSecret, 'update_api_url' => $updateApiUrl]);

        return ['message' => 'success', 'update' => __('message.auto_update_settings_saved')];
    }

    /*
     * Update Msg91 Details In Database
     */
    public function updatemobileDetails(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'msg91_auth_key' => ['required', 'string'],
            'msg91_sender' => ['required', 'string'],
            'msg91_template_id' => ['required', 'string'],
        ]);

        $authKey = trim($request->input('msg91_auth_key'));
        $sender = trim($request->input('msg91_sender'));
        $templateId = trim($request->input('msg91_template_id'));
        $thirdPartyId = $request->input('thirdPartyId');
        $status = $request->input('status');

        // Validate the authkey against the MSG91 OTP Analytics endpoint.
        // This is a read-only GET — no OTP is sent and no credits are consumed.
        // MSG91 returns 401 for an invalid authkey, 200 for a valid one.
        try {
            $today = now()->toDateString();
            $response = Http::withHeaders([
                'Authkey' => $authKey,
                'Accept' => 'application/json',
            ])->get('https://control.msg91.com/api/v5/report/analytics/p/otp', [
                'startDate' => $today,
                'endDate' => $today,
            ]);

            if ($response->status() === 401) {
                return errorResponse(__('message.mobile_authkey'));
            }
        } catch (Exception) {
            return errorResponse(__('message.mobile_authkey'));
        }

        StatusSetting::where('id', 1)->update(['msg91_status' => $status]);

        ApiKey::where('id', 1)->update([
            'msg91_auth_key' => $authKey,
            'msg91_sender' => $sender,
            'msg91_template_id' => $templateId,
            'msg91_third_party_id' => $thirdPartyId,
        ]);

        return successResponse(__('message.mobile_setting'));
    }

    /*
     * Update Zoho Details In Database
     */
    /**
     * @return array<mixed>
     */
    public function updatezohoDetails(Request $request): array
    {
        $status = $request->input('status');
        $key = $request->input('zoho_key');
        StatusSetting::where('id', 1)->update(['zoho_status' => $status]);
        ApiKey::where('id', 1)->update(['zoho_api_key' => $key]);

        return ['message' => 'success', 'update' => Lang::get('message.zoho_status')];
    }

    /*
     * Update Email Status In Database
     */
    /**
     * @return array<mixed>
     */
    public function updateEmailDetails(Request $request): array
    {
        $status = $request->input('status');
        StatusSetting::where('id', 1)->update(['emailverification_status' => $status]);

        return ['message' => 'success', 'update' => Lang::get('message.email_setting')];
    }

    /*
     * Update Domain Check status In Database
     */
    /**
     * @return array<mixed>
     */
    public function updatedomainCheckDetails(Request $request): array
    {
        $status = $request->input('status');
        StatusSetting::where('id', 1)->update(['domain_check' => $status]);

        return ['message' => 'success', 'update' => __('message.domain_check_status_saved')];
    }

    /*
    * Update Twitter Details In Database
    */
    /**
     * @return array<mixed>
     */
    public function updatetwitterDetails(Request $request): array
    {
        $consumer_key = $request->input('consumer_key');
        $consumer_secret = $request->input('consumer_secret');
        $access_token = $request->input('access_token');
        $token_secret = $request->input('token_secret');
        $status = $request->input('status');
        StatusSetting::where('id', 1)->update(['twitter_status' => $status]);
        ApiKey::where('id', 1)->update(['twitter_consumer_key' => $consumer_key, 'twitter_consumer_secret' => $consumer_secret, 'twitter_access_token' => $access_token, 'access_tooken_secret' => $token_secret]);

        return ['message' => 'success', 'update' => Lang::get('message.twitter_setting')];
    }

    public function updatepipedriveDetails(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $pipedriveKey = $request->input('pipedrive_key');
            $status = $request->input('status');
            $verificationStatus = (bool) $request->input('require_pipedrive_user_verification');

            $response = Http::get('https://api.pipedrive.com/v1/users/me', [
                'api_token' => $pipedriveKey,
            ]);
            if (! $response->successful()) {
                return errorResponse(__('message.pipedrive_error'));
            }

            $result = json_decode($response, associative: true);
            if (isset($result['success']) && $result['success'] !== true) {
                return errorResponse(__('message.pipedrive_error'));
            }

            StatusSetting::where('id', 1)->update(['pipedrive_status' => $status]);
            ApiKey::where('id', 1)->update(['pipedrive_api_key' => $pipedriveKey]);
            ApiKey::where('id', 1)->update(['require_pipedrive_user_verification' => $verificationStatus]);

            return successResponse(__('message.pipedrive_setting'));
        } catch (Exception) {
            return errorResponse(__('message.pipedrive_error'));
        }
    }

    /**
     * @return array<mixed>
     */
    public function updateMailchimpProductStatus(Request $request): array
    {
        StatusSetting::where('id', 1)->update(['mailchimp_product_status' => $request->input('status')]);

        return ['message' => 'success', 'update' => __('message.mailchimp_products_group_status_saved')];
    }

    /**
     * @return array<mixed>
     */
    public function updateMailchimpIsPaidStatus(Request $request): array
    {
        StatusSetting::where('id', 1)->update(['mailchimp_ispaid_status' => $request->input('status')]);

        return ['message' => 'success', 'update' => __('message.mailchimp_is_paid_status_saved')];
    }

    public function updateMailchimpDetails(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $chimp_auth_key = $request->input('mailchimp_auth_key');

            $dc = substr((string) $chimp_auth_key, strpos((string) $chimp_auth_key, '-') + 1);
            // Mailchimp API URL
            $url = sprintf('https://%s.api.mailchimp.com/3.0/', $dc);
            // Make an API request
            $response = Http::withBasicAuth('anystring', $chimp_auth_key)->get($url);
            if ($response->successful()) {
                $status = $request->input('status');
                StatusSetting::where('id', 1)->update(['mailchimp_status' => $status]);
                MailchimpSetting::where('id', 1)->update(['api_key' => $chimp_auth_key]);
                $mailchimpverifiedStatus = 1;

                $mailchimp_set = new MailchimpSetting();
                $set = $mailchimp_set->firstOrFail();
                $mail_api_key = $set->api_key;
                $mailchimp = new \Mailchimp\Mailchimp($mail_api_key); // @phpstan-ignore class.notFound
                $allists = $mailchimp->get('lists?count=20')['lists']; // @phpstan-ignore class.notFound
                $selectedList[] = $set->list_id;
                $subscribe_status = MailchimpSetting::value('subscribe_status');
                $data = ['mailchimpverifiedStatus' => $mailchimpverifiedStatus,
                    'status' => $status,
                    'allLists' => $allists,
                    'selectedList' => $selectedList,
                    'subscribe_status' => $subscribe_status, ];

                return successResponse(__('message.mailchimp_setting'), $data);
            }

            return errorResponse(__('message.mailchimp_apikey_error'));
        } catch(Exception) {
            return errorResponse(__('message.mailchimp_apikey_error'));
        }
    }

    public function updateTermsDetails(Request $request): \Illuminate\Http\JsonResponse
    {
        $terms_url = $request->input('terms_url');
        try {
            $response = Http::get($terms_url);

            if ($response == false) { // @phpstan-ignore equal.alwaysFalse
                return errorResponse(__('message.terms_error'));
            }

            $status = (int) $request->input('status');
            StatusSetting::where('id', 1)->update(['terms' => $status]);
            ApiKey::where('id', 1)->update(['terms_url' => $terms_url]);

            return successResponse(__('message.terms_setting'));
        } catch (Exception) {
            return errorResponse(__('message.terms_error'));
        }
    }

    /**
     * Get Date.
     */
    public function getDate(mixed $dbdate): mixed
    {
        $created = new DateTime($dbdate);
        $user = Auth::user();
        if (!$user instanceof \App\User) {
            $tz = 'UTC';
        } else {
            $timezone = $user->timezone()->first();
            $tz = $timezone instanceof \App\Model\Common\Timezone ? $timezone->name : 'UTC';
        }
        $created->setTimezone(new DateTimeZone($tz));
        $date = $created->format('M j, Y, g:i a '); //5th October, 2018, 11:17PM
        $newDate = $date;

        return $newDate;
    }

    public function getDateFormat(mixed $dbdate = ''): string
    {
        $created = new DateTime($dbdate);
        $user = Auth::user();
        if (!$user instanceof \App\User) {
            $tz = 'UTC';
        } else {
            $timezone = $user->timezone()->first();
            $tz = $timezone instanceof \App\Model\Common\Timezone ? $timezone->name : 'UTC';
        }
        $created->setTimezone(new DateTimeZone($tz));

        return $created->format('Y-m-d H:m:i');
    }

    public function saveConditions(Request $request): void
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
            $msg91_commands = \Request::get('msg91-commands');
            $msg91_dailyAt = \Request::get('msg91-dailyAt');
            $reoon_commands = \Request::get('reoon-commands');
            $reoon_dailyAt = \Request::get('reoon-dailyAt');

            $system_commands = \Request::get('systemlogs-commands');
            $system_dailyAt = \Request::get('systemlogs-dailyAt');
            $installationlogs_commands = \Request::get('installationlogs-commands');
            $installationlogs_dailyAt = \Request::get('installationlogs-dailyAt');
            $licensereports_commands = \Request::get('licensereports-commands');
            $licensereports_dailyAt = \Request::get('licensereports-dailyAt');
            $licensecallbacks_commands = \Request::get('licensecallbacks-commands');
            $licensecallbacks_dailyAt = \Request::get('licensecallbacks-dailyAt');
            $licensecrack_commands = \Request::get('licensecrack-commands');
            $licensecrack_dailyAt = \Request::get('licensecrack-dailyAt');
            $licensesystem_commands = \Request::get('licensesystem-commands');
            $licensesystem_dailyAt = \Request::get('licensesystem-dailyAt');
            $licenseversions_commands = \Request::get('licenseversions-commands');
            $licenseversions_dailyAt = \Request::get('licenseversions-dailyAt');

            $activity_command = $this->getCommand($activity_commands, $activity_dailyAt);
            $expiry_command = $this->getCommand($expiry_commands, $expiry_dailyAt);
            $subexpiry_command = $this->getCommand($subexpiry_commands, $subexpiry_dailyAt);
            $postexpiry_command = $this->getCommand($postexpiry_commands, $postexpiry_dailyAt);
            $expiry_command = $this->getCommand($expiry_commands, $expiry_dailyAt);
            $cloud_command = $this->getCommand($cloud_commands, $cloud_dailyAt);
            $invoice_command = $this->getCommand($invoice_commands, $invoice_dailyAt);
            $msg91_command = $this->getCommand($msg91_commands, $msg91_dailyAt);
            $reoon_command = $this->getCommand($reoon_commands, $reoon_dailyAt);
            $system_command = $this->getCommand($system_commands, $system_dailyAt);
            $installationlogs_command = $this->getCommand($installationlogs_commands, $installationlogs_dailyAt);
            $licensereports_command = $this->getCommand($licensereports_commands, $licensereports_dailyAt);
            $licensecallbacks_command = $this->getCommand($licensecallbacks_commands, $licensecallbacks_dailyAt);
            $licensecrack_command = $this->getCommand($licensecrack_commands, $licensecrack_dailyAt);
            $licensesystem_command = $this->getCommand($licensesystem_commands, $licensesystem_dailyAt);
            $licenseversions_command = $this->getCommand($licenseversions_commands, $licenseversions_dailyAt);

            $jobs = [
                'expiryMail' => $expiry_command, 'deleteLogs' => $activity_command, 'subsExpirymail' => $subexpiry_command, 'postExpirymail' => $postexpiry_command,
                'cloud' => $cloud_command, 'invoice' => $invoice_command, 'msg91Reports' => $msg91_command, 'reoon' => $reoon_command, 'systemLogs' => $system_command,
                'installationLogs' => $installationlogs_command, 'licenseReportsCleanup' => $licensereports_command,
                'licenseCallbacksCleanup' => $licensecallbacks_command, 'licenseCrackReportsCleanup' => $licensecrack_command,
                'licenseSystemReportsCleanup' => $licensesystem_command, 'licenseVersionsCleanup' => $licenseversions_command,
            ];

            $this->storeCommand($jobs);
        }
    }

    public function getCommand(mixed $command, string $daily_at): mixed
    {
        if ($command == 'dailyAt') {
            return 'dailyAt,'.$daily_at;
        }

        return $command;
    }

    /**
     * @param array<mixed> $jobs
     */
    public function storeCommand(array $jobs = []): void
    {
        $model = new Condition();

        // Clear all previous commands
        Condition::truncate();

        // Insert all new commands
        foreach ($jobs as $job => $value) {
            $model->create([
                'job' => $job,
                'value' => $value,
            ]);
        }
    }

    public function showFileStorage(): \Illuminate\Http\JsonResponse
    {
        try {
            $fileStorageSettings = FileSystemSettings::first();
            if (!$fileStorageSettings instanceof FileSystemSettings) {
                throw new Exception('File system settings not configured.');
            }

            $fileStorage = [
                'disk' => $fileStorageSettings->disk ?? '',
                'local_file_storage_path' => config('custom.storage_path', storage_path('app/public')),
                's3_bucket' => config('filesystems.disks.s3.bucket', ''),
                's3_region' => config('filesystems.disks.s3.region', ''),
                's3_access_key' => config('filesystems.disks.s3.key', ''),
                's3_secret_key' => config('filesystems.disks.s3.secret', ''),
                's3_endpoint_url' => config('filesystems.disks.s3.endpoint', ''),
                's3_url' => config('filesystems.disks.s3.url', ''),
                's3_path_style_endpoint' => config('filesystems.disks.s3.use_path_style_endpoint', ''),
            ];

            return successResponse('', $fileStorage);
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    public function updateStoragePath(UpdateStoragePathRequest $request): \Illuminate\Http\JsonResponse
    {
        $disk = $request->input('disk');
        $fileStorageSettings = FileSystemSettings::first();
        if (!$fileStorageSettings instanceof FileSystemSettings) {
            return errorResponse('File system settings not configured.');
        }

        $response = match ($disk) { // @phpstan-ignore match.unhandled
            'system' => $this->updateLocalStorage($request, $fileStorageSettings),
            's3' => $this->updateS3Storage($request, $fileStorageSettings),
        };

        if ($response->status() !== 200) {
            return $response;
        }

        $fileStorageSettings->save();

        return successResponse(trans('message.setting_updated'));
    }

    protected function updateLocalStorage(mixed $request, mixed $fileStorageSettings): \Illuminate\Http\JsonResponse
    {
        $path = $request->input('path');

        $fileStorageSettings->fill([
            'disk' => 'system',
            'local_file_storage_path' => $path,
        ]);

        setEnvValue(['STORAGE_PATH' => $path]);

        return successResponse();
    }

    protected function updateS3Storage(mixed $request, mixed $fileStorageSettings): \Illuminate\Http\JsonResponse
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

    protected function updateS3EnvSettings(mixed $s3fields): void
    {
        foreach ($s3fields as $key => $value) {
            $envKey = match ($key) { // @phpstan-ignore match.unhandled
                's3_bucket' => 'AWS_BUCKET',
                's3_region' => 'AWS_DEFAULT_REGION',
                's3_access_key' => 'AWS_ACCESS_KEY_ID',
                's3_secret_key' => 'AWS_SECRET_ACCESS_KEY',
                's3_endpoint_url' => 'AWS_ENDPOINT',
                's3_url' => 'AWS_URL',
                's3_path_style_endpoint' => 'AWS_USE_PATH_STYLE_ENDPOINT',
            };

            setEnvValue([$envKey => $value]);
        }
    }

    protected function validateS3Credentials(mixed $s3Region, mixed $s3AccessKey, mixed $s3SecretKey, mixed $s3EndpointUrl, mixed $s3Bucket, mixed $s3Url, mixed $s3PathStyleEndpoint): mixed
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
                'use_path_style_endpoint' => $s3PathStyleEndpoint === 'true',
            ]);

            return $s3Client->doesBucketExist($s3Bucket);
        } catch (AwsException|Exception) {
            return false;
        }
    }

    public function showPdfSettings(): \Illuminate\Http\JsonResponse
    {
        try {
            $settings = FileSystemSettings::first();

            return successResponse('', [
                'node_path' => $settings->node_path ?? '',
                'npm_path' => $settings->npm_path ?? '',
                'chrome_path' => $settings->chrome_path ?? '',
            ]);
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    public function updatePdfSettings(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $settings = FileSystemSettings::firstOrNew([]);
            $settings->fill([
                'node_path' => $request->input('node_path', ''),
                'npm_path' => $request->input('npm_path', ''),
                'chrome_path' => $request->input('chrome_path', ''),
            ]);
            $settings->save();

            return successResponse(trans('message.setting_updated'));
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }
}
