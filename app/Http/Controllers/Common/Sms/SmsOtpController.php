<?php

namespace App\Http\Controllers\Common\Sms;

use Exception;
use Logger;
use App\ApiKey;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Support\Facades\Http;

class SmsOtpController extends Controller
{
    /**
     * MSG91 API base URL.
     */
    protected const string BASE_URL = 'https://api.msg91.com/api/v5/otp';

    /**
     * Default OTP length.
     */
    protected const int OTP_LENGTH = 6;

    /**
     * Default OTP expiry in minutes.
     */
    protected const int OTP_EXPIRY_MINUTES = 10;

    /**
     * Cached MSG91 credentials to avoid repeated DB queries within a request.
     */
    protected ?object $cachedCredentials = null;

    /**
     * Retrieve and cache MSG91 credentials from the database.
     *
     * Caches the result per request lifecycle to avoid redundant queries
     * when multiple SMS operations are performed in the same request.
     */
    protected function getCredentials(): object
    {
        if ($this->cachedCredentials === null) {
            $this->cachedCredentials = ApiKey::find(1, [
                'msg91_auth_key',
                'msg91_sender',
                'msg91_template_id',
            ]);
        }

        return $this->cachedCredentials;
    }

    /**
     * Make an authenticated HTTP request to the MSG91 API.
     *
     * @param  string  $method  HTTP method (GET, POST, etc.)
     * @param  string  $url  Full MSG91 API endpoint URL
     * @param  array  $queryParams  Query parameters for the request
     * @return array{status: int, body: array} Response with status code and decoded body
     */
    public function makeRequest(string $method, string $url, array $queryParams = []): array
    {
        $credentials = $this->getCredentials();

        try {
            $response = Http::withHeaders([
                'authkey' => $credentials->msg91_auth_key,
                'Content-Type' => 'application/json',
            ])
                ->withOptions([
                    'curl' => [
                        CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
                    ],
                ])
                ->send($method, $url, [
                    'query' => $queryParams,
                ]);

            return [
                'status' => $response->status(),
                'body' => $response->json() ?? [],
            ];
        } catch (Exception $e) {
            Logger::exception($e);

            return $this->errorPayload('There was an error processing your request');
        }
    }

    /**
     * Send OTP to a mobile number via MSG91.
     *
     * @param  string  $mobile  Full mobile number with country code (e.g. "919876543210")
     * @param  int|null  $userID  Optional user ID for tracking the OTP request in delivery reports
     * @return array{type: string, message: string}
     */
    public function sendOtp(string $mobile, ?int $userID = null, string $source = 'register', array $mobileInfo = []): array
    {
        $mobile = $this->sanitizeMobile($mobile);
        $credentials = $this->getCredentials();

        $queryParams = [
            'template_id' => $credentials->msg91_template_id,
            'sender' => $credentials->msg91_sender,
            'mobile' => $mobile,
            'otp_length' => self::OTP_LENGTH,
            'otp_expiry' => self::OTP_EXPIRY_MINUTES,
        ];

        $response = $this->makeRequest('POST', self::BASE_URL, $queryParams);

        $this->trackOtpRequest($response, $userID, $source, 'send', $mobileInfo);

        return $this->responseHandler($response);
    }

    /**
     * Resend OTP via text or voice call.
     *
     * @param  string  $mobile  Full mobile number with country code
     * @param  string  $type  Retry type: 'text' for SMS, 'voice' for voice call
     * @return array{type: string, message: string}
     */
    public function sendForReOtp(string $mobile, string $type, $userID = null, string $source = 'register', array $mobileInfo = []): array
    {
        $mobile = $this->sanitizeMobile($mobile);

        $queryParams = [
            'mobile' => $mobile,
            'retrytype' => $type,
        ];

        $response = $this->makeRequest('GET', self::BASE_URL.'/retry', $queryParams);

        $this->trackOtpRequest($response, $userID, $source, 'resend', $mobileInfo);

        return $this->responseHandler($response);
    }

    /**
     * Verify an OTP against a mobile number via MSG91.
     *
     * @param  string  $otp  The OTP to verify
     * @param  string  $mobile  Full mobile number with country code
     * @return array{type: string, message: string}
     */
    public function sendVerifyOTP(string $otp, string $mobile, $userID = null, string $source = 'register'): array
    {
        $mobile = $this->sanitizeMobile($mobile);

        $queryParams = [
            'otp' => $otp,
            'mobile' => $mobile,
        ];

        $response = $this->makeRequest('GET', self::BASE_URL.'/verify', $queryParams);

        return $this->responseHandler($response);
    }

    /**
     * Handle and normalize the MSG91 API response into a consistent format.
     *
     * MSG91 returns {"type": "success"|"error", "message": "..."} in the body
     * for both 200 and non-200 (e.g. 401) status codes.
     *
     * @param  array{status: int, body: array}  $response  Raw API response
     * @return array{type: string, message: string} Normalized response
     */
    public function responseHandler(array $response): array
    {
        $body = $response['body'] ?? [];
        $type = $body['type'] ?? 'error';
        $message = $body['message'] ?? '';

        if ($type === 'success') {
            return [
                'type' => 'success',
                'message' => match (true) {
                    str_contains($message, 'OTP verified success') => __('message.otp_verified'),
                    str_contains($message, 'retry send successfully') => __('message.otp_verification.resend_send_success'),
                    default => __('message.otp_verification.send_success'),
                },
            ];
        }

        return [
            'type' => 'error',
            'message' => $this->mapErrorMessage($message),
        ];
    }

    /**
     * Map MSG91 error messages to user-friendly custom messages.
     */
    protected function mapErrorMessage(string $message): string
    {
        return match ($message) {
            'Please enter atleast one number to send sms.', 'Mobile no. empty or not numeric', 'Mobile number empty or not numeric' => __('message.enter_your_mobile'),
            'OTP expired', 'otp_expired' => __('message.email_verification.token_expired'),
            'Mobile no. not found', 'OTP not match' => __('message.otp_invalid'),
            'Max limit reached for this otp verification' => __('message.otp_verification.max_attempts_exceeded', ['time' => 'later']),
            'No OTP request found to retryotp' => __('message.otp_verification.resend_failure'),
            'OTP retry count maxed out' => __('message.otp_verification.resend_max_attempts_exceeded', ['time' => 'later']),
            default => __('message.msg_service_down'),
        };
    }

    /**
     * Sanitize a mobile number by removing all non-numeric characters.
     */
    protected function sanitizeMobile(string $mobile): string
    {
        return preg_replace('/\D/', '', $mobile);
    }

    /**
     * Track the OTP request in the MSG91 delivery reports.
     *
     * On 'send': creates a new record with action = 'send'.
     * On 'resend': appends retry attempt to the same record (e.g. 'send, retry_1').
     */
    protected function trackOtpRequest(array $response, $userID, string $source, string $action, array $mobileInfo = []): void
    {
        if (! $userID) {
            return;
        }

        // Use provided mobile info (e.g. profile update with new number), or fall back to user's saved mobile
        if ($mobileInfo) {
            $countryIso = $mobileInfo['country_iso'];
            $mobileNumber = $mobileInfo['mobile'];
            $mobileCode = $mobileInfo['mobile_code'];
        } else {
            $user = User::select('mobile_country_iso', 'mobile', 'mobile_code')->find($userID);

            if (! $user) {
                return;
            }

            $countryIso = $user->mobile_country_iso;
            $mobileNumber = $user->mobile;
            $mobileCode = $user->mobile_code;
        }

        try {
            $controller = new MSG91Controller();

            if ($action === 'resend') {
                $controller->appendOtpRetry(
                    $response,
                    $countryIso,
                    $mobileNumber,
                    $mobileCode,
                    $userID,
                    $source
                );
            } else {
                $controller->updateOtpRequest(
                    $response['body']['request_id'] ?? null,
                    0,
                    $countryIso,
                    $mobileNumber,
                    $mobileCode,
                    $userID,
                    $source,
                    'send'
                );
            }
        } catch (Exception $e) {
            Logger::exception($e);
        }
    }

    /**
     * Build a standardized error response payload.
     */
    protected function errorPayload(string $message): array
    {
        return [
            'status' => 200,
            'body' => ['type' => 'error', 'message' => $message],
        ];
    }
}
